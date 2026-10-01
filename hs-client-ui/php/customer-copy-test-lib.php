<?php
/**
 * HairSoft Klient V195 - izolovana TEST kopie zakaznika mezi zapamatovanymi firmami.
 *
 * Zdrojova firma se nikdy nemeni. Cilem je bezpecne overit stavajici synchronizaci
 * HS Klient -> HairSoft v jine firme pred tim, nez bude nekdy zavedena ostra migrace.
 */

if (!defined('HS_CUSTOMER_COPY_TEST_LOG_TABLE')) {
    define('HS_CUSTOMER_COPY_TEST_LOG_TABLE', 'k_klient_customer_copy_test_log');
}
if (!defined('HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE')) {
    define('HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE', 'k_klient_customer_copy_timeline_stage');
}

function hsCustomerCopyTestTableExists($mysqli)
{
    $result = @$mysqli->query("SHOW TABLES LIKE '" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "'");
    return $result && $result->num_rows > 0;
}

function hsCustomerCopyTestTimelineStageTableExists($mysqli)
{
    $result = @$mysqli->query("SHOW TABLES LIKE '" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "'");
    return $result && $result->num_rows > 0;
}

function hsCustomerCopyTestEnsureTimelineStageTable($mysqli)
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    if (hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
        $ready = true;
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `copy_log_id` BIGINT UNSIGNED NOT NULL,
        `source_timeline_id` BIGINT NOT NULL DEFAULT 0,
        `target_guid` VARCHAR(96) NOT NULL,
        `target_branch_id` INT NOT NULL DEFAULT 0,
        `timeline_datum_cas` DATETIME NULL,
        `timeline_text` TEXT NOT NULL,
        `timeline_obsluha` VARCHAR(255) NOT NULL DEFAULT '',
        `status` VARCHAR(16) NOT NULL DEFAULT 'waiting',
        `target_timeline_id` BIGINT NULL,
        `created_at` DATETIME NOT NULL,
        `prepared_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_hs_copy_timeline_source` (`copy_log_id`,`source_timeline_id`),
        KEY `idx_hs_copy_timeline_target` (`target_guid`,`status`),
        KEY `idx_hs_copy_timeline_log` (`copy_log_id`,`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    $ready = (@$mysqli->query($sql) !== false);
    return $ready;
}

function hsCustomerCopyTestEnsureTargetCustomerIdColumn($mysqli)
{
    $result = @$mysqli->query("SHOW COLUMNS FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` LIKE 'target_customer_id'");
    if ($result && $result->num_rows > 0) {
        return true;
    }
    if (@$mysqli->query("ALTER TABLE `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` ADD COLUMN `target_customer_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `target_guid`") === false) {
        $result = @$mysqli->query("SHOW COLUMNS FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` LIKE 'target_customer_id'");
        if (!$result || $result->num_rows === 0) {
            return false;
        }
    }
    // Index je pouze optimalizace. Pokud uz existuje nebo jej server nepovoli, funkce je stale bezpecna.
    @$mysqli->query("ALTER TABLE `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` ADD KEY `idx_hs_copy_target_customer` (`target_customer_id`)");
    return true;
}

function hsCustomerCopyTestEnsureTable($mysqli)
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    if (hsCustomerCopyTestTableExists($mysqli)) {
        $ready = hsCustomerCopyTestEnsureTargetCustomerIdColumn($mysqli);
        return $ready;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `source_guid` VARCHAR(96) NOT NULL,
        `target_guid` VARCHAR(96) NOT NULL,
        `target_customer_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
        `source_owner_k_id` INT NOT NULL,
        `target_owner_k_id` INT NOT NULL,
        `source_branch_id` INT NOT NULL,
        `target_branch_id` INT NOT NULL,
        `target_company_label` VARCHAR(255) NOT NULL DEFAULT '',
        `target_branch_name` VARCHAR(255) NOT NULL DEFAULT '',
        `customer_name` VARCHAR(255) NOT NULL DEFAULT '',
        `timeline_count` INT NOT NULL DEFAULT 0,
        `photo_count` INT NOT NULL DEFAULT 0,
        `status` VARCHAR(32) NOT NULL DEFAULT 'created',
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_hs_copy_source` (`source_owner_k_id`,`source_guid`),
        KEY `idx_hs_copy_target` (`target_owner_k_id`,`target_guid`),
        KEY `idx_hs_copy_target_customer` (`target_customer_id`),
        KEY `idx_hs_copy_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    $ready = (@$mysqli->query($sql) !== false);
    return $ready;
}


/**
 * V201: technicke datum pro odchozi Timeline zaznamy vytvorene kopii mezi firmami.
 * timelineDatumCas zustava puvodni historicke datum zobrazovane uzivateli.
 * Novy sloupec je oddeleny a stavajici HairSoft/legacy SQL jej muze bezpecne ignorovat.
 */
function hsCustomerCopyTestTimelineSyncStampColumnExists($mysqli)
{
    $result = @$mysqli->query("SHOW COLUMNS FROM `timeline` LIKE 'timelineDoPCVlozeno'");
    return ($result && $result->num_rows > 0);
}

function hsCustomerCopyTestEnsureTimelineSyncStampColumn($mysqli)
{
    if (hsCustomerCopyTestTimelineSyncStampColumnExists($mysqli)) {
        return true;
    }

    // DDL se vola pred zahajenim transakce kopie, aby neporusilo rollback zakaznika/timeline/fotek.
    $sql = "ALTER TABLE `timeline` ADD COLUMN `timelineDoPCVlozeno` DATETIME NULL AFTER `timelineDoPCSynchro`";
    if (@$mysqli->query($sql) !== false) {
        return true;
    }

    // Paralelni prvni kopie mohla sloupec vytvorit mezi kontrolou a ALTERem.
    $result = @$mysqli->query("SHOW COLUMNS FROM `timeline` LIKE 'timelineDoPCVlozeno'");
    return ($result && $result->num_rows > 0);
}

function hsCustomerCopyTestCurrentIdentity($mysqli)
{
    if (!function_exists('hsMultiCurrentIdentity')) {
        return null;
    }
    $identity = hsMultiCurrentIdentity($mysqli);
    if (!$identity || !isset($identity['type']) || !in_array((string) $identity['type'], array('admin', 'manager'), true)) {
        return null;
    }
    if ((int) $identity['owner_id'] <= 0 || (int) $identity['owner_id'] === 2) {
        return null;
    }
    return $identity;
}

// Zpetna kompatibilita pro pripadne stare include: V198 uz neni omezen jen na hlavniho administratora.
function hsCustomerCopyTestCurrentAdminIdentity($mysqli)
{
    return hsCustomerCopyTestCurrentIdentity($mysqli);
}

function hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $identity, $branchId)
{
    $branchId = (int) $branchId;
    if (!$identity || $branchId <= 0 || !isset($identity['type'], $identity['owner_id'], $identity['principal_id'])) {
        return false;
    }
    $type = (string) $identity['type'];
    if (!in_array($type, array('admin', 'manager'), true)) {
        return false;
    }
    if (!function_exists('hsMultiBranchAllowed') || !hsMultiBranchAllowed($mysqli, $identity, $branchId)) {
        return false;
    }
    if ($type === 'admin') {
        return true;
    }

    // Manazer smi kopirovat jen tam, kde ma na dane pobocce pravo pro praci s kartou/novym zakaznikem.
    $principalId = (int) $identity['principal_id'];
    if ($principalId <= 0) {
        return false;
    }
    $menu = 'NovyZakaznik';
    $stmt = $mysqli->prepare("SELECT 1 FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id`=? AND `pobocka_id`=? AND `prava_uzivatel_menu`=? AND `prava_uzivatel_pravo`=1 LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('iis', $principalId, $branchId, $menu);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $allowed = $result && $result->num_rows > 0;
    $stmt->close();
    return $allowed;
}

function hsCustomerCopyTestAllowedBranches($mysqli, $identity)
{
    $branches = array();
    if (!$identity || !isset($identity['owner_id'], $identity['type']) || !in_array((string) $identity['type'], array('admin', 'manager'), true)) {
        return $branches;
    }

    $ownerId = (int) $identity['owner_id'];
    if ($ownerId <= 0) {
        return $branches;
    }

    $stmt = $mysqli->prepare("SELECT `sw_info`.`sw_id`,`sw_info`.`sw_jmeno_pobocky`,`sw_info`.`sw_skupina_id` FROM `sw_email_pobocka` JOIN `sw_info` ON `sw_info`.`sw_id`=`sw_email_pobocka`.`sw_id` WHERE `sw_email_pobocka`.`k_id`=? ORDER BY `sw_info`.`sw_jmeno_pobocky` ASC, `sw_info`.`sw_id` ASC");
    if (!$stmt) {
        return $branches;
    }
    $stmt->bind_param('i', $ownerId);
    if (!$stmt->execute()) {
        $stmt->close();
        return $branches;
    }
    $result = $stmt->get_result();
    while ($result && ($row = $result->fetch_assoc())) {
        $branchId = (int) $row['sw_id'];
        if ($branchId <= 0 || !hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $identity, $branchId)) {
            continue;
        }
        $branches[] = array(
            'id' => $branchId,
            'name' => trim((string) $row['sw_jmeno_pobocky']) !== '' ? (string) $row['sw_jmeno_pobocky'] : ('SW ID ' . $branchId),
            'group_id' => isset($row['sw_skupina_id']) ? (int) $row['sw_skupina_id'] : 0
        );
    }
    $stmt->close();
    return $branches;
}

