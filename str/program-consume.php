<?php
// HairSoft Klient V244 - fronta čerpání + stav pro okamžitou obnovu jednoho zákazníka.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    http_response_code(404);
    exit;
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

function hsProgramConsumeJson($status, array $payload)
{
    http_response_code((int) $status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function hsProgramConsumeEnsureTable($mysqli)
{
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
    return $mysqli->query($sql) !== false;
}


$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : 'GET';
if ($requestMethod !== 'POST' && $requestMethod !== 'GET') {
    hsProgramConsumeJson(405, array('ok' => false, 'error' => 'Method Not Allowed'));
}
if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
    hsProgramConsumeJson(401, array('ok' => false, 'error' => 'Přihlášení vypršelo.'));
}

require_once '../cfg/nastaveni.php';
require_once __DIR__ . '/multi_company_auth.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Databázové spojení není dostupné.'));
}
$mysqli->set_charset('utf8mb4');

$raw = file_get_contents('php://input');
$in = json_decode(is_string($raw) ? $raw : '', true);
if (!is_array($in)) {
    $in = $_POST;
}

$csrf = isset($in['csrf']) && !is_array($in['csrf']) ? (string) $in['csrf'] : '';
if (!function_exists('hsMultiCheckCsrf') || !hsMultiCheckCsrf($csrf)) {
    hsProgramConsumeJson(403, array('ok' => false, 'error' => 'Neplatný bezpečnostní token. Obnovte stránku a zkuste to znovu.'));
}

$identity = function_exists('hsMultiCurrentIdentity') ? hsMultiCurrentIdentity($mysqli) : null;
if (!$identity || !isset($identity['owner_id']) || (int) $identity['owner_id'] <= 0) {
    hsProgramConsumeJson(403, array('ok' => false, 'error' => 'Pro čerpání programu nemáte platný přístup.'));
}

if ($requestMethod === 'GET') {
    $action = isset($_GET['action']) && !is_array($_GET['action']) ? trim((string) $_GET['action']) : '';
    $commandId = isset($_GET['commandId']) && !is_array($_GET['commandId']) ? (int) $_GET['commandId'] : 0;
    if ($action !== 'status' || $commandId <= 0) {
        hsProgramConsumeJson(400, array('ok' => false, 'error' => 'Neplatná akce.'));
    }
    if (!hsProgramConsumeEnsureTable($mysqli)) {
        hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Frontu čerpání se nepodařilo načíst.'));
    }
    $ownerId = (int) $identity['owner_id'];
    $stmt = $mysqli->prepare('SELECT status,sw_id,group_id,customer_hs_id,program_hs_id,visit_hs_id,last_error FROM hsbridge_program_commands WHERE id=? AND created_by_k_id=? LIMIT 1');
    if (!$stmt) hsProgramConsumeJson(500, array('ok'=>false,'error'=>'Stav čerpání se nepodařilo načíst.'));
    $stmt->bind_param('ii', $commandId, $ownerId);
    if (!$stmt->execute()) { $stmt->close(); hsProgramConsumeJson(500, array('ok'=>false,'error'=>'Stav čerpání se nepodařilo načíst.')); }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$row) hsProgramConsumeJson(404, array('ok'=>false,'error'=>'Požadavek na čerpání nebyl nalezen.'));
    $swId = (int) ($row['sw_id'] ?? 0);
    if (!function_exists('hsMultiBranchAllowed') || !hsMultiBranchAllowed($mysqli, $identity, $swId)) {
        hsProgramConsumeJson(403, array('ok'=>false,'error'=>'K této pobočce nemáte oprávnění.'));
    }
    hsProgramConsumeJson(200, array(
        'ok'=>true,
        'status'=>(string) ($row['status'] ?? ''),
        'customerId'=>(int) ($row['customer_hs_id'] ?? 0),
        'programId'=>(int) ($row['program_hs_id'] ?? 0),
        'visitId'=>(int) ($row['visit_hs_id'] ?? 0),
        'error'=>(string) ($row['last_error'] ?? ''),
    ));
}

$customerGuid = isset($in['customerGuid']) && !is_array($in['customerGuid']) ? trim((string) $in['customerGuid']) : '';
$programId = isset($in['programId']) && !is_array($in['programId']) ? (int) $in['programId'] : 0;
$quantity = isset($in['quantity']) && !is_array($in['quantity']) ? (int) $in['quantity'] : 0;

if ($customerGuid === '' || strlen($customerGuid) > 96 || $programId <= 0 || $quantity <= 0) {
    hsProgramConsumeJson(422, array('ok' => false, 'error' => 'Zadejte platné kladné množství.'));
}

