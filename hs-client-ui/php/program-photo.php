<?php
// HairSoft Klient V241 - temporary PROGRAMS photo batches plus delivered-photo counter.
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

function hsProgramPhotoJson($status, array $payload)
{
    http_response_code((int) $status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function hsProgramPhotoStorageRoot()
{
    $base = rtrim((string) sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    return $base . DIRECTORY_SEPARATOR . 'hairsoft-klient-program-photos';
}

function hsProgramPhotoRemoveTree($dir)
{
    if (!is_string($dir) || $dir === '' || !is_dir($dir)) return;
    $items = @scandir($dir);
    if (is_array($items)) {
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) hsProgramPhotoRemoveTree($path);
            else @unlink($path);
        }
    }
    @rmdir($dir);
}

function hsProgramPhotoSafeSubfolder($value)
{
    $value = trim((string) $value);
    if ($value === '') return '';
    $value = preg_replace('/[<>:"\\\\\/|?*\x00-\x1F]+/u', ' ', $value);
    $value = preg_replace('/\s+/u', ' ', (string) $value);
    $value = trim((string) $value, " .\t\n\r\0\x0B");
    if ($value === '' || $value === '.' || $value === '..') return '';
    if (function_exists('mb_substr')) return mb_substr($value, 0, 60, 'UTF-8');
    return substr($value, 0, 60);
}

function hsProgramPhotoCleanupStale($mysqli)
{
    $cutoff = date('Y-m-d H:i:s', time() - 86400);
    $stmt = $mysqli->prepare("SELECT id,job_uuid FROM hsbridge_program_photo_jobs WHERE status IN ('pending','processing','failed') AND created_at<? LIMIT 100");
    if (!$stmt) return;
    $stmt->bind_param('s', $cutoff);
    if (!$stmt->execute()) { $stmt->close(); return; }
    $res=$stmt->get_result(); $rows=array(); while($res && ($row=$res->fetch_assoc())) $rows[]=$row; $stmt->close();
    foreach($rows as $row){
        $id=(int)$row['id'];
        hsProgramPhotoRemoveTree(hsProgramPhotoStorageRoot().DIRECTORY_SEPARATOR.(string)$row['job_uuid']);
        $q=$mysqli->prepare('DELETE FROM hsbridge_program_photo_files WHERE job_id=?'); if($q){$q->bind_param('i',$id);$q->execute();$q->close();}
        $q=$mysqli->prepare("UPDATE hsbridge_program_photo_jobs SET status='expired',leased_until=NULL,last_error='temporary files expired',updated_at=NOW() WHERE id=?"); if($q){$q->bind_param('i',$id);$q->execute();$q->close();}
    }
}

function hsProgramPhotoEnsureTables($mysqli)
{
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
    if (!$mysqli->query($jobs)) return false;

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
    return $mysqli->query($files) !== false;
}

$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : '';
if (!in_array($requestMethod, array('GET', 'POST'), true)) {
    hsProgramPhotoJson(405, array('ok' => false, 'error' => 'Method Not Allowed'));
}
if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
    hsProgramPhotoJson(401, array('ok' => false, 'error' => 'Přihlášení vypršelo.'));
}

require_once '../cfg/nastaveni.php';
require_once __DIR__ . '/multi_company_auth.php';

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    hsProgramPhotoJson(500, array('ok' => false, 'error' => 'Databázové spojení není dostupné.'));
}
$mysqli->set_charset('utf8mb4');

$identity = function_exists('hsMultiCurrentIdentity') ? hsMultiCurrentIdentity($mysqli) : null;
if (!$identity || !isset($identity['owner_id']) || (int) $identity['owner_id'] <= 0) {
    hsProgramPhotoJson(403, array('ok' => false, 'error' => 'Pro odeslání fotografií nemáte platný přístup.'));
}