function hsCustomerCopyTestTargetAccounts($mysqli, $requiredSoft = 'HairSoft')
{
    $targets = array();
    $current = hsCustomerCopyTestCurrentIdentity($mysqli);
    if (!$current || !function_exists('hsMultiListAccounts') || !function_exists('hsMultiFindAccountBySelector')) {
        return $targets;
    }

    $currentOwnerId = (int) $current['owner_id'];
    $accounts = hsMultiListAccounts($mysqli, $requiredSoft);
    foreach ($accounts as $account) {
        if (!empty($account['active']) || !isset($account['selector'])) {
            continue;
        }
        $validation = hsMultiFindAccountBySelector($mysqli, (string) $account['selector']);
        if (!$validation || !isset($validation['identity'])) {
            continue;
        }
        $identity = $validation['identity'];
        if (!isset($identity['type']) || !in_array((string) $identity['type'], array('admin', 'manager'), true)) {
            continue;
        }
        if ((int) $identity['owner_id'] === $currentOwnerId) {
            continue;
        }
        if ($requiredSoft !== '' && strcasecmp((string) $requiredSoft, (string) $identity['soft']) !== 0) {
            continue;
        }
        $branches = hsCustomerCopyTestAllowedBranches($mysqli, $identity);
        if (count($branches) === 0) {
            continue;
        }
        $targets[] = array(
            'selector' => (string) $account['selector'],
            'label' => (string) $account['label'],
            'login_label' => (string) $account['login_label'],
            'type' => (string) $identity['type'],
            'owner_id' => (int) $identity['owner_id'],
            'branches' => $branches
        );
    }
    return $targets;
}

function hsCustomerCopyTestFindRememberedOwner($mysqli, $ownerId, $requiredSoft = 'HairSoft', $branchId = 0)
{
    $ownerId = (int) $ownerId;
    $branchId = (int) $branchId;
    if ($ownerId <= 0 || !function_exists('hsMultiReadCookie') || !function_exists('hsMultiValidateToken')) {
        return null;
    }
    $payload = hsMultiReadCookie();
    foreach ($payload['accounts'] as $selector => $validator) {
        $validation = hsMultiValidateToken($mysqli, $selector, $validator);
        if (!$validation || !isset($validation['identity'])) {
            continue;
        }
        $identity = $validation['identity'];
        if (!isset($identity['type']) || !in_array((string) $identity['type'], array('admin', 'manager'), true) || (int) $identity['owner_id'] !== $ownerId) {
            continue;
        }
        if ($requiredSoft !== '' && strcasecmp((string) $requiredSoft, (string) $identity['soft']) !== 0) {
            continue;
        }
        if ($branchId > 0 && !hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $identity, $branchId)) {
            continue;
        }
        return array('selector' => $selector, 'validation' => $validation);
    }
    return null;
}

// Zpetna kompatibilita s V195 nazvem; V198 umi najit i manazersky login se spravnym opravnenim.
function hsCustomerCopyTestFindRememberedAdminOwner($mysqli, $ownerId, $requiredSoft = 'HairSoft')
{
    return hsCustomerCopyTestFindRememberedOwner($mysqli, $ownerId, $requiredSoft, 0);
}

