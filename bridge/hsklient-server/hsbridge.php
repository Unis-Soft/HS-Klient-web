<?php
declare(strict_types=1);

/*
 * HSBridge -> HS Klient API, V002 test endpoint.
 *
 * Target deployment:
 *   https://klient.hairsoft.cz/str/api/hsbridge.php
 *
 * V002 intentionally supports only HairSoft customer.id = 492.
 * CUSTOMER: note + loyalityPoints
 * STATISTICS: last completed visit + next active booking
 *
 * Authentication:
 *   1) HSBridge sends its Bridge ID/token over HTTPS.
 *   2) This endpoint validates them server-to-server against
 *      https://bridge.bonfero.com/api/pairing.php?route=identity
 *   3) Directory returns the SoftRC identity previously bound to Bridge ID.
 *   4) RC Setting3/Setting10 are mapped to sw_info.sw_sn/sw_dodatkove.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

require_once __DIR__ . '/../../cfg/nastaveni.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    hsbridge_json(500, ['ok'=>false,'error'=>'DB spojeni neni dostupne.']);
}
$mysqli->set_charset('utf8mb4');

const HSBRIDGE_DIRECTORY_IDENTITY_URL = 'https://bridge.bonfero.com/api/pairing.php?route=identity';
const HSBRIDGE_TEST_CUSTOMER_ID = 492;

function hsbridge_json(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function hsbridge_header(string $name): string {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    $v = $_SERVER[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

function hsbridge_directory_identity(string $bridgeId, string $bridgeToken): array {
    $headers = [
        'Accept: application/json',
        'X-HS-Bridge-ID: ' . $bridgeId,
        'X-HS-Bridge-Token: ' . $bridgeToken,
    ];

    $body = '';
    $status = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init(HSBRIDGE_DIRECTORY_IDENTITY_URL);
        if ($ch === false) throw new RuntimeException('Nelze inicializovat overeni Bridge.');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $result = curl_exec($ch);
        if ($result === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Directory neni dostupny: ' . $err);
        }
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        $body = (string)$result;
    } else {
        $ctx = stream_context_create([
            'http'=>[
                'method'=>'GET',
                'header'=>implode("\r\n", $headers) . "\r\n",
                'timeout'=>8,
                'ignore_errors'=>true,
            ],
            'ssl'=>[
                'verify_peer'=>true,
                'verify_peer_name'=>true,
            ],
        ]);
        $result = @file_get_contents(HSBRIDGE_DIRECTORY_IDENTITY_URL, false, $ctx);
        if ($result === false) throw new RuntimeException('Directory neni dostupny.');
        $body = (string)$result;
        $status = 200;
        if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
            if (preg_match('/\s(\d{3})\s/', (string)$http_response_header[0], $m)) $status = (int)$m[1];
        }
    }

    $json = json_decode($body, true);
    if ($status !== 200 || !is_array($json) || empty($json['ok'])) {
        throw new RuntimeException('Bridge autentizace nebyla Directory potvrzena.');
    }
    return $json;
}

function hsbridge_resolve_pc(mysqli $mysqli, array $directoryIdentity): array {
    $rc = isset($directoryIdentity['rcIdentity']) && is_array($directoryIdentity['rcIdentity'])
        ? $directoryIdentity['rcIdentity'] : [];
    $setting3 = trim(is_string($rc['setting3'] ?? null) ? $rc['setting3'] : '');
    $setting10 = trim(is_string($rc['setting10'] ?? null) ? $rc['setting10'] : '');

    if ($setting3 === '' || $setting10 === '') {
        throw new RuntimeException('Directory nema kompletni SoftRC identitu PC.');
    }

    // SoftRC RC V1: Setting3 = licence/serial, Setting10 = DČ.
    $stmt = $mysqli->prepare(
        'SELECT sw_id, COALESCE(sw_skupina_id,0) AS sw_skupina_id, COALESCE(sw_jmeno_pobocky,"") AS sw_jmeno_pobocky
         FROM sw_info
         WHERE sw_sn=? AND sw_dodatkove=?
         ORDER BY sw_id DESC
         LIMIT 2'
    );
    if (!$stmt) throw new RuntimeException('Nelze pripravit mapovani PC.');
    $stmt->bind_param('ss', $setting3, $setting10);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Mapovani PC selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $rows = [];
    while ($res && ($row = $res->fetch_assoc())) $rows[] = $row;
    $stmt->close();

    if (count($rows) === 0) throw new RuntimeException('PC nebylo v Admin Klient nalezeno.');
    if (count($rows) !== 1) throw new RuntimeException('Identita PC neni v Admin Klient jednoznacna.');

    $swId = (int)$rows[0]['sw_id'];
    $groupId = (int)$rows[0]['sw_skupina_id'];
    if ($swId <= 0 || $groupId <= 0) throw new RuntimeException('PC nema platne sw_id nebo neni zarazeno do skupiny.');

    // Independent server-side confirmation that this branch is attached to
    // at least one HS Klient account. Local Dashboard=1 alone is not enough.
    $stmt = $mysqli->prepare('SELECT 1 FROM sw_email_pobocka WHERE sw_id=? LIMIT 1');
    if (!$stmt) throw new RuntimeException('Nelze overit HS Klient licenci.');
    $stmt->bind_param('i', $swId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Overeni HS Klient selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $hsKlient = $res && $res->num_rows > 0;
    $stmt->close();

    return [
        'sw_id'=>$swId,
        'group_id'=>$groupId,
        'branch_name'=>(string)$rows[0]['sw_jmeno_pobocky'],
        'hs_klient'=>$hsKlient,
        'rc_setting3'=>$setting3,
        'rc_setting10'=>$setting10,
    ];
}

function hsbridge_ensure_audit(mysqli $mysqli): void {
    $sql = 'CREATE TABLE IF NOT EXISTS hsbridge_sync_audit (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id CHAR(64) NOT NULL,
        bridge_id VARCHAR(80) NOT NULL,
        module VARCHAR(40) NOT NULL,
        sw_id INT NOT NULL,
        group_id INT NOT NULL,
        customer_hs_id INT NOT NULL,
        source_updated VARCHAR(64) NOT NULL DEFAULT "",
        before_note TEXT NULL,
        after_note TEXT NULL,
        before_points BIGINT NOT NULL DEFAULT 0,
        after_points BIGINT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_hsbridge_event (event_id),
        KEY idx_hsbridge_customer (sw_id,group_id,customer_hs_id,created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
    if (!$mysqli->query($sql)) throw new RuntimeException('Nelze pripravit HSBridge audit.');
}

function hsbridge_ensure_statistics_audit(mysqli $mysqli): void {
    $sql = 'CREATE TABLE IF NOT EXISTS hsbridge_statistics_audit (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id CHAR(64) NOT NULL,
        bridge_id VARCHAR(80) NOT NULL,
        sw_id INT NOT NULL,
        group_id INT NOT NULL,
        customer_hs_id INT NOT NULL,
        source_as_of VARCHAR(32) NOT NULL DEFAULT "",
        before_last_visit VARCHAR(32) NOT NULL DEFAULT "",
        after_last_visit VARCHAR(32) NOT NULL DEFAULT "",
        before_next_visit VARCHAR(32) NOT NULL DEFAULT "",
        after_next_visit VARCHAR(32) NOT NULL DEFAULT "",
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_hsbridge_statistics_event (event_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
    if (!$mysqli->query($sql)) throw new RuntimeException('Nelze pripravit HSBridge statistics audit.');
}

function hsbridge_normalize_datetime(mixed $value, string $field): string {
    $v = trim(is_string($value) ? $value : '');
    if ($v === '') return '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d{1,6})?$/', $v)) {
        throw new InvalidArgumentException('Neplatne datum ' . $field . '.');
    }
    return substr($v, 0, 19);
}

function hsbridge_statistics_test(mysqli $mysqli, array $pc, string $bridgeId, array $in): never {
    if (!$pc['hs_klient']) hsbridge_json(403, ['ok'=>false,'error'=>'Pro toto PC neni na serveru aktivni HS Klient.']);

    $eventId = strtolower(trim(is_string($in['eventId'] ?? null) ? $in['eventId'] : ''));
    $statistics = isset($in['statistics']) && is_array($in['statistics']) ? $in['statistics'] : [];
    if (!preg_match('/^[a-f0-9]{64}$/', $eventId)) throw new InvalidArgumentException('Neplatne eventId.');

    $customerId = (int)($statistics['customerId'] ?? 0);
    if ($customerId !== HSBRIDGE_TEST_CUSTOMER_ID) throw new InvalidArgumentException('V002 statistics test povoluje pouze customer.id=492.');

    $guid = strtoupper(trim(is_string($statistics['guid'] ?? null) ? $statistics['guid'] : ''));
    if ($guid === '' || !preg_match('/^[A-F0-9]{32}$/', $guid)) throw new InvalidArgumentException('Neplatny KlientGuid.');

    $lastVisit = hsbridge_normalize_datetime($statistics['lastVisit'] ?? '', 'lastVisit');
    $nextVisit = hsbridge_normalize_datetime($statistics['nextVisit'] ?? '', 'nextVisit');
    $asOf = hsbridge_normalize_datetime($statistics['asOf'] ?? '', 'asOf');
    if ($asOf === '') throw new InvalidArgumentException('Chybi statistics.asOf.');

    hsbridge_ensure_statistics_audit($mysqli);
    $swId = (int)$pc['sw_id'];
    $groupId = (int)$pc['group_id'];

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare('SELECT event_id FROM hsbridge_statistics_audit WHERE event_id=? LIMIT 1');
        if (!$stmt) throw new RuntimeException('Statistics audit prepare selhal.');
        $stmt->bind_param('s', $eventId);
        $stmt->execute();
        $dup = $stmt->get_result();
        $already = $dup && $dup->num_rows > 0;
        $stmt->close();
        if ($already) {
            $mysqli->rollback();
            hsbridge_json(200, ['ok'=>true,'alreadyProcessed'=>true]);
        }

        $stmt = $mysqli->prepare(
            'SELECT lidi_id, COALESCE(lidi_guid,"") AS lidi_guid
             FROM klient_lidi
             WHERE lidi_sw_id=? AND lidi_skupinaID=? AND lidi_hs_id=?
             LIMIT 2'
        );
        if (!$stmt) throw new RuntimeException('Statistics customer prepare selhal.');
        $stmt->bind_param('iii', $swId, $groupId, $customerId);
        $stmt->execute();
        $res = $stmt->get_result();
        $customers = [];
        while ($res && ($row = $res->fetch_assoc())) $customers[] = $row;
        $stmt->close();

        if (count($customers) !== 1) throw new RuntimeException('Zakaznik neni v HS Klient jednoznacne nalezen.');
        $serverGuid = strtoupper(trim((string)$customers[0]['lidi_guid']));
        if ($serverGuid === '' || !hash_equals($serverGuid, $guid)) {
            throw new RuntimeException('KlientGuid nesouhlasi; statistics zapis byl zablokovan.');
        }

        $stmt = $mysqli->prepare(
            'SELECT statistikaID, stat_PosledniNavsteva, stat_PristiNavsteva
             FROM klient_lidi_statistika
             WHERE stat_klient_GUID=?
             LIMIT 2
             FOR UPDATE'
        );
        if (!$stmt) throw new RuntimeException('Statistics row prepare selhal.');
        $stmt->bind_param('s', $serverGuid);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($res && ($row = $res->fetch_assoc())) $rows[] = $row;
        $stmt->close();
        if (count($rows) !== 1) throw new RuntimeException('Statistika zakaznika neni v HS Klient jednoznacne nalezena.');

        $statisticsId = (int)$rows[0]['statistikaID'];
        $beforeLast = $rows[0]['stat_PosledniNavsteva'] === null ? '' : substr((string)$rows[0]['stat_PosledniNavsteva'], 0, 19);
        $beforeNext = $rows[0]['stat_PristiNavsteva'] === null ? '' : substr((string)$rows[0]['stat_PristiNavsteva'], 0, 19);

        $stmt = $mysqli->prepare(
            "UPDATE klient_lidi_statistika
             SET stat_PosledniNavsteva=NULLIF(?, ''),
                 stat_PristiNavsteva=NULLIF(?, ''),
                 stat_updated=NOW()
             WHERE statistikaID=?
             LIMIT 1"
        );
        if (!$stmt) throw new RuntimeException('Statistics update prepare selhal.');
        $stmt->bind_param('ssi', $lastVisit, $nextVisit, $statisticsId);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Statistics update selhal: ' . $err);
        }
        $stmt->close();

        $stmt = $mysqli->prepare(
            'INSERT INTO hsbridge_statistics_audit
             (event_id,bridge_id,sw_id,group_id,customer_hs_id,source_as_of,
              before_last_visit,after_last_visit,before_next_visit,after_next_visit,created_at)
             VALUES(?,?,?,?,?,?,?,?,?,?,NOW())'
        );
        if (!$stmt) throw new RuntimeException('Statistics audit insert prepare selhal.');
        $stmt->bind_param(
            'ssiiisssss',
            $eventId, $bridgeId, $swId, $groupId, $customerId, $asOf,
            $beforeLast, $lastVisit, $beforeNext, $nextVisit
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Statistics audit insert selhal: ' . $err);
        }
        $stmt->close();

        $mysqli->commit();
        hsbridge_json(200, [
            'ok'=>true,
            'alreadyProcessed'=>false,
            'identity'=>['swId'=>$swId,'groupId'=>$groupId],
            'statistics'=>[
                'customerId'=>$customerId,
                'before'=>['lastVisit'=>$beforeLast,'nextVisit'=>$beforeNext],
                'after'=>['lastVisit'=>$lastVisit,'nextVisit'=>$nextVisit],
                'asOf'=>$asOf,
            ],
        ]);
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
}

function hsbridge_request_json(): array {
    $raw = (string)file_get_contents('php://input');
    if ($raw === '' || strlen($raw) > 262144) throw new InvalidArgumentException('Neplatne telo pozadavku.');
    $json = json_decode($raw, true);
    if (!is_array($json)) throw new InvalidArgumentException('Neplatny JSON.');
    return $json;
}

$bridgeId = hsbridge_header('X-HS-Bridge-ID');
$bridgeToken = hsbridge_header('X-HS-Bridge-Token');

if (!preg_match('/^hsb_[a-f0-9]{16,64}$/', $bridgeId) || !preg_match('/^[a-f0-9]{64}$/i', $bridgeToken)) {
    hsbridge_json(401, ['ok'=>false,'error'=>'Chybi nebo je neplatna autentizace Bridge.']);
}

try {
    $directory = hsbridge_directory_identity($bridgeId, $bridgeToken);
    $pc = hsbridge_resolve_pc($mysqli, $directory);

    $route = trim((string)($_GET['route'] ?? ''), '/');
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if ($route === 'status' && $method === 'GET') {
        hsbridge_json(200, [
            'ok'=>true,
            'identity'=>[
                'swId'=>$pc['sw_id'],
                'groupId'=>$pc['group_id'],
                'branchName'=>$pc['branch_name'],
            ],
            'modules'=>[
                'customer'=>(bool)$pc['hs_klient'],
                'statistics'=>(bool)$pc['hs_klient'],
                'timeline'=>false,
            ],
            'testCustomerId'=>HSBRIDGE_TEST_CUSTOMER_ID,
        ]);
    }

    if ($route === 'customer/test' && $method === 'POST') {
        if (!$pc['hs_klient']) hsbridge_json(403, ['ok'=>false,'error'=>'Pro toto PC neni na serveru aktivni HS Klient.']);

        $in = hsbridge_request_json();
        $eventId = strtolower(trim(is_string($in['eventId'] ?? null) ? $in['eventId'] : ''));
        $customer = isset($in['customer']) && is_array($in['customer']) ? $in['customer'] : [];

        if (!preg_match('/^[a-f0-9]{64}$/', $eventId)) throw new InvalidArgumentException('Neplatne eventId.');

        $customerId = (int)($customer['id'] ?? 0);
        if ($customerId !== HSBRIDGE_TEST_CUSTOMER_ID) throw new InvalidArgumentException('V001 test povoluje pouze customer.id=492.');

        $guid = strtoupper(trim(is_string($customer['guid'] ?? null) ? $customer['guid'] : ''));
        if ($guid !== '' && !preg_match('/^[A-F0-9]{32}$/', $guid)) throw new InvalidArgumentException('Neplatny KlientGuid.');

        $note = is_string($customer['note'] ?? null) ? $customer['note'] : '';
        if (strlen($note) > 20000) throw new InvalidArgumentException('Poznamka je prilis dlouha.');

        $points = filter_var($customer['loyalityPoints'] ?? null, FILTER_VALIDATE_INT);
        if ($points === false || $points < 0 || $points > 100000000) throw new InvalidArgumentException('Neplatne loyalityPoints.');

        $sourceUpdated = trim(is_string($customer['updated'] ?? null) ? $customer['updated'] : '');
        if (strlen($sourceUpdated) > 64) throw new InvalidArgumentException('Neplatne updated.');

        hsbridge_ensure_audit($mysqli);

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare('SELECT event_id FROM hsbridge_sync_audit WHERE event_id=? LIMIT 1');
            if (!$stmt) throw new RuntimeException('Audit prepare selhal.');
            $stmt->bind_param('s', $eventId);
            $stmt->execute();
            $dup = $stmt->get_result();
            $already = $dup && $dup->num_rows > 0;
            $stmt->close();
            if ($already) {
                $mysqli->rollback();
                hsbridge_json(200, ['ok'=>true,'alreadyProcessed'=>true]);
            }

            $stmt = $mysqli->prepare(
                'SELECT lidi_id, COALESCE(lidi_guid,"") AS lidi_guid, COALESCE(lidi_hs_note,"") AS lidi_hs_note,
                        COALESCE(lidi_hs_loyalityPoints,0) AS lidi_hs_loyalityPoints
                 FROM klient_lidi
                 WHERE lidi_sw_id=? AND lidi_skupinaID=? AND lidi_hs_id=?
                 LIMIT 2 FOR UPDATE'
            );
            if (!$stmt) throw new RuntimeException('Customer prepare selhal.');
            $swId = (int)$pc['sw_id'];
            $groupId = (int)$pc['group_id'];
            $stmt->bind_param('iii', $swId, $groupId, $customerId);
            $stmt->execute();
            $res = $stmt->get_result();
            $rows = [];
            while ($res && ($row = $res->fetch_assoc())) $rows[] = $row;
            $stmt->close();

            if (count($rows) !== 1) throw new RuntimeException('Zakaznik neni v HS Klient jednoznacne nalezen.');
            $serverGuid = strtoupper(trim((string)$rows[0]['lidi_guid']));
            if ($guid !== '' && $serverGuid !== '' && !hash_equals($serverGuid, $guid)) {
                throw new RuntimeException('KlientGuid nesouhlasi; zapis byl zablokovan.');
            }

            $lidiId = (int)$rows[0]['lidi_id'];
            $beforeNote = (string)$rows[0]['lidi_hs_note'];
            $beforePoints = (int)$rows[0]['lidi_hs_loyalityPoints'];

            $stmt = $mysqli->prepare('UPDATE klient_lidi SET lidi_hs_note=?, lidi_hs_loyalityPoints=? WHERE lidi_id=? LIMIT 1');
            if (!$stmt) throw new RuntimeException('Customer update prepare selhal.');
            $stmt->bind_param('sii', $note, $points, $lidiId);
            if (!$stmt->execute()) {
                $e = $stmt->error;
                $stmt->close();
                throw new RuntimeException('Customer update selhal: ' . $e);
            }
            $stmt->close();

            $module = 'customer-test-v001';
            $stmt = $mysqli->prepare(
                'INSERT INTO hsbridge_sync_audit
                 (event_id,bridge_id,module,sw_id,group_id,customer_hs_id,source_updated,before_note,after_note,before_points,after_points,created_at)
                 VALUES(?,?,?,?,?,?,?,?,?,?,?,NOW())'
            );
            if (!$stmt) throw new RuntimeException('Audit insert prepare selhal.');
            $stmt->bind_param(
                'sssiiisssii',
                $eventId, $bridgeId, $module, $swId, $groupId, $customerId,
                $sourceUpdated, $beforeNote, $note, $beforePoints, $points
            );
            if (!$stmt->execute()) {
                $e = $stmt->error;
                $stmt->close();
                throw new RuntimeException('Audit insert selhal: ' . $e);
            }
            $stmt->close();

            $mysqli->commit();
            hsbridge_json(200, [
                'ok'=>true,
                'alreadyProcessed'=>false,
                'identity'=>['swId'=>$pc['sw_id'],'groupId'=>$pc['group_id']],
                'customer'=>[
                    'id'=>$customerId,
                    'before'=>['note'=>$beforeNote,'loyalityPoints'=>$beforePoints],
                    'after'=>['note'=>$note,'loyalityPoints'=>$points],
                ],
            ]);
        } catch (Throwable $e) {
            $mysqli->rollback();
            throw $e;
        }
    }

    if ($route === 'statistics/test' && $method === 'POST') {
        hsbridge_statistics_test($mysqli, $pc, $bridgeId, hsbridge_request_json());
    }

    hsbridge_json(404, ['ok'=>false,'error'=>'Neznama HSBridge route.']);
} catch (InvalidArgumentException $e) {
    hsbridge_json(400, ['ok'=>false,'error'=>$e->getMessage()]);
} catch (Throwable $e) {
    error_log('HSBridge HS Klient API: ' . $e->getMessage());
    hsbridge_json(409, ['ok'=>false,'error'=>$e->getMessage()]);
}