$input = $requestMethod === 'GET' ? $_GET : $_POST;
$customerGuid = isset($input['customerGuid']) && !is_array($input['customerGuid']) ? trim((string) $input['customerGuid']) : '';
$programId = isset($input['programId']) && !is_array($input['programId']) ? (int) $input['programId'] : 0;
$programName = isset($input['programName']) && !is_array($input['programName']) ? trim((string) $input['programName']) : '';
$subfolder = hsProgramPhotoSafeSubfolder(isset($input['subfolder']) && !is_array($input['subfolder']) ? $input['subfolder'] : '');
if ($customerGuid === '' || strlen($customerGuid) > 96 || $programId <= 0) {
    hsProgramPhotoJson(422, array('ok' => false, 'error' => 'Fotografie se nepodařilo přiřadit k zákazníkovi a programu.'));
}
if (function_exists('mb_substr')) $programName = mb_substr($programName, 0, 255, 'UTF-8');
else $programName = substr($programName, 0, 255);

if ($requestMethod === 'POST') {
    $csrf = isset($_POST['csrf']) && !is_array($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    if (!function_exists('hsMultiCheckCsrf') || !hsMultiCheckCsrf($csrf)) {
        hsProgramPhotoJson(403, array('ok' => false, 'error' => 'Neplatný bezpečnostní token. Obnovte stránku a zkuste to znovu.'));
    }
}

$stmt = $mysqli->prepare('SELECT COALESCE(lidi_hs_id,0) AS customer_hs_id, COALESCE(lidi_sw_id,0) AS sw_id, COALESCE(lidi_skupinaID,0) AS group_id FROM klient_lidi WHERE lidi_guid=? LIMIT 1');
if (!$stmt) hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Zákazníka se nepodařilo načíst.'));
$stmt->bind_param('s', $customerGuid);
if (!$stmt->execute()) { $stmt->close(); hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Zákazníka se nepodařilo načíst.')); }
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();
$customerId = $row ? (int) ($row['customer_hs_id'] ?? 0) : 0;
$swId = $row ? (int) ($row['sw_id'] ?? 0) : 0;
$groupId = $row ? (int) ($row['group_id'] ?? 0) : 0;
if ($customerId <= 0 || $customerId === 11111111 || $swId <= 0) {
    hsProgramPhotoJson(409, array('ok'=>false,'error'=>'Zákazník ještě nemá skutečné HairSoft ID. Fotografie zatím nelze odeslat.'));
}
if (!function_exists('hsMultiBranchAllowed') || !hsMultiBranchAllowed($mysqli, $identity, $swId)) {
    hsProgramPhotoJson(403, array('ok'=>false,'error'=>'K této pobočce nemáte oprávnění.'));
}
if ($groupId <= 0) {
    $stmt = $mysqli->prepare('SELECT COALESCE(sw_skupina_id,0) AS group_id FROM sw_info WHERE sw_id=? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $swId);
        if ($stmt->execute()) { $r=$stmt->get_result(); $g=$r?$r->fetch_assoc():null; if ($g) $groupId=(int)($g['group_id']??0); }
        $stmt->close();
    }
}
if ($groupId <= 0) hsProgramPhotoJson(409, array('ok'=>false,'error'=>'Pobočka nemá platnou skupinu HairSoft.'));

$stmt = $mysqli->prepare('SELECT COALESCE(name,\'\') AS program_name FROM hsbridge_programs WHERE sw_id=? AND group_id=? AND program_hs_id=? LIMIT 1');
if (!$stmt) hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Program se nepodařilo ověřit.'));
$stmt->bind_param('iii', $swId, $groupId, $programId);
if (!$stmt->execute()) { $stmt->close(); hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Program se nepodařilo ověřit.')); }
$res=$stmt->get_result(); $programRow=$res?$res->fetch_assoc():null; $stmt->close();
if (!$programRow) hsProgramPhotoJson(409, array('ok'=>false,'error'=>'Vybraný program už není pro tuto pobočku dostupný. Obnovte data.'));
if ($programName === '') $programName = trim((string)($programRow['program_name'] ?? ''));

if ($requestMethod === 'GET') {
    $action = isset($_GET['action']) && !is_array($_GET['action']) ? trim((string) $_GET['action']) : '';
    if ($action !== 'count') {
        hsProgramPhotoJson(400, array('ok'=>false,'error'=>'Neplatná akce.'));
    }
    if (!hsProgramPhotoEnsureTables($mysqli)) {
        hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Počítadlo fotografií se nepodařilo připravit.'));
    }
    $stmt = $mysqli->prepare("SELECT COALESCE(SUM(total_files),0) AS total_count FROM hsbridge_program_photo_jobs WHERE sw_id=? AND group_id=? AND customer_hs_id=? AND program_hs_id=? AND status='done'");
    if (!$stmt) hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Počet fotografií se nepodařilo načíst.'));
    $stmt->bind_param('iiii', $swId, $groupId, $customerId, $programId);
    if (!$stmt->execute()) { $stmt->close(); hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Počet fotografií se nepodařilo načíst.')); }
    $result = $stmt->get_result();
    $countRow = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    $totalCount = $countRow && isset($countRow['total_count']) ? max(0, (int)$countRow['total_count']) : 0;
    hsProgramPhotoJson(200, array('ok'=>true,'count'=>$totalCount));
}

if (!isset($_FILES['photos']) || !is_array($_FILES['photos']['name'] ?? null)) {
    hsProgramPhotoJson(422, array('ok'=>false,'error'=>'Nejdříve vyfoťte alespoň jednu fotografii.'));
}
$names = $_FILES['photos']['name'];
$tmpNames = $_FILES['photos']['tmp_name'];
$errors = $_FILES['photos']['error'];
$sizes = $_FILES['photos']['size'];
$count = count($names);
if ($count < 1 || $count > 20) hsProgramPhotoJson(422, array('ok'=>false,'error'=>'V jednom odeslání lze předat 1 až 20 fotografií.'));

if (!hsProgramPhotoEnsureTables($mysqli)) {
    hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Frontu fotografií se nepodařilo připravit.'));
}
hsProgramPhotoCleanupStale($mysqli);

try { $jobUuid = bin2hex(random_bytes(16)); }
catch (Throwable $e) { hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Dávku fotografií se nepodařilo bezpečně vytvořit.')); }
$storageRoot = hsProgramPhotoStorageRoot();
$jobDir = $storageRoot . DIRECTORY_SEPARATOR . $jobUuid;
if (!is_dir($storageRoot) && !@mkdir($storageRoot, 0700, true)) hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Dočasné úložiště fotografií není dostupné.'));
if (!@mkdir($jobDir, 0700, false)) hsProgramPhotoJson(500, array('ok'=>false,'error'=>'Dočasnou dávku fotografií se nepodařilo vytvořit.'));

$prepared = array();
$totalBytes = 0;
try {
    for ($i=0; $i<$count; $i++) {
        $uploadError = isset($errors[$i]) ? (int)$errors[$i] : UPLOAD_ERR_NO_FILE;
        if ($uploadError !== UPLOAD_ERR_OK) throw new RuntimeException('Jednu z fotografií se nepodařilo nahrát.');
        $tmp = isset($tmpNames[$i]) ? (string)$tmpNames[$i] : '';
        $size = isset($sizes[$i]) ? (int)$sizes[$i] : 0;
        if ($tmp === '' || !is_uploaded_file($tmp) || $size <= 0) throw new RuntimeException('Jedna z fotografií je neplatná.');
        if ($size > 12 * 1024 * 1024) throw new RuntimeException('Jedna fotografie je příliš velká. Limit je 12 MB.');
        $totalBytes += $size;
        if ($totalBytes > 120 * 1024 * 1024) throw new RuntimeException('Celá dávka fotografií je příliš velká.');
        $info = @getimagesize($tmp);
        $mime = is_array($info) && isset($info['mime']) ? strtolower((string)$info['mime']) : '';
        $ext = $mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : ''));
        if ($ext === '') throw new RuntimeException('Podporované jsou fotografie JPG, PNG a WebP.');
        $fileUuid = bin2hex(random_bytes(16));
        $storedName = $fileUuid . '.' . $ext;
        $dest = $jobDir . DIRECTORY_SEPARATOR . $storedName;
        if (!@move_uploaded_file($tmp, $dest)) throw new RuntimeException('Fotografii se nepodařilo uložit do dočasné fronty.');
        @chmod($dest, 0600);
        $sha = hash_file('sha256', $dest);
        if (!is_string($sha) || strlen($sha) !== 64) throw new RuntimeException('Fotografii se nepodařilo ověřit.');
        $stamp = date('Ymd_His');
        $finalName = sprintf('%s_%s_%02d.%s', $stamp, strtoupper(substr($jobUuid, 0, 6)), $i + 1, $ext);
        $prepared[] = array('uuid'=>$fileUuid,'stored'=>$storedName,'final'=>$finalName,'mime'=>$mime,'size'=>$size,'sha'=>$sha,'sort'=>$i+1);
    }

    $tz = new DateTimeZone('Europe/Prague');
    $dateFolder = (new DateTimeImmutable('now', $tz))->format('d.m.Y');
    $ownerId = (int)$identity['owner_id'];
    $staffId = isset($identity['principal_id']) && (string)($identity['type'] ?? '') !== 'admin' ? (int)$identity['principal_id'] : 0;

    $mysqli->begin_transaction();
    $stmt = $mysqli->prepare("INSERT INTO hsbridge_program_photo_jobs (job_uuid,sw_id,group_id,customer_hs_id,customer_guid,program_hs_id,program_name,date_folder,subfolder,total_files,status,attempts,available_at,leased_until,last_error,created_by_k_id,created_by_staff_id,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,'pending',0,NOW(),NULL,'',?,?,NOW(),NOW())");
    if (!$stmt) throw new RuntimeException('Dávku fotografií se nepodařilo zařadit do fronty.');
    $totalFiles=count($prepared);
    $stmt->bind_param('siiisisssiii', $jobUuid,$swId,$groupId,$customerId,$customerGuid,$programId,$programName,$dateFolder,$subfolder,$totalFiles,$ownerId,$staffId);
    if (!$stmt->execute()) { $e=$stmt->error; $stmt->close(); throw new RuntimeException('Dávku fotografií se nepodařilo zařadit do fronty: '.$e); }
    $jobId=(int)$mysqli->insert_id; $stmt->close();

    $stmt = $mysqli->prepare('INSERT INTO hsbridge_program_photo_files (job_id,file_uuid,stored_name,final_name,mime_type,file_size,sha256,sort_order,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())');
    if (!$stmt) throw new RuntimeException('Fotografie se nepodařilo zařadit do fronty.');
    foreach ($prepared as $p) {
        $stmt->bind_param('issssisi', $jobId,$p['uuid'],$p['stored'],$p['final'],$p['mime'],$p['size'],$p['sha'],$p['sort']);
        if (!$stmt->execute()) { $e=$stmt->error; $stmt->close(); throw new RuntimeException('Fotografie se nepodařilo zařadit do fronty: '.$e); }
    }
    $stmt->close();
    $mysqli->commit();

    hsProgramPhotoJson(200, array('ok'=>true,'queued'=>true,'jobId'=>$jobId,'count'=>$totalFiles,'dateFolder'=>$dateFolder,'subfolder'=>$subfolder,'message'=>'Fotografie byly zařazeny do fronty pro HairSoft.'));
} catch (Throwable $e) {
    if ($mysqli->errno === 0) { /* no-op */ }
    try { @$mysqli->rollback(); } catch (Throwable $ignore) {}
    hsProgramPhotoRemoveTree($jobDir);
    error_log('HairSoft program photo queue: ' . $e->getMessage());
    hsProgramPhotoJson(500, array('ok'=>false,'error'=>$e->getMessage()));
}