function hsCustomerCopyTestRecentLogs($mysqli, $sourceOwnerId, $sourceGuid)
{
    $items = array();
    $sourceOwnerId = (int) $sourceOwnerId;
    $sourceGuid = (string) $sourceGuid;
    if ($sourceOwnerId <= 0 || $sourceGuid === '' || !hsCustomerCopyTestTableExists($mysqli)) {
        return $items;
    }

    $stmt = $mysqli->prepare("SELECT `l`.`id`,`l`.`target_guid`,`l`.`target_owner_k_id`,`l`.`target_branch_id`,`l`.`target_company_label`,`l`.`target_branch_name`,`l`.`timeline_count`,`l`.`photo_count`,`l`.`status`,`l`.`created_at`,`k`.`lidi_web_pc`,`k`.`lidi_aktivni` FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` `l` LEFT JOIN `klient_lidi` `k` ON `k`.`lidi_guid`=`l`.`target_guid` WHERE `l`.`source_owner_k_id`=? AND `l`.`source_guid`=? ORDER BY `l`.`id` DESC LIMIT 10");
    if (!$stmt) {
        return $items;
    }
    $stmt->bind_param('is', $sourceOwnerId, $sourceGuid);
    if (!$stmt->execute()) {
        $stmt->close();
        return $items;
    }
    $result = $stmt->get_result();
    while ($result && ($row = $result->fetch_assoc())) {
        $row['target_available'] = hsCustomerCopyTestFindRememberedOwner($mysqli, (int) $row['target_owner_k_id'], 'HairSoft', (int) $row['target_branch_id']) ? 1 : 0;
        $items[] = $row;
    }
    $stmt->close();
    return $items;
}


function hsCustomerCopyTestCustomerRowByGuid($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '') {
        return null;
    }
    $stmt = $mysqli->prepare("SELECT `lidi_id`,`lidi_guid`,`lidi_hs_id`,`lidi_sw_id`,`lidi_aktivni`,`lidi_hs_name`,`lidi_hs_surname`,`lidi_hs_email`,`lidi_hs_cell`,`lidi_hs_phone`,`lidi_hs_BirthDay` FROM `klient_lidi` WHERE `lidi_guid`=? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function hsCustomerCopyTestNormalizeMatchValue($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

/**
 * V208: bezpečný fallback pro kopie V207, které ještě neměly uložené stabilní lidi_id.
 * Kandidát musí být ve stejné cílové pobočce, mít stejné jméno/příjmení jako zdroj
 * a alespoň dva shodné neprázdné identifikační údaje (e-mail/mobil/telefon/narození).
 */
function hsCustomerCopyTestRecoverLegacyTargetLog($mysqli, $currentCustomer)
{
    if (!$currentCustomer || !isset($currentCustomer['lidi_id'], $currentCustomer['lidi_sw_id']) || !hsCustomerCopyTestTableExists($mysqli)) {
        return null;
    }
    $branchId = (int) $currentCustomer['lidi_sw_id'];
    $customerId = (int) $currentCustomer['lidi_id'];
    if ($branchId <= 0 || $customerId <= 0) {
        return null;
    }

    $stmt = $mysqli->prepare("SELECT `id`,`source_guid`,`target_guid`,`target_customer_id`,`source_owner_k_id`,`target_owner_k_id`,`source_branch_id`,`target_branch_id`,`target_company_label`,`target_branch_name`,`customer_name`,`timeline_count`,`photo_count`,`status`,`created_at` FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` WHERE `target_customer_id`=0 AND `target_branch_id`=? AND `status` NOT IN ('removed','delete_requested') ORDER BY `id` DESC LIMIT 25");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $branchId);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    $matches = array();
    while ($result && ($log = $result->fetch_assoc())) {
        $source = hsCustomerCopyTestCustomerRowByGuid($mysqli, (string) $log['source_guid']);
        if (!$source) {
            continue;
        }
        if (hsCustomerCopyTestNormalizeMatchValue($source['lidi_hs_name']) !== hsCustomerCopyTestNormalizeMatchValue($currentCustomer['lidi_hs_name']) ||
            hsCustomerCopyTestNormalizeMatchValue($source['lidi_hs_surname']) !== hsCustomerCopyTestNormalizeMatchValue($currentCustomer['lidi_hs_surname'])) {
            continue;
        }
        $score = 0;
        foreach (array('lidi_hs_email','lidi_hs_cell','lidi_hs_phone','lidi_hs_BirthDay') as $field) {
            $a = hsCustomerCopyTestNormalizeMatchValue(isset($source[$field]) ? $source[$field] : '');
            $b = hsCustomerCopyTestNormalizeMatchValue(isset($currentCustomer[$field]) ? $currentCustomer[$field] : '');
            if ($a !== '' && $b !== '' && $a === $b) {
                $score++;
            }
        }
        if ($score >= 2) {
            $matches[] = $log;
        }
    }
    $stmt->close();
    return count($matches) === 1 ? $matches[0] : null;
}

/**
 * V208: pokud legacy synchronizace změnila GUID webové kopie, přepojíme pouze izolovaná
 * data cílové TEST kopie na aktuální GUID. Zdrojová firma se nikdy nemění.
 */
function hsCustomerCopyTestQuarantineLegacyTimelineForGuid($mysqli, $copyLogId, $sourceGuid, $oldTargetGuid, $targetBranchId)
{
    $copyLogId = (int) $copyLogId;
    $sourceGuid = trim((string) $sourceGuid);
    $oldTargetGuid = trim((string) $oldTargetGuid);
    $targetBranchId = (int) $targetBranchId;
    if ($copyLogId <= 0 || $sourceGuid === '' || $oldTargetGuid === '' || $sourceGuid === $oldTargetGuid || $targetBranchId <= 0) {
        return 0;
    }

    // Mazeme jen tehdy, kdy uz existuje izolovany snapshot teto konkretni kopie.
    // Bez snapshotu se stare V205/V206 radky automaticky nedotykaji.
    if (!hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
        return 0;
    }
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS `c` FROM `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` WHERE `copy_log_id`=?");
    $stageCount = 0;
    if ($stmt) {
        $stmt->bind_param('i', $copyLogId);
        if ($stmt->execute()) { $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; if($row){$stageCount=(int)$row['c'];} }
        $stmt->close();
    }
    if ($stageCount <= 0) {
        return 0;
    }

    // Odstranime pouze nesynchronizovane klony, ktere se PRESNE shoduji s historickou
    // zdrojovou Timeline (datum + text + obsluha). Rucne nove poznamky tim nejsou dotceny.
    $sql = "DELETE `t` FROM `timeline` `t`
            INNER JOIN `timeline` `s`
              ON `s`.`timelineOsobaGuid`=? AND `s`.`timelineValid`=1
             AND (`s`.`timelineDatumCas` <=> `t`.`timelineDatumCas`)
             AND COALESCE(`s`.`timelineText`,'')=COALESCE(`t`.`timelineText`,'')
             AND COALESCE(`s`.`timelineObsluha`,'')=COALESCE(`t`.`timelineObsluha`,'')
            WHERE `t`.`timelineOsobaGuid`=?
              AND `t`.`timelineValid`=1
              AND `t`.`timelineIDHS` IS NULL
              AND `t`.`timelineDoPCZnak` IN ('H','N','U')";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param('ss', $sourceGuid, $oldTargetGuid);
    if (!$stmt->execute()) { $stmt->close(); return 0; }
    $count = max(0, (int) $stmt->affected_rows);
    $stmt->close();
    return $count;
}

