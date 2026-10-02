<?php
declare(strict_types=1);

/*
 * HairSoft Klient V243 - HSBridge queues with persistent local Bridge authorization.
 * Target: /str/api/hsbridge-program-actions.php
 *
 * HairSoft always has priority. This endpoint only leases commands; the Windows
 * Bridge performs the local DB write with zero SQLite busy timeout and returns
 * the command to pending state when HairSoft is currently using the database.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

require_once __DIR__ . '/../../cfg/nastaveni.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    hspa_json(500, ['ok'=>false,'error'=>'DB spojeni neni dostupne.']);
}
$mysqli->set_charset('utf8mb4');

const HSPA_DIRECTORY_IDENTITY_URL = 'https://bridge.bonfero.com/api/pairing.php?route=identity';
const HSPA_BINDING_TABLE = 'hsbridge_program_pc_bindings';

/*
 * V243: PROGRAMS action/photo endpoint no longer revalidates a healthy Bridge
 * against the external Directory on a timer.  The first successful Directory
 * verification persists only bridge_id + SHA-256(token) + sw_id locally.
 * Subsequent polls are authenticated locally.  Directory is used again only
 * when no matching local binding exists (for example first use or token change).
 */
function hspa_ensure_binding_table(mysqli $mysqli): void {
    $sql = "CREATE TABLE IF NOT EXISTS `" . HSPA_BINDING_TABLE . "` (
        `bridge_id` VARCHAR(80) NOT NULL,
        `token_hash` CHAR(64) NOT NULL,
        `sw_id` INT NOT NULL,
        `directory_verified_at` DATETIME NOT NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`bridge_id`),
        KEY `idx_hspa_binding_sw` (`sw_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!$mysqli->query($sql)) {
        throw new RuntimeException('Nelze pripravit lokalni vazbu Bridge.');
    }
}

function hspa_token_hash(string $bridgeToken): string {
    return hash('sha256', $bridgeToken);
}

function hspa_resolve_pc_by_sw_id(mysqli $mysqli, int $swId): ?array {
    if ($swId <= 0) return null;

    $stmt = $mysqli->prepare(
        'SELECT sw_id, COALESCE(sw_skupina_id,0) AS sw_skupina_id FROM sw_info WHERE sw_id=? LIMIT 1'
    );
    if (!$stmt) throw new RuntimeException('Nelze pripravit lokalni mapovani PC.');
    $stmt->bind_param('i', $swId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Lokalni mapovani PC selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!is_array($row)) return null;

    $groupId = (int) ($row['sw_skupina_id'] ?? 0);
    if ($groupId <= 0) return null;

    // Licence se kontroluje lokalne pri kazdem requestu, takze jeji odebrani
    // se projevi okamzite bez zavislosti na externim Directory.
    $stmt = $mysqli->prepare('SELECT 1 FROM sw_email_pobocka WHERE sw_id=? LIMIT 1');
    if (!$stmt) throw new RuntimeException('Nelze overit HS Klient licenci.');
    $stmt->bind_param('i', $swId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Overeni HS Klient selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $allowed = $res && $res->num_rows > 0;
    $stmt->close();
    if (!$allowed) {
        throw new RuntimeException('Pro toto PC neni na serveru aktivni HS Klient.');
    }

    return ['sw_id'=>$swId, 'group_id'=>$groupId];
}

function hspa_binding_read(mysqli $mysqli, string $bridgeId, string $bridgeToken): ?array {
    hspa_ensure_binding_table($mysqli);
    $stmt = $mysqli->prepare('SELECT token_hash, sw_id FROM `' . HSPA_BINDING_TABLE . '` WHERE bridge_id=? LIMIT 1');
    if (!$stmt) throw new RuntimeException('Nelze nacist lokalni vazbu Bridge.');
    $stmt->bind_param('s', $bridgeId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Nacteni lokalni vazby Bridge selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!is_array($row)) return null;

    $storedHash = trim((string) ($row['token_hash'] ?? ''));
    $actualHash = hspa_token_hash($bridgeToken);
    if ($storedHash === '' || !hash_equals($storedHash, $actualHash)) {
        return null;
    }

    return hspa_resolve_pc_by_sw_id($mysqli, (int) ($row['sw_id'] ?? 0));
}

function hspa_binding_write(mysqli $mysqli, string $bridgeId, string $bridgeToken, array $pc): void {
    hspa_ensure_binding_table($mysqli);
    $swId = (int) ($pc['sw_id'] ?? 0);
    if ($swId <= 0) throw new RuntimeException('Nelze ulozit lokalni vazbu Bridge bez sw_id.');
    $tokenHash = hspa_token_hash($bridgeToken);

    $stmt = $mysqli->prepare(
        'INSERT INTO `' . HSPA_BINDING_TABLE . '` '
        . '(bridge_id,token_hash,sw_id,directory_verified_at,created_at,updated_at) '
        . 'VALUES (?,?,?,NOW(),NOW(),NOW()) '
        . 'ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash), sw_id=VALUES(sw_id), '
        . 'directory_verified_at=NOW(), updated_at=NOW()'
    );
    if (!$stmt) throw new RuntimeException('Nelze ulozit lokalni vazbu Bridge.');
    $stmt->bind_param('ssi', $bridgeId, $tokenHash, $swId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Ulozeni lokalni vazby Bridge selhalo: ' . $e);
    }
    $stmt->close();
}

function hspa_resolve_pc_local_first(mysqli $mysqli, string $bridgeId, string $bridgeToken): array {
    $pc = hspa_binding_read($mysqli, $bridgeId, $bridgeToken);
    if (is_array($pc)) return $pc;

    // Pouze bootstrap / zmena tokenu: jednorazove overeni proti Directory.
    $identity = hspa_directory_identity($bridgeId, $bridgeToken);
    $pc = hspa_resolve_pc($mysqli, $identity);
    hspa_binding_write($mysqli, $bridgeId, $bridgeToken, $pc);
    return $pc;
}

function hspa_json(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function hspa_header(string $name): string {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    $v = $_SERVER[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

function hspa_directory_identity(string $bridgeId, string $bridgeToken): array {
    $headers = [
        'Accept: application/json',
        'X-HS-Bridge-ID: ' . $bridgeId,
        'X-HS-Bridge-Token: ' . $bridgeToken,
    ];
    $body = '';
    $status = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init(HSPA_DIRECTORY_IDENTITY_URL);
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
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        $body = (string) $result;
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
        $result = @file_get_contents(HSPA_DIRECTORY_IDENTITY_URL, false, $ctx);
        if ($result === false) throw new RuntimeException('Directory neni dostupny.');
        $body = (string) $result;
        $status = 200;
        if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
            if (preg_match('/\s(\d{3})\s/', (string) $http_response_header[0], $m)) $status = (int) $m[1];
        }
    }

    $json = json_decode($body, true);
    if ($status !== 200 || !is_array($json) || empty($json['ok'])) {
        throw new RuntimeException('Bridge autentizace nebyla Directory potvrzena.');
    }
    return $json;
}

function hspa_resolve_pc(mysqli $mysqli, array $directoryIdentity): array {
    $rc = isset($directoryIdentity['rcIdentity']) && is_array($directoryIdentity['rcIdentity']) ? $directoryIdentity['rcIdentity'] : [];
    $setting3 = trim(is_string($rc['setting3'] ?? null) ? $rc['setting3'] : '');
    $setting10 = trim(is_string($rc['setting10'] ?? null) ? $rc['setting10'] : '');
    if ($setting3 === '' || $setting10 === '') {
        throw new RuntimeException('Directory nema kompletni SoftRC identitu PC.');
    }

    $stmt = $mysqli->prepare(
        'SELECT sw_id, COALESCE(sw_skupina_id,0) AS sw_skupina_id FROM sw_info WHERE sw_sn=? AND sw_dodatkove=? ORDER BY sw_id DESC LIMIT 2'
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

    $swId = (int) $rows[0]['sw_id'];
    $groupId = (int) $rows[0]['sw_skupina_id'];
    if ($swId <= 0 || $groupId <= 0) throw new RuntimeException('PC nema platne sw_id nebo neni zarazeno do skupiny.');

    $stmt = $mysqli->prepare('SELECT 1 FROM sw_email_pobocka WHERE sw_id=? LIMIT 1');
    if (!$stmt) throw new RuntimeException('Nelze overit HS Klient licenci.');
    $stmt->bind_param('i', $swId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Overeni HS Klient selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $allowed = $res && $res->num_rows > 0;
    $stmt->close();
    if (!$allowed) throw new RuntimeException('Pro toto PC neni na serveru aktivni HS Klient.');

    return ['sw_id'=>$swId, 'group_id'=>$groupId];
}

function hspa_ensure_queue(mysqli $mysqli): void {
    $sql = "CREATE TABLE IF NOT EXISTS `hsbridge_program_commands` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `command_uuid` CHAR(32) NOT NULL,
        `sw_id` INT NOT NULL,
        `group_id` INT NOT NULL,
        `customer_hs_id` BIGINT NOT NULL,
        `customer_guid` VARCHAR(96) NOT NULL DEFAULT '',
        `program_hs_id` BIGINT NOT NULL,
        `quantity` INT NOT NULL,
        `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
        `attempts` INT NOT NULL DEFAULT 0,
        `available_at` DATETIME NOT NULL,
        `leased_until` DATETIME NULL,
        `visit_hs_id` BIGINT NOT NULL DEFAULT 0,
        `last_error` VARCHAR(1000) NOT NULL DEFAULT '',
        `created_by_k_id` INT NOT NULL DEFAULT 0,
        `created_by_staff_id` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_hs_program_command_uuid` (`command_uuid`),
        KEY `idx_hs_program_command_queue` (`sw_id`,`group_id`,`status`,`available_at`,`id`),
        KEY `idx_hs_program_command_customer` (`sw_id`,`group_id`,`customer_hs_id`,`program_hs_id`,`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!$mysqli->query($sql)) throw new RuntimeException('Nelze pripravit frontu Programu.');
}