$stmt = $mysqli->prepare('SELECT COALESCE(lidi_hs_id,0) AS customer_hs_id, COALESCE(lidi_sw_id,0) AS sw_id, COALESCE(lidi_skupinaID,0) AS group_id FROM klient_lidi WHERE lidi_guid=? LIMIT 1');
if (!$stmt) {
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Zákazníka se nepodařilo načíst.'));
}
$stmt->bind_param('s', $customerGuid);
if (!$stmt->execute()) {
    $stmt->close();
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Zákazníka se nepodařilo načíst.'));
}
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

$customerId = $row && isset($row['customer_hs_id']) ? (int) $row['customer_hs_id'] : 0;
$swId = $row && isset($row['sw_id']) ? (int) $row['sw_id'] : 0;
$groupId = $row && isset($row['group_id']) ? (int) $row['group_id'] : 0;
if ($customerId <= 0 || $swId <= 0) {
    hsProgramConsumeJson(409, array('ok' => false, 'error' => 'Zákazník ještě nemá platné HairSoft ID nebo pobočku.'));
}

if (!function_exists('hsMultiBranchAllowed') || !hsMultiBranchAllowed($mysqli, $identity, $swId)) {
    hsProgramConsumeJson(403, array('ok' => false, 'error' => 'K této pobočce nemáte oprávnění.'));
}

if ($groupId <= 0) {
    $stmt = $mysqli->prepare('SELECT COALESCE(sw_skupina_id,0) AS group_id FROM sw_info WHERE sw_id=? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $swId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $groupRow = $res ? $res->fetch_assoc() : null;
            if ($groupRow && isset($groupRow['group_id'])) {
                $groupId = (int) $groupRow['group_id'];
            }
        }
        $stmt->close();
    }
}
if ($groupId <= 0) {
    hsProgramConsumeJson(409, array('ok' => false, 'error' => 'Pobočka nemá platnou skupinu HairSoft.'));
}

// Program musí patřit do synchronizované databáze stejného PC/skupiny.
// Zůstatek se záměrně NEKONTROLUJE: HairSoft je autorita a může jít i do záporu.
$stmt = $mysqli->prepare('SELECT 1 FROM hsbridge_programs WHERE sw_id=? AND group_id=? AND program_hs_id=? LIMIT 1');
if (!$stmt) {
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Program se nepodařilo ověřit.'));
}
$stmt->bind_param('iii', $swId, $groupId, $programId);
if (!$stmt->execute()) {
    $stmt->close();
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Program se nepodařilo ověřit.'));
}
$res = $stmt->get_result();
$programExists = $res && $res->num_rows > 0;
$stmt->close();
if (!$programExists) {
    hsProgramConsumeJson(409, array('ok' => false, 'error' => 'Vybraný program už není pro tuto pobočku dostupný. Obnovte data.'));
}

if (!hsProgramConsumeEnsureTable($mysqli)) {
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Frontu čerpání se nepodařilo připravit.'));
}

try {
    $uuid = bin2hex(random_bytes(16));
} catch (Throwable $e) {
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Požadavek se nepodařilo bezpečně vytvořit.'));
}
$ownerId = (int) $identity['owner_id'];
$staffId = isset($identity['principal_id']) && (string) ($identity['type'] ?? '') !== 'admin' ? (int) $identity['principal_id'] : 0;

$stmt = $mysqli->prepare("INSERT INTO hsbridge_program_commands
    (command_uuid,sw_id,group_id,customer_hs_id,customer_guid,program_hs_id,quantity,status,attempts,available_at,leased_until,visit_hs_id,last_error,created_by_k_id,created_by_staff_id,created_at,updated_at)
    VALUES (?,?,?,?,?,?,?,'pending',0,NOW(),NULL,0,'',?,?,NOW(),NOW())");
if (!$stmt) {
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Požadavek se nepodařilo zařadit do fronty.'));
}
$stmt->bind_param('siiisiiii', $uuid, $swId, $groupId, $customerId, $customerGuid, $programId, $quantity, $ownerId, $staffId);
if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    error_log('HairSoft program consume queue insert failed: ' . $err);
    hsProgramConsumeJson(500, array('ok' => false, 'error' => 'Požadavek se nepodařilo zařadit do fronty.'));
}
$commandId = (int) $mysqli->insert_id;
$stmt->close();

hsProgramConsumeJson(200, array(
    'ok' => true,
    'queued' => true,
    'commandId' => $commandId,
    'message' => 'Požadavek na čerpání byl zařazen do fronty pro HairSoft.'
));