function hsCustomerCopyTestRebindTargetIdentity($mysqli, $log, $currentCustomer)
{
    if (!$log || !$currentCustomer || !isset($log['id'], $log['target_guid'], $currentCustomer['lidi_id'], $currentCustomer['lidi_guid'])) {
        return false;
    }
    $logId = (int) $log['id'];
    $customerId = (int) $currentCustomer['lidi_id'];
    $oldGuid = trim((string) $log['target_guid']);
    $newGuid = trim((string) $currentCustomer['lidi_guid']);
    if ($logId <= 0 || $customerId <= 0 || $newGuid === '') {
        return false;
    }

    $transactionStarted = false;
    try {
        if (!$mysqli->begin_transaction()) {
            return false;
        }
        $transactionStarted = true;

        if ($oldGuid !== '' && $oldGuid !== $newGuid) {
            hsCustomerCopyTestQuarantineLegacyTimelineForGuid($mysqli, $logId, isset($log['source_guid']) ? (string) $log['source_guid'] : '', $oldGuid, isset($log['target_branch_id']) ? (int) $log['target_branch_id'] : 0);
            if (hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
                $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` SET `target_guid`=? WHERE `copy_log_id`=?");
                if ($stmt) { $stmt->bind_param('si', $newGuid, $logId); if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('stage rebind'); } $stmt->close(); }
            }
            $stmt = $mysqli->prepare("UPDATE `klient_lidi_obrazky` SET `obrazek_osoba_GUID`=? WHERE `obrazek_osoba_GUID`=?");
            if ($stmt) { $stmt->bind_param('ss', $newGuid, $oldGuid); if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('photo rebind'); } $stmt->close(); }
            $stmt = $mysqli->prepare("UPDATE `soubory` SET `guid_cloveka`=? WHERE `guid_cloveka`=?");
            if ($stmt) { $stmt->bind_param('ss', $newGuid, $oldGuid); if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('file rebind'); } $stmt->close(); }
            if (function_exists('hsCustomerSyncHoldTableExists') && hsCustomerSyncHoldTableExists($mysqli)) {
                $stmt = $mysqli->prepare("SELECT `branch_id`,`status`,`last_hs_id`,`last_error`,`created_at`,`updated_at`,`released_at` FROM `k_klient_customer_sync_hold` WHERE `customer_guid`=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('s', $oldGuid);
                    if ($stmt->execute()) {
                        $r = $stmt->get_result(); $q = $r ? $r->fetch_assoc() : null;
                        if ($q) {
                            $branchId=(int)$q['branch_id']; $status=(string)$q['status']; $lastHs=(string)$q['last_hs_id'];
                            $ins=$mysqli->prepare("INSERT INTO `k_klient_customer_sync_hold` (`customer_guid`,`branch_id`,`status`,`last_hs_id`,`last_error`,`created_at`,`updated_at`,`released_at`) VALUES (?,?,?,?,'',NOW(),NOW(),NULL) ON DUPLICATE KEY UPDATE `branch_id`=VALUES(`branch_id`),`status`=VALUES(`status`),`last_hs_id`=VALUES(`last_hs_id`),`updated_at`=NOW()");
                            if ($ins) { $ins->bind_param('siss',$newGuid,$branchId,$status,$lastHs); if(!$ins->execute()){ $ins->close(); $stmt->close(); throw new RuntimeException('queue rebind'); } $ins->close(); }
                        }
                    }
                    $stmt->close();
                }
                $del=$mysqli->prepare("DELETE FROM `k_klient_customer_sync_hold` WHERE `customer_guid`=?");
                if ($del) { $del->bind_param('s',$oldGuid); if(!$del->execute()){ $del->close(); throw new RuntimeException('queue cleanup'); } $del->close(); }
            }
        }

        $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` SET `target_guid`=?,`target_customer_id`=?,`updated_at`=NOW() WHERE `id`=?");
        if (!$stmt) { throw new RuntimeException('log rebind'); }
        $stmt->bind_param('sii', $newGuid, $customerId, $logId);
        if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('log rebind'); }
        $stmt->close();

        if (!$mysqli->commit()) { throw new RuntimeException('commit'); }
        $transactionStarted = false;
        $log['target_guid'] = $newGuid;
        $log['target_customer_id'] = $customerId;
        return $log;
    } catch (Throwable $e) {
        if ($transactionStarted) { @$mysqli->rollback(); }
        return false;
    }
}

function hsCustomerCopyTestTargetLog($mysqli, $targetOwnerId, $targetGuid)
{
    $targetOwnerId = (int) $targetOwnerId;
    $targetGuid = trim((string) $targetGuid);
    if ($targetGuid === '' || !hsCustomerCopyTestEnsureTable($mysqli)) {
        return null;
    }
    $currentCustomer = hsCustomerCopyTestCustomerRowByGuid($mysqli, $targetGuid);
    if (!$currentCustomer) {
        return null;
    }
    $customerId = (int) $currentCustomer['lidi_id'];

    $select = "SELECT `l`.`id`,`l`.`source_guid`,`l`.`source_owner_k_id`,`l`.`target_guid`,`l`.`target_customer_id`,`l`.`target_owner_k_id`,`l`.`target_branch_id`,`l`.`target_company_label`,`l`.`target_branch_name`,`l`.`customer_name`,`l`.`timeline_count`,`l`.`photo_count`,`l`.`status`,`l`.`created_at`,`k`.`lidi_hs_id`,`k`.`lidi_web_pc`,`k`.`lidi_aktivni`,`k`.`lidi_sw_id` FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` `l` LEFT JOIN `klient_lidi` `k` ON `k`.`lidi_id`=`l`.`target_customer_id` ";

    if ($customerId > 0) {
        $stmt = $mysqli->prepare($select . "WHERE `l`.`target_customer_id`=? AND `l`.`status` NOT IN ('removed','delete_requested') ORDER BY `l`.`id` DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $customerId);
            if ($stmt->execute()) { $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; $stmt->close(); if($row){ if((string)$row['target_guid']!==$targetGuid){ $reb=hsCustomerCopyTestRebindTargetIdentity($mysqli,$row,$currentCustomer); if($reb){ $row=$reb + $row; $row['lidi_hs_id']=$currentCustomer['lidi_hs_id']; $row['lidi_sw_id']=$currentCustomer['lidi_sw_id']; $row['lidi_aktivni']=$currentCustomer['lidi_aktivni']; } } return $row; } } else { $stmt->close(); }
        }
    }

    // Zpetna kompatibilita: V195-V207 znaly pouze target_guid.
    $selectGuid = "SELECT `l`.`id`,`l`.`source_guid`,`l`.`source_owner_k_id`,`l`.`target_guid`,`l`.`target_customer_id`,`l`.`target_owner_k_id`,`l`.`target_branch_id`,`l`.`target_company_label`,`l`.`target_branch_name`,`l`.`customer_name`,`l`.`timeline_count`,`l`.`photo_count`,`l`.`status`,`l`.`created_at`,`k`.`lidi_hs_id`,`k`.`lidi_web_pc`,`k`.`lidi_aktivni`,`k`.`lidi_sw_id` FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` `l` LEFT JOIN `klient_lidi` `k` ON `k`.`lidi_guid`=`l`.`target_guid` WHERE `l`.`target_guid`=? AND `l`.`status` NOT IN ('removed','delete_requested') ORDER BY `l`.`id` DESC LIMIT 1";
    $stmt = $mysqli->prepare($selectGuid);
    if ($stmt) {
        $stmt->bind_param('s', $targetGuid);
        if ($stmt->execute()) {
            $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; $stmt->close();
            if ($row) {
                $reb = hsCustomerCopyTestRebindTargetIdentity($mysqli, $row, $currentCustomer);
                if ($reb) { $row = $reb + $row; }
                $row['lidi_hs_id']=$currentCustomer['lidi_hs_id']; $row['lidi_sw_id']=$currentCustomer['lidi_sw_id']; $row['lidi_aktivni']=$currentCustomer['lidi_aktivni'];
                return $row;
            }
        } else { $stmt->close(); }
    }

    // V207 recovery: pokud se GUID po prvnim PC syncu zmenil, dohledame JEDINY bezpecny
    // legacy audit podle cilove pobocky + shody zdrojovych zakladnich udaju a ulozime stabilni lidi_id.
    $legacy = hsCustomerCopyTestRecoverLegacyTargetLog($mysqli, $currentCustomer);
    if ($legacy) {
        $reb = hsCustomerCopyTestRebindTargetIdentity($mysqli, $legacy, $currentCustomer);
        if ($reb) {
            $reb['lidi_hs_id']=$currentCustomer['lidi_hs_id']; $reb['lidi_sw_id']=$currentCustomer['lidi_sw_id']; $reb['lidi_aktivni']=$currentCustomer['lidi_aktivni'];
            return $reb;
        }
    }
    return null;
}

function hsCustomerCopyTestTimelineStageRowsByLog($mysqli, $copyLogId)
{
    $items = array();
    $copyLogId = (int) $copyLogId;
    if ($copyLogId <= 0 || !hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
        return $items;
    }
    $stmt = $mysqli->prepare("SELECT `id`,`copy_log_id`,`source_timeline_id`,`target_guid`,`target_branch_id`,`timeline_datum_cas`,`timeline_text`,`timeline_obsluha`,`status`,`target_timeline_id`,`created_at`,`prepared_at` FROM `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` WHERE `copy_log_id`=? AND `status`='waiting' ORDER BY `timeline_datum_cas` DESC,`id` DESC");
    if (!$stmt) { return $items; }
    $stmt->bind_param('i', $copyLogId);
    if ($stmt->execute()) { $r=$stmt->get_result(); while($r && ($row=$r->fetch_assoc())){ $items[]=$row; } }
    $stmt->close();
    return $items;
}

function hsCustomerCopyTestPurgeUnexpectedPendingTimeline($mysqli, $log, $currentGuid)
{
    $currentGuid = trim((string) $currentGuid);
    if (!$log || !isset($log['id'], $log['source_guid']) || $currentGuid === '' || !hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
        return 0;
    }
    $copyLogId = (int) $log['id'];
    $sourceGuid = trim((string) $log['source_guid']);
    if ($copyLogId <= 0 || $sourceGuid === '') { return 0; }

    // Tato pojistka se aktivuje jen u kopie, ktera skutecne ma V207+ staging.
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS `c` FROM `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` WHERE `copy_log_id`=?");
    if (!$stmt) { return 0; }
    $stmt->bind_param('i', $copyLogId);
    if (!$stmt->execute()) { $stmt->close(); return 0; }
    $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; $stageCount=$row?(int)$row['c']:0; $stmt->close();
    if ($stageCount <= 0) { return 0; }

    $sql = "DELETE `t` FROM `timeline` `t`
            INNER JOIN `timeline` `s`
              ON `s`.`timelineOsobaGuid`=? AND `s`.`timelineValid`=1
             AND (`s`.`timelineDatumCas` <=> `t`.`timelineDatumCas`)
             AND COALESCE(`s`.`timelineText`,'')=COALESCE(`t`.`timelineText`,'')
             AND COALESCE(`s`.`timelineObsluha`,'')=COALESCE(`t`.`timelineObsluha`,'')
            WHERE `t`.`timelineOsobaGuid`=?
              AND `t`.`timelineValid`=1
              AND `t`.`timelineIDHS` IS NULL
              AND `t`.`timelineDoPCZnak` IN ('H','N','U')
              AND NOT EXISTS (
                SELECT 1 FROM `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` `st`
                WHERE `st`.`copy_log_id`=? AND `st`.`status` IN ('prepared','sent') AND `st`.`target_timeline_id`=`t`.`timelineID`
              )";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) { return 0; }
    $stmt->bind_param('ssi', $sourceGuid, $currentGuid, $copyLogId);
    if (!$stmt->execute()) { $stmt->close(); return 0; }
    $count=max(0,(int)$stmt->affected_rows); $stmt->close();
    return $count;
}

function hsCustomerCopyTestGenerateGuid()
{
    if (function_exists('GUID')) {
        $guid = (string) GUID();
        if ($guid !== '') {
            return $guid;
        }
    }
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function hsCustomerCopyTestSafeSourceValue($row, $key, $default = '')
{
    if (!is_array($row) || !array_key_exists($key, $row) || $row[$key] === null) {
        return $default;
    }
    return (string) $row[$key];
}

function hsCustomerCopyTestInsertCustomer($mysqli, $source, $targetGuid, $targetBranchId, $targetGroupId)
{
    $fields = array(
        'lidi_hs_id' => '11111111',
        'lidi_hs_name' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_name'),
        'lidi_hs_surname' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_surname'),
        'lidi_hs_title' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_title'),
        'lidi_hs_genre' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_genre', '0'),
        'lidi_hs_email' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_email'),
        'lidi_hs_street' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_street'),
        'lidi_hs_city' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_city'),
        'lidi_hs_zip' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_zip'),
        'lidi_hs_phone' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_phone'),
        'lidi_hs_cell' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_cell'),
        'lidi_hs_BirthDay' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_BirthDay'),
        'lidi_hs_firm_name' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_firm_name'),
        'lidi_hs_firm_register_number' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_firm_register_number'),
        'lidi_hs_www' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_www'),
        'lidi_hs_skype' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_skype'),
        'lidi_hs_facebook' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_facebook'),
        'lidi_hs_note' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_note'),
        // Karta/bonusy/blacklist jsou firemni stav. Testovaci kopie je zamerne neprebira.
        'lidi_hs_card' => '',
        // Respektujeme blokaci SMS zdrojove osoby; je bezpecnejsi ji zachovat nez nechtene povolit marketing.
        'lidi_hs_NoSMS' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_NoSMS', '1'),
        'lidi_hs_loyalityPoints' => '0',
        'lidi_zmena' => date('Y-m-d H:i:s'),
        'lidi_zalozeno' => date('Y-m-d H:i:s'),
        'lidi_aktivni' => '1',
        'lidi_sw_id' => (string) ((int) $targetBranchId),
        'lidi_skupinaID' => (string) ((int) $targetGroupId),
        'lidi_web_pc' => 'N',
        'lidi_guid' => (string) $targetGuid,
        'lidi_hs_pojistovnaGUID' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_pojistovnaGUID'),
        'lidi_hs_ostatni' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_ostatni'),
        'lidi_hs_rc' => hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_rc')
    );

    $columns = array();
    $placeholders = array();
    $values = array();
    foreach ($fields as $column => $value) {
        $columns[] = '`' . str_replace('`', '``', $column) . '`';
        $placeholders[] = '?';
        $values[] = $value;
    }
    $sql = "INSERT INTO `klient_lidi` (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se připravit založení cílového zákazníka.');
    }
    $types = str_repeat('s', count($values));
    $refs = array($types);
    foreach ($values as $i => $value) {
        $refs[] = &$values[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), $refs);
    if (!$stmt->execute()) {
        $message = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Cílového zákazníka se nepodařilo založit: ' . $message);
    }
    $insertId = (int) $stmt->insert_id;
    $stmt->close();
    if ($insertId <= 0) {
        $check = hsCustomerCopyTestCustomerRowByGuid($mysqli, (string) $targetGuid);
        $insertId = $check && isset($check['lidi_id']) ? (int) $check['lidi_id'] : 0;
    }
    if ($insertId <= 0) {
        throw new RuntimeException('Cílového zákazníka se podařilo vložit, ale nepodařilo se zjistit jeho stabilní databázové ID.');
    }
    return $insertId;
}

function hsCustomerCopyTestCountTimeline($mysqli, $sourceGuid)
{
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS `c` FROM `timeline` WHERE `timelineOsobaGuid`=? AND `timelineValid`=1");
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se spočítat Timeline zákazníka.');
    }
    $stmt->bind_param('s', $sourceGuid);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Nepodařilo se spočítat Timeline zákazníka.');
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ? max(0, (int) $row['c']) : 0;
}