function hspa_mirror_program_visit(mysqli $mysqli, array $pc, array $commandRow, int $visitId, string $visitAt): bool {
    if ($visitId <= 0) return false;
    $customerId = (int) ($commandRow['customer_hs_id'] ?? 0);
    $programId = (int) ($commandRow['program_hs_id'] ?? 0);
    $quantity = (int) ($commandRow['quantity'] ?? 0);
    $swId = (int) ($pc['sw_id'] ?? 0);
    $groupId = (int) ($pc['group_id'] ?? 0);
    if ($customerId <= 0 || $programId <= 0 || $quantity <= 0 || $swId <= 0 || $groupId <= 0) return false;

    $visitAt = trim($visitAt);
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $visitAt)) {
        $visitAt = date('Y-m-d H:i:s');
    }

    $tableCheck = $mysqli->query("SHOW TABLES LIKE 'hsbridge_program_visits'");
    if (!$tableCheck || $tableCheck->num_rows < 1) {
        if ($tableCheck) $tableCheck->free();
        return false;
    }
    $tableCheck->free();

    $required = ['sw_id','group_id','visit_hs_id','program_hs_id','customer_hs_id','visit_at_hs','quantity'];
    $columns = [];
    $columnResult = $mysqli->query('SHOW COLUMNS FROM hsbridge_program_visits');
    if (!$columnResult) return false;
    while ($column = $columnResult->fetch_assoc()) {
        $name = isset($column['Field']) ? (string) $column['Field'] : '';
        if ($name !== '') $columns[$name] = true;
    }
    $columnResult->free();
    foreach ($required as $name) {
        if (!isset($columns[$name])) return false;
    }

    // Idempotent immediate mirror update for this one customer/visit only.
    // The regular full PROGRAMS snapshot remains the authoritative reconciliation.
    $stmt = $mysqli->prepare('DELETE FROM hsbridge_program_visits WHERE sw_id=? AND group_id=? AND visit_hs_id=?');
    if (!$stmt) return false;
    $stmt->bind_param('iii', $swId, $groupId, $visitId);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) return false;

    $stmt = $mysqli->prepare('INSERT INTO hsbridge_program_visits (sw_id,group_id,visit_hs_id,program_hs_id,customer_hs_id,visit_at_hs,quantity) VALUES (?,?,?,?,?,?,?)');
    if (!$stmt) return false;
    $stmt->bind_param('iiiiisi', $swId, $groupId, $visitId, $programId, $customerId, $visitAt, $quantity);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function hspa_photo_storage_root(): string {
    return rtrim((string) sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'hairsoft-klient-program-photos';
}

function hspa_photo_remove_tree(string $dir): void {
    if ($dir === '' || !is_dir($dir)) return;
    $items = @scandir($dir);
    if (is_array($items)) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) hspa_photo_remove_tree($path); else @unlink($path);
        }
    }
    @rmdir($dir);
}