/**
 * V207: kopirovana historicka Timeline se NESMI vlozit do produkcni tabulky timeline
 * pred explicitnim kliknutim administratora. Legacy HairSoft totiz stav H nespolehlive
 * respektuje. Snapshot proto drzi izolovana staging tabulka mimo dosah stare synchronizace.
 */
function hsCustomerCopyTestStageTimeline($mysqli, $copyLogId, $sourceGuid, $targetGuid, $targetBranchId)
{
    if (!hsCustomerCopyTestEnsureTimelineStageTable($mysqli)) {
        throw new RuntimeException('Nepodařilo se připravit bezpečný mezisklad Timeline.');
    }
    $copyLogId = (int) $copyLogId;
    $targetBranchId = (int) $targetBranchId;
    if ($copyLogId <= 0 || $sourceGuid === '' || $targetGuid === '' || $targetBranchId <= 0) {
        throw new RuntimeException('Timeline se nepodařilo bezpečně připravit.');
    }

    $sql = "INSERT INTO `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "`
            (`copy_log_id`,`source_timeline_id`,`target_guid`,`target_branch_id`,`timeline_datum_cas`,`timeline_text`,`timeline_obsluha`,`status`,`target_timeline_id`,`created_at`,`prepared_at`)
            SELECT ?,`timelineID`,?,?,`timelineDatumCas`,COALESCE(`timelineText`,''),COALESCE(`timelineObsluha`,''),'waiting',NULL,NOW(),NULL
            FROM `timeline`
            WHERE `timelineOsobaGuid`=? AND `timelineValid`=1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se připravit snapshot Timeline.');
    }
    $stmt->bind_param('isis', $copyLogId, $targetGuid, $targetBranchId, $sourceGuid);
    if (!$stmt->execute()) {
        $message = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Timeline se nepodařilo uložit do meziskladu: ' . $message);
    }
    $count = max(0, (int) $stmt->affected_rows);
    $stmt->close();
    return $count;
}

function hsCustomerCopyTestTimelineStageRows($mysqli, $targetGuid)
{
    $items = array();
    $targetGuid = trim((string) $targetGuid);
    if ($targetGuid === '' || !hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
        return $items;
    }
    $stmt = $mysqli->prepare("SELECT `id`,`copy_log_id`,`source_timeline_id`,`target_guid`,`target_branch_id`,`timeline_datum_cas`,`timeline_text`,`timeline_obsluha`,`status`,`target_timeline_id`,`created_at`,`prepared_at` FROM `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` WHERE `target_guid`=? AND `status`='waiting' ORDER BY `timeline_datum_cas` DESC,`id` DESC");
    if (!$stmt) {
        return $items;
    }
    $stmt->bind_param('s', $targetGuid);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($result && ($row = $result->fetch_assoc())) {
            $items[] = $row;
        }
    }
    $stmt->close();
    return $items;
}

function hsCustomerCopyTestDeleteTimelineStageByTarget($mysqli, $targetGuid)
{
    $targetGuid = trim((string) $targetGuid);
    if ($targetGuid === '' || !hsCustomerCopyTestTimelineStageTableExists($mysqli)) {
        return true;
    }
    $stmt = $mysqli->prepare("DELETE FROM `" . HS_CUSTOMER_COPY_TIMELINE_STAGE_TABLE . "` WHERE `target_guid`=?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $targetGuid);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool) $ok;
}

function hsCustomerCopyTestLoadPhotoRows($mysqli, $sourceGuid)
{
    $rows = array();
    $stmt = $mysqli->prepare("SELECT * FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID`=? AND `obrazek_smazan`=0");
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se načíst fotografie zákazníka.');
    }
    $stmt->bind_param('s', $sourceGuid);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Nepodařilo se načíst fotografie zákazníka.');
    }
    $result = $stmt->get_result();
    while ($result && ($row = $result->fetch_assoc())) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function hsCustomerCopyTestPhotoColumns($mysqli)
{
    $columns = array();
    $result = $mysqli->query("SHOW COLUMNS FROM `klient_lidi_obrazky`");
    if (!$result) {
        throw new RuntimeException('Nepodařilo se ověřit strukturu fotografií.');
    }
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    return $columns;
}

function hsCustomerCopyTestClonePhotoMetadata($mysqli, $columnInfo, $sourceRow, $targetGuid, $targetFileName, $targetBranchId, $targetGroupId)
{
    $columns = array();
    $placeholders = array();
    $values = array();

    foreach ($columnInfo as $info) {
        $field = isset($info['Field']) ? (string) $info['Field'] : '';
        if ($field === '') {
            continue;
        }
        $extra = isset($info['Extra']) ? strtolower((string) $info['Extra']) : '';
        $key = isset($info['Key']) ? strtoupper((string) $info['Key']) : '';
        if (strpos($extra, 'auto_increment') !== false) {
            continue;
        }

        $lower = strtolower($field);
        // Neznamy rucne spravovany primarni/unikatni klic nesmime slepe prevzit ze zdroje.
        // Dve zname GUID vazby umime bezpecne premapovat na novou osobu/soubor.
        if (($key === 'PRI' || $key === 'UNI') && $lower !== 'obrazek_osoba_guid' && $lower !== 'obrazek_jmeno_guid') {
            throw new RuntimeException('Struktura fotografií obsahuje nepodporovaný unikátní klíč ' . $field . '. Kopie byla bezpečně zastavena.');
        }
        if ($lower === 'obrazek_osoba_guid') {
            $value = $targetGuid;
        } elseif ($lower === 'obrazek_jmeno_guid') {
            $value = $targetFileName;
        } elseif ($lower === 'obrazek_stazeno') {
            // V205: fotografie se v cili nejprve drzi ve stavu 2. U TEST kopie je na 0
            // uvolni az rucni zadani skutecneho lokalniho HairSoft ID ve Sprave dat.
            $value = '2';
        } elseif ($lower === 'obrazek_smazan') {
            $value = '0';
        } elseif ($lower === 'obrazek_profilovka') {
            $value = isset($sourceRow[$field]) ? (string) $sourceRow[$field] : '0';
        } elseif (preg_match('/(^|_)sw(_|$).*id|(^|_)sw_id$/i', $field) || stripos($lower, 'pobocka') !== false) {
            $value = (string) ((int) $targetBranchId);
        } elseif (stripos($lower, 'skupina') !== false && stripos($lower, 'id') !== false) {
            $value = (string) ((int) $targetGroupId);
        } elseif (array_key_exists($field, $sourceRow)) {
            $value = $sourceRow[$field];
        } elseif (array_key_exists('Default', $info) && $info['Default'] !== null) {
            $value = $info['Default'];
        } else {
            $value = '';
        }

        $columns[] = '`' . str_replace('`', '``', $field) . '`';
        $placeholders[] = '?';
        $values[] = $value === null ? null : (string) $value;
    }

    if (count($columns) === 0) {
        throw new RuntimeException('Struktura fotografií neobsahuje kopírovatelná pole.');
    }

    $stmt = $mysqli->prepare("INSERT INTO `klient_lidi_obrazky` (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")");
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se připravit metadata fotografie.');
    }
    $types = str_repeat('s', count($values));
    $refs = array($types);
    foreach ($values as $i => $value) {
        $refs[] = &$values[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), $refs);
    if (!$stmt->execute()) {
        $message = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Metadata fotografie se nepodařilo zkopírovat: ' . $message);
    }
    $stmt->close();
}