function hspa_ensure_photo_queue(mysqli $mysqli): void {
    $jobs = "CREATE TABLE IF NOT EXISTS `hsbridge_program_photo_jobs` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `job_uuid` CHAR(32) NOT NULL,
        `sw_id` INT NOT NULL,
        `group_id` INT NOT NULL,
        `customer_hs_id` BIGINT NOT NULL,
        `customer_guid` VARCHAR(96) NOT NULL DEFAULT '',
        `program_hs_id` BIGINT NOT NULL DEFAULT 0,
        `program_name` VARCHAR(255) NOT NULL DEFAULT '',
        `date_folder` VARCHAR(16) NOT NULL,
        `subfolder` VARCHAR(80) NOT NULL DEFAULT '',
        `total_files` INT NOT NULL DEFAULT 0,
        `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
        `attempts` INT NOT NULL DEFAULT 0,
        `available_at` DATETIME NOT NULL,
        `leased_until` DATETIME NULL,
        `last_error` VARCHAR(1000) NOT NULL DEFAULT '',
        `created_by_k_id` INT NOT NULL DEFAULT 0,
        `created_by_staff_id` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_hs_program_photo_uuid` (`job_uuid`),
        KEY `idx_hs_program_photo_queue` (`sw_id`,`group_id`,`status`,`available_at`,`id`),
        KEY `idx_hs_program_photo_customer` (`sw_id`,`group_id`,`customer_hs_id`,`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!$mysqli->query($jobs)) throw new RuntimeException('Nelze pripravit frontu fotografii Programu.');
    $files = "CREATE TABLE IF NOT EXISTS `hsbridge_program_photo_files` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `job_id` BIGINT UNSIGNED NOT NULL,
        `file_uuid` CHAR(32) NOT NULL,
        `stored_name` VARCHAR(80) NOT NULL,
        `final_name` VARCHAR(120) NOT NULL,
        `mime_type` VARCHAR(80) NOT NULL DEFAULT 'image/jpeg',
        `file_size` BIGINT NOT NULL DEFAULT 0,
        `sha256` CHAR(64) NOT NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_hs_program_photo_file_uuid` (`file_uuid`),
        KEY `idx_hs_program_photo_files_job` (`job_id`,`sort_order`,`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!$mysqli->query($files)) throw new RuntimeException('Nelze pripravit soubory fronty fotografii Programu.');
}

function hspa_photo_next(mysqli $mysqli, array $pc): never {
    hspa_ensure_photo_queue($mysqli);
    $cutoff=date('Y-m-d H:i:s',time()-86400);
    $stale=$mysqli->prepare("SELECT id,job_uuid FROM hsbridge_program_photo_jobs WHERE status IN ('pending','processing','failed') AND created_at<? LIMIT 100");
    if($stale){$stale->bind_param('s',$cutoff);if($stale->execute()){$rr=$stale->get_result();$rows=[];while($rr&&($sr=$rr->fetch_assoc()))$rows[]=$sr;$stale->close();foreach($rows as $sr){$sid=(int)$sr['id'];hspa_photo_remove_tree(hspa_photo_storage_root().DIRECTORY_SEPARATOR.(string)$sr['job_uuid']);$q=$mysqli->prepare('DELETE FROM hsbridge_program_photo_files WHERE job_id=?');if($q){$q->bind_param('i',$sid);$q->execute();$q->close();}$q=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='expired',leased_until=NULL,last_error='temporary files expired',updated_at=NOW() WHERE id=?");if($q){$q->bind_param('i',$sid);$q->execute();$q->close();}}}else{$stale->close();}}
    $swId=(int)$pc['sw_id']; $groupId=(int)$pc['group_id'];
    $stmt=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='pending',leased_until=NULL,updated_at=NOW() WHERE sw_id=? AND group_id=? AND status='processing' AND leased_until IS NOT NULL AND leased_until<NOW()");
    if ($stmt) { $stmt->bind_param('ii',$swId,$groupId); $stmt->execute(); $stmt->close(); }
    for ($try=0;$try<2;$try++) {
        $stmt=$mysqli->prepare("SELECT id,job_uuid,customer_hs_id,program_hs_id,program_name,date_folder,subfolder,total_files FROM hsbridge_program_photo_jobs WHERE sw_id=? AND group_id=? AND status='pending' AND available_at<=NOW() ORDER BY id ASC LIMIT 1");
        if (!$stmt) throw new RuntimeException('Nelze nacist frontu fotografii.');
        $stmt->bind_param('ii',$swId,$groupId); if(!$stmt->execute()){ $e=$stmt->error;$stmt->close();throw new RuntimeException($e); }
        $res=$stmt->get_result(); $row=$res?$res->fetch_assoc():null; $stmt->close();
        if(!$row) hspa_json(200,['ok'=>true,'job'=>null]);
        $jobId=(int)$row['id'];
        $stmt=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='processing',attempts=attempts+1,leased_until=DATE_ADD(NOW(),INTERVAL 10 MINUTE),updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status='pending'");
        if(!$stmt) throw new RuntimeException('Nelze rezervovat fotografie.');
        $stmt->bind_param('iii',$jobId,$swId,$groupId); $stmt->execute(); $leased=$stmt->affected_rows===1; $stmt->close(); if(!$leased) continue;
        $stmt=$mysqli->prepare('SELECT id,file_uuid,final_name,mime_type,file_size,sha256,sort_order FROM hsbridge_program_photo_files WHERE job_id=? ORDER BY sort_order ASC,id ASC');
        if(!$stmt) throw new RuntimeException('Nelze nacist soubory fotografii.');
        $stmt->bind_param('i',$jobId); $stmt->execute(); $r=$stmt->get_result(); $files=[];
        while($r && ($f=$r->fetch_assoc())) $files[]=['id'=>(int)$f['id'],'uuid'=>(string)$f['file_uuid'],'name'=>(string)$f['final_name'],'mime'=>(string)$f['mime_type'],'size'=>(int)$f['file_size'],'sha256'=>(string)$f['sha256']];
        $stmt->close();
        hspa_json(200,['ok'=>true,'job'=>['id'=>$jobId,'uuid'=>(string)$row['job_uuid'],'customerId'=>(int)$row['customer_hs_id'],'programId'=>(int)$row['program_hs_id'],'programName'=>(string)$row['program_name'],'dateFolder'=>(string)$row['date_folder'],'subfolder'=>(string)$row['subfolder'],'files'=>$files]]);
    }
    hspa_json(200,['ok'=>true,'job'=>null]);
}

function hspa_photo_file(mysqli $mysqli, array $pc): never {
    hspa_ensure_photo_queue($mysqli);
    $jobId=(int)($_GET['jobId']??0); $fileId=(int)($_GET['fileId']??0);
    if($jobId<=0||$fileId<=0) throw new InvalidArgumentException('Neplatny soubor fotografie.');
    $swId=(int)$pc['sw_id']; $groupId=(int)$pc['group_id'];
    $stmt=$mysqli->prepare("SELECT j.job_uuid,f.stored_name,f.final_name,f.mime_type,f.file_size,f.sha256 FROM hsbridge_program_photo_jobs j JOIN hsbridge_program_photo_files f ON f.job_id=j.id WHERE j.id=? AND f.id=? AND j.sw_id=? AND j.group_id=? AND j.status='processing' AND j.leased_until IS NOT NULL AND j.leased_until>=NOW() LIMIT 1");
    if(!$stmt) throw new RuntimeException('Nelze pripravit fotografii.');
    $stmt->bind_param('iiii',$jobId,$fileId,$swId,$groupId); $stmt->execute(); $res=$stmt->get_result(); $row=$res?$res->fetch_assoc():null; $stmt->close();
    if(!$row){ http_response_code(404); exit; }
    $path=hspa_photo_storage_root().DIRECTORY_SEPARATOR.$row['job_uuid'].DIRECTORY_SEPARATOR.$row['stored_name'];
    if(!is_file($path)||!is_readable($path)){ http_response_code(410); exit; }
    header_remove('Content-Type');
    header('Content-Type: '.((string)$row['mime_type']?:'application/octet-stream'));
    header('Content-Length: '.(string)filesize($path));
    header('Content-Disposition: attachment; filename="'.str_replace(['"',"\r","\n"],'',(string)$row['final_name']).'"');
    header('X-HS-Photo-SHA256: '.(string)$row['sha256']);
    readfile($path); exit;
}

function hspa_photo_result(mysqli $mysqli, array $pc, array $in): never {
    hspa_ensure_photo_queue($mysqli);
    $jobId=(int)($in['jobId']??0); $status=trim(is_string($in['status']??null)?$in['status']:''); $message=trim(is_string($in['message']??null)?$in['message']:'');
    if($jobId<=0||!in_array($status,['done','retry','failed'],true)) throw new InvalidArgumentException('Neplatny vysledek fotografii.');
    if(strlen($message)>1000)$message=substr($message,0,1000);
    $swId=(int)$pc['sw_id']; $groupId=(int)$pc['group_id'];
    $stmt=$mysqli->prepare('SELECT job_uuid FROM hsbridge_program_photo_jobs WHERE id=? AND sw_id=? AND group_id=? LIMIT 1');
    if(!$stmt) throw new RuntimeException('Nelze overit davku fotografii.'); $stmt->bind_param('iii',$jobId,$swId,$groupId); $stmt->execute(); $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; $stmt->close();
    if(!$row) throw new RuntimeException('Davka fotografii nebyla pro toto PC nalezena.');
    if($status==='done'){
        $stmt=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='done',last_error='',leased_until=NULL,updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status IN ('processing','pending')"); $stmt->bind_param('iii',$jobId,$swId,$groupId);
    }elseif($status==='retry'){
        $stmt=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='pending',available_at=DATE_ADD(NOW(),INTERVAL 10 SECOND),leased_until=NULL,last_error=?,updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status IN ('processing','pending')"); $stmt->bind_param('siii',$message,$jobId,$swId,$groupId);
    }else{
        $stmt=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='failed',leased_until=NULL,last_error=?,updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status IN ('processing','pending')"); $stmt->bind_param('siii',$message,$jobId,$swId,$groupId);
    }
    if(!$stmt->execute()){ $e=$stmt->error;$stmt->close();throw new RuntimeException('Ulozeni vysledku fotografii selhalo: '.$e); } $stmt->close();
    if($status==='done'||$status==='failed'){
        hspa_photo_remove_tree(hspa_photo_storage_root().DIRECTORY_SEPARATOR.(string)$row['job_uuid']);
        $stmt=$mysqli->prepare('DELETE FROM hsbridge_program_photo_files WHERE job_id=?'); if($stmt){$stmt->bind_param('i',$jobId);$stmt->execute();$stmt->close();}
    }
    hspa_json(200,['ok'=>true]);
}

function hspa_next(mysqli $mysqli, array $pc): never {
    hspa_ensure_queue($mysqli);
    $swId = (int) $pc['sw_id'];
    $groupId = (int) $pc['group_id'];

    // Expired lease means Bridge did not acknowledge the previous attempt.
    $stmt = $mysqli->prepare("UPDATE hsbridge_program_commands SET status='pending', leased_until=NULL, updated_at=NOW() WHERE sw_id=? AND group_id=? AND status='processing' AND leased_until IS NOT NULL AND leased_until < NOW()");
    if ($stmt) {
        $stmt->bind_param('ii', $swId, $groupId);
        $stmt->execute();
        $stmt->close();
    }

    for ($try = 0; $try < 2; $try++) {
        $stmt = $mysqli->prepare("SELECT id, command_uuid, customer_hs_id, program_hs_id, quantity FROM hsbridge_program_commands WHERE sw_id=? AND group_id=? AND status='pending' AND available_at<=NOW() ORDER BY id ASC LIMIT 1");
        if (!$stmt) throw new RuntimeException('Nelze nacist frontu Programu.');
        $stmt->bind_param('ii', $swId, $groupId);
        if (!$stmt->execute()) {
            $e = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Fronta Programu selhala: ' . $e);
        }
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if (!$row) hspa_json(200, ['ok'=>true,'command'=>null]);

        $id = (int) $row['id'];
        $stmt = $mysqli->prepare("UPDATE hsbridge_program_commands SET status='processing', attempts=attempts+1, leased_until=DATE_ADD(NOW(), INTERVAL 60 SECOND), updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status='pending'");
        if (!$stmt) throw new RuntimeException('Nelze rezervovat prikaz Programu.');
        $stmt->bind_param('iii', $id, $swId, $groupId);
        if (!$stmt->execute()) {
            $e = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Rezervace prikazu selhala: ' . $e);
        }
        $leased = $stmt->affected_rows === 1;
        $stmt->close();
        if (!$leased) continue;

        hspa_json(200, ['ok'=>true,'command'=>[
            'id'=>$id,
            'uuid'=>(string) $row['command_uuid'],
            'customerId'=>(int) $row['customer_hs_id'],
            'programId'=>(int) $row['program_hs_id'],
            'quantity'=>(int) $row['quantity'],
        ]]);
    }

    hspa_json(200, ['ok'=>true,'command'=>null]);
}

function hspa_result(mysqli $mysqli, array $pc, array $in): never {
    hspa_ensure_queue($mysqli);
    $id = (int) ($in['commandId'] ?? 0);
    $status = trim(is_string($in['status'] ?? null) ? $in['status'] : '');
    $visitId = max(0, (int) ($in['visitId'] ?? 0));
    $visitAt = trim(is_string($in['visitAt'] ?? null) ? $in['visitAt'] : '');
    $message = trim(is_string($in['message'] ?? null) ? $in['message'] : '');
    if ($id <= 0 || !in_array($status, ['done','retry','failed'], true)) {
        throw new InvalidArgumentException('Neplatny vysledek prikazu.');
    }
    if (strlen($message) > 1000) $message = substr($message, 0, 1000);

    $swId = (int) $pc['sw_id'];
    $groupId = (int) $pc['group_id'];

    $stmt = $mysqli->prepare('SELECT id,customer_hs_id,program_hs_id,quantity,status,visit_hs_id FROM hsbridge_program_commands WHERE id=? AND sw_id=? AND group_id=? LIMIT 1');
    if (!$stmt) throw new RuntimeException('Nelze nacist prikaz Programu.');
    $stmt->bind_param('iii', $id, $swId, $groupId);
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Nacteni prikazu selhalo: ' . $e);
    }
    $res = $stmt->get_result();
    $commandRow = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!is_array($commandRow)) throw new RuntimeException('Prikaz Programu nebyl pro toto PC nalezen.');

    if ($status === 'done') {
        if ((string) ($commandRow['status'] ?? '') !== 'done') {
            $stmt = $mysqli->prepare("UPDATE hsbridge_program_commands SET status='done', visit_hs_id=?, last_error='', leased_until=NULL, updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status IN ('processing','pending')");
            if (!$stmt) throw new RuntimeException('Nelze potvrdit prikaz Programu.');
            $stmt->bind_param('iiii', $visitId, $id, $swId, $groupId);
            if (!$stmt->execute()) {
                $e = $stmt->error;
                $stmt->close();
                throw new RuntimeException('Ulozeni vysledku selhalo: ' . $e);
            }
            $stmt->close();
        } else if ($visitId <= 0) {
            $visitId = max(0, (int) ($commandRow['visit_hs_id'] ?? 0));
        }

        $mirrorSynced = false;
        try {
            $mirrorSynced = hspa_mirror_program_visit($mysqli, $pc, $commandRow, $visitId, $visitAt);
        } catch (Throwable $mirrorError) {
            error_log('HSBridge PROGRAMS immediate customer mirror failed command=' . $id . ': ' . $mirrorError->getMessage());
        }
        hspa_json(200, [
            'ok'=>true,
            'mirrorSynced'=>$mirrorSynced,
            'customerId'=>(int) ($commandRow['customer_hs_id'] ?? 0),
            'programId'=>(int) ($commandRow['program_hs_id'] ?? 0),
        ]);
    }

    if ($status === 'retry') {
        $stmt = $mysqli->prepare("UPDATE hsbridge_program_commands SET status='pending', available_at=DATE_ADD(NOW(), INTERVAL 5 SECOND), leased_until=NULL, last_error=?, updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status IN ('processing','pending')");
        if (!$stmt) throw new RuntimeException('Nelze vratit prikaz Programu do fronty.');
        $stmt->bind_param('siii', $message, $id, $swId, $groupId);
    } else {
        $stmt = $mysqli->prepare("UPDATE hsbridge_program_commands SET status='failed', leased_until=NULL, last_error=?, updated_at=NOW() WHERE id=? AND sw_id=? AND group_id=? AND status IN ('processing','pending')");
        if (!$stmt) throw new RuntimeException('Nelze ulozit chybu prikazu Programu.');
        $stmt->bind_param('siii', $message, $id, $swId, $groupId);
    }
    if (!$stmt->execute()) {
        $e = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Ulozeni vysledku selhalo: ' . $e);
    }
    $stmt->close();
    hspa_json(200, ['ok'=>true]);
}

try {
    $bridgeId = hspa_header('X-HS-Bridge-ID');
    $bridgeToken = hspa_header('X-HS-Bridge-Token');
    if ($bridgeId === '' || $bridgeToken === '') {
        hspa_json(401, ['ok'=>false,'error'=>'Chybi Bridge autentizace.']);
    }
    $pc = hspa_resolve_pc_local_first($mysqli, $bridgeId, $bridgeToken);

    $route = trim((string) ($_GET['route'] ?? ''), '/');
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($route === 'next' && $method === 'GET') {
        hspa_next($mysqli, $pc);
    }
    if ($route === 'result' && $method === 'POST') {
        $raw = file_get_contents('php://input');
        $in = json_decode(is_string($raw) ? $raw : '', true);
        if (!is_array($in)) throw new InvalidArgumentException('Neplatny JSON.');
        hspa_result($mysqli, $pc, $in);
    }
    if ($route === 'photo-next' && $method === 'GET') {
        hspa_photo_next($mysqli, $pc);
    }
    if ($route === 'photo-file' && $method === 'GET') {
        hspa_photo_file($mysqli, $pc);
    }
    if ($route === 'photo-result' && $method === 'POST') {
        $raw = file_get_contents('php://input');
        $in = json_decode(is_string($raw) ? $raw : '', true);
        if (!is_array($in)) throw new InvalidArgumentException('Neplatny JSON.');
        hspa_photo_result($mysqli, $pc, $in);
    }
    hspa_json(404, ['ok'=>false,'error'=>'Neznama PROGRAMS action route.']);
} catch (InvalidArgumentException $e) {
    hspa_json(422, ['ok'=>false,'error'=>$e->getMessage()]);
} catch (Throwable $e) {
    $message = (string) $e->getMessage();
    $code = 'endpoint';
    if (stripos($message, 'Directory') !== false || stripos($message, 'Bridge autentizace') !== false || stripos($message, 'identitu') !== false) {
        $code = 'bridge_directory';
    } elseif (stripos($message, 'PC ') !== false || stripos($message, 'Mapovani PC') !== false || stripos($message, 'HS Klient') !== false) {
        $code = 'bridge_pc';
    } elseif (stripos($message, 'frontu') !== false || stripos($message, 'prikaz') !== false || stripos($message, 'fotograf') !== false) {
        $code = 'queue';
    }
    $errorId = substr(hash('sha256', microtime(true) . '|' . getmypid() . '|' . $message), 0, 10);
    error_log('HSBridge PROGRAMS actions [' . $errorId . '/' . $code . ']: ' . $message);
    hspa_json(500, ['ok'=>false,'error'=>'PROGRAMS action endpoint selhal.','error_code'=>$code,'error_id'=>$errorId,'retryable'=>true]);
}