function hsCustomerCopyTestBuildPhotoPlan($photoRows)
{
    $plan = array();
    if (count($photoRows) === 0) {
        return $plan;
    }
    $galleryDir = defined('HS_CLIENT_UI_ROOT') ? HS_CLIENT_UI_ROOT . '/str/strana/galerie/' : '';
    if ($galleryDir === '' || !is_dir($galleryDir) || !is_readable($galleryDir) || !is_writable($galleryDir)) {
        throw new RuntimeException('Adresář fotografií není pro bezpečnou kopii dostupný. Zdrojová firma zůstala beze změny.');
    }

    foreach ($photoRows as $row) {
        $sourceName = isset($row['obrazek_jmeno_GUID']) ? (string) $row['obrazek_jmeno_GUID'] : '';
        if ($sourceName === '' || basename($sourceName) !== $sourceName) {
            throw new RuntimeException('Jedna z fotografií má neplatný název a kopie byla zastavena.');
        }
        $sourcePath = $galleryDir . $sourceName;
        $sourceThumb = $galleryDir . 'm' . $sourceName;
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            throw new RuntimeException('Fotografie ' . $sourceName . ' chybí na serveru. Kopie nebyla provedena.');
        }

        $extension = pathinfo($sourceName, PATHINFO_EXTENSION);
        $targetName = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . strtolower($extension) : '');
        while (is_file($galleryDir . $targetName) || is_file($galleryDir . 'm' . $targetName)) {
            $targetName = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . strtolower($extension) : '');
        }
        $plan[] = array(
            'row' => $row,
            'source' => $sourcePath,
            'source_thumb' => $sourceThumb,
            'target_name' => $targetName,
            'target' => $galleryDir . $targetName,
            'target_thumb' => $galleryDir . 'm' . $targetName
        );
    }
    return $plan;
}

function hsCustomerCopyTestCopyPhotoFiles($plan, &$createdFiles)
{
    foreach ($plan as $item) {
        if (!@copy($item['source'], $item['target'])) {
            throw new RuntimeException('Fotografii se nepodařilo fyzicky zkopírovat.');
        }
        $createdFiles[] = $item['target'];
        $thumbSource = is_file($item['source_thumb']) ? $item['source_thumb'] : $item['source'];
        if (!@copy($thumbSource, $item['target_thumb'])) {
            throw new RuntimeException('Miniaturu fotografie se nepodařilo zkopírovat.');
        }
        $createdFiles[] = $item['target_thumb'];
    }
}

function hsCustomerCopyTestDeleteFiles($files)
{
    foreach ($files as $file) {
        if (is_string($file) && $file !== '') {
            @unlink($file);
        }
    }
}

function hsCustomerCopyTestTargetDuplicateExists($mysqli, $source, $targetGroupId)
{
    $conditions = array();
    $values = array();
    $types = '';
    $cell = trim(hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_cell'));
    $phone = trim(hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_phone'));
    $email = trim(hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_email'));

    if ($cell !== '') {
        $conditions[] = '`lidi_hs_cell`=?';
        $values[] = $cell;
        $types .= 's';
    }
    if ($phone !== '') {
        $conditions[] = '`lidi_hs_phone`=?';
        $values[] = $phone;
        $types .= 's';
    }
    if ($email !== '') {
        $conditions[] = 'LOWER(`lidi_hs_email`)=LOWER(?)';
        $values[] = $email;
        $types .= 's';
    }
    if (count($conditions) === 0) {
        return false;
    }

    $sql = "SELECT `lidi_guid`,`lidi_hs_name`,`lidi_hs_surname` FROM `klient_lidi` WHERE `lidi_aktivni`=1 AND `lidi_skupinaID`=? AND (" . implode(' OR ', $conditions) . ") LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se ověřit duplicitu zákazníka.');
    }
    $targetGroupId = (int) $targetGroupId;
    $allValues = array_merge(array($targetGroupId), $values);
    $allTypes = 'i' . $types;
    $refs = array($allTypes);
    foreach ($allValues as $i => $value) {
        $refs[] = &$allValues[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), $refs);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Nepodařilo se ověřit duplicitu zákazníka.');
    }
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function hsCustomerCopyTestExistingActiveCopy($mysqli, $sourceOwnerId, $sourceGuid, $targetOwnerId, $targetBranchId)
{
    if (!hsCustomerCopyTestTableExists($mysqli)) {
        return false;
    }
    $stmt = $mysqli->prepare("SELECT `l`.`id` FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` `l` JOIN `klient_lidi` `k` ON `k`.`lidi_guid`=`l`.`target_guid` WHERE `l`.`source_owner_k_id`=? AND `l`.`source_guid`=? AND `l`.`target_owner_k_id`=? AND `l`.`target_branch_id`=? AND `l`.`status` IN ('created','delete_requested') AND `k`.`lidi_aktivni`=1 LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $sourceOwnerId = (int) $sourceOwnerId;
    $targetOwnerId = (int) $targetOwnerId;
    $targetBranchId = (int) $targetBranchId;
    $stmt->bind_param('isii', $sourceOwnerId, $sourceGuid, $targetOwnerId, $targetBranchId);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

function hsCustomerCopyTestInsertLog($mysqli, $data)
{
    if (!hsCustomerCopyTestEnsureTable($mysqli)) {
        throw new RuntimeException('Nepodařilo se připravit testovací audit kopie.');
    }
    $stmt = $mysqli->prepare("INSERT INTO `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` (`source_guid`,`target_guid`,`target_customer_id`,`source_owner_k_id`,`target_owner_k_id`,`source_branch_id`,`target_branch_id`,`target_company_label`,`target_branch_name`,`customer_name`,`timeline_count`,`photo_count`,`status`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'created',NOW(),NOW())");
    if (!$stmt) {
        throw new RuntimeException('Nepodařilo se uložit testovací audit kopie.');
    }
    $sourceGuid = (string) $data['source_guid'];
    $targetGuid = (string) $data['target_guid'];
    $targetCustomerId = isset($data['target_customer_id']) ? (int) $data['target_customer_id'] : 0;
    $sourceOwnerId = (int) $data['source_owner_k_id'];
    $targetOwnerId = (int) $data['target_owner_k_id'];
    $sourceBranchId = (int) $data['source_branch_id'];
    $targetBranchId = (int) $data['target_branch_id'];
    $companyLabel = (string) $data['target_company_label'];
    $branchName = (string) $data['target_branch_name'];
    $customerName = (string) $data['customer_name'];
    $timelineCount = (int) $data['timeline_count'];
    $photoCount = (int) $data['photo_count'];
    $stmt->bind_param('ssiiiiisssii', $sourceGuid, $targetGuid, $targetCustomerId, $sourceOwnerId, $targetOwnerId, $sourceBranchId, $targetBranchId, $companyLabel, $branchName, $customerName, $timelineCount, $photoCount);
    if (!$stmt->execute()) {
        $message = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Nepodařilo se uložit testovací audit kopie: ' . $message);
    }
    $id = (int) $stmt->insert_id;
    $stmt->close();
    return $id;
}

function hsCustomerCopyTestCompensateTarget($mysqli, $targetGuid)
{
    if (!is_string($targetGuid) || $targetGuid === '') {
        return;
    }
    if (function_exists('hsCustomerCopyTestDeleteTimelineStageByTarget')) {
        hsCustomerCopyTestDeleteTimelineStageByTarget($mysqli, $targetGuid);
    }
    $stmt = $mysqli->prepare("DELETE FROM `timeline` WHERE `timelineOsobaGuid`=?");
    if ($stmt) { $stmt->bind_param('s', $targetGuid); @$stmt->execute(); $stmt->close(); }
    $stmt = $mysqli->prepare("DELETE FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID`=?");
    if ($stmt) { $stmt->bind_param('s', $targetGuid); @$stmt->execute(); $stmt->close(); }
    $stmt = $mysqli->prepare("DELETE FROM `klient_lidi` WHERE `lidi_guid`=?");
    if ($stmt) { $stmt->bind_param('s', $targetGuid); @$stmt->execute(); $stmt->close(); }
    if (function_exists('hsCustomerSyncHoldRemoveQueueGuid')) {
        hsCustomerSyncHoldRemoveQueueGuid($mysqli, $targetGuid);
    }
    if (hsCustomerCopyTestTableExists($mysqli)) {
        $stmt = $mysqli->prepare("DELETE FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` WHERE `target_guid`=? AND `status`='created'");
        if ($stmt) { $stmt->bind_param('s', $targetGuid); @$stmt->execute(); $stmt->close(); }
    }
}

function hsCustomerCopyTestPhotoNamesForGuid($mysqli, $guid)
{
    $names = array();
    $stmt = $mysqli->prepare("SELECT `obrazek_jmeno_GUID` FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID`=?");
    if (!$stmt) {
        return $names;
    }
    $stmt->bind_param('s', $guid);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($result && ($row = $result->fetch_assoc())) {
            $name = isset($row['obrazek_jmeno_GUID']) ? (string) $row['obrazek_jmeno_GUID'] : '';
            if ($name !== '' && basename($name) === $name) {
                $names[] = $name;
            }
        }
    }
    $stmt->close();
    return $names;
}

function hsCustomerCopyTestDeletePhotoFilesByNames($names)
{
    if (!defined('HS_CLIENT_UI_ROOT')) {
        return;
    }
    $galleryDir = HS_CLIENT_UI_ROOT . '/str/strana/galerie/';
    foreach ($names as $name) {
        if (!is_string($name) || $name === '' || basename($name) !== $name) {
            continue;
        }
        @unlink($galleryDir . $name);
        @unlink($galleryDir . 'm' . $name);
    }
}
