<?php
/**
 * HairSoft Klient V210 - ochrana dat zavislych na lokalnim HairSoft ID.
 *
 * Novy zakaznik vytvoreny na webu pouziva legacy docasne lidi_hs_id=11111111.
 * Timeline, fotografie a soubory proto nesmi byt nabidnuty PC pod timto docasnym ID.
 * Pouzivaji se HOLD stavy:
 *   timeline.timelineDoPCZnak = H
 *   klient_lidi_obrazky.obrazek_stazeno = 2
 *   soubory.stav = 2
 *
 * Bezny novy webovy zakaznik zachovava automatiku V202. TEST kopie mezi firmami je
 * vedena rucne: admin/manager zada skutecne HairSoft ID, fotky/soubory se uvolni
 * hromadne. Historicka Timeline V207 ceka mimo produkcni tabulku ve stagingu a
 * jednotlivy radek se do timeline vlozi az po explicitnim kliknuti uzivatele.
 * V210 navic drzi tvrdy manualni zamek: dokud uzivatel nepotvrdi, ze predchozi
 * pripraveny Timeline radek uz HairSoft zpracoval, dalsi radek nelze pripravit.
 */

if (!defined('HS_CUSTOMER_SYNC_HOLD_TABLE')) {
    define('HS_CUSTOMER_SYNC_HOLD_TABLE', 'k_klient_customer_sync_hold');
}
if (!defined('HS_CUSTOMER_TEMP_HS_ID')) {
    define('HS_CUSTOMER_TEMP_HS_ID', '11111111');
}
if (!defined('HS_CUSTOMER_TIMELINE_HOLD')) {
    define('HS_CUSTOMER_TIMELINE_HOLD', 'H');
}
if (!defined('HS_CUSTOMER_PHOTO_HOLD')) {
    define('HS_CUSTOMER_PHOTO_HOLD', 2);
}
if (!defined('HS_CUSTOMER_FILE_HOLD')) {
    define('HS_CUSTOMER_FILE_HOLD', 2);
}

function hsCustomerSyncHoldTableExists($mysqli)
{
    $result = @$mysqli->query("SHOW TABLES LIKE '" . HS_CUSTOMER_SYNC_HOLD_TABLE . "'");
    return $result && $result->num_rows > 0;
}


/**
 * V205: TEST kopie mezi firmami se dokoncuje rucne po zadani skutecneho HairSoft ID.
 * Tato kontrola je zamerne primo v helperu synchronizace, aby upload fotografie ani
 * bezny request nemohly omylem prepnout rucni kopii zpet do automaticke fronty V202.
 */
function hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '') {
        return false;
    }
    $table = 'k_klient_customer_copy_test_log';
    $exists = @$mysqli->query("SHOW TABLES LIKE '" . $table . "'");
    if (!$exists || $exists->num_rows === 0) {
        return false;
    }

    // V208: primarne stale funguje GUID, ale pokud audit obsahuje stabilni target_customer_id,
    // rucni rezim prezije i zmenu GUID po prvnim legacy syncu.
    $hasCustomerId = @$mysqli->query("SHOW COLUMNS FROM `" . $table . "` LIKE 'target_customer_id'");
    if ($hasCustomerId && $hasCustomerId->num_rows > 0) {
        $stmt = $mysqli->prepare("SELECT `lidi_id` FROM `klient_lidi` WHERE `lidi_guid`=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $guid);
            if ($stmt->execute()) {
                $r=$stmt->get_result(); $customer=$r?$r->fetch_assoc():null;
                $stmt->close();
                if ($customer && (int)$customer['lidi_id'] > 0) {
                    $customerId=(int)$customer['lidi_id'];
                    $stmt=$mysqli->prepare("SELECT 1 FROM `" . $table . "` WHERE (`target_customer_id`=? OR `target_guid`=?) AND `status` NOT IN ('removed','delete_requested') ORDER BY `id` DESC LIMIT 1");
                    if ($stmt) {
                        $stmt->bind_param('is',$customerId,$guid);
                        if($stmt->execute()){ $rr=$stmt->get_result(); $manual=$rr && $rr->num_rows>0; $stmt->close(); return $manual; }
                        $stmt->close();
                    }
                }
            } else {
                $stmt->close();
            }
        }
    }

    $stmt = $mysqli->prepare("SELECT 1 FROM `" . $table . "` WHERE `target_guid`=? AND `status` NOT IN ('removed','delete_requested') ORDER BY `id` DESC LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $manual = $result && $result->num_rows > 0;
    $stmt->close();
    return $manual;
}

function hsCustomerSyncHoldEnsureTable($mysqli)
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    if (hsCustomerSyncHoldTableExists($mysqli)) {
        $ready = true;
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` (
        `customer_guid` VARCHAR(96) NOT NULL,
        `branch_id` INT NOT NULL DEFAULT 0,
        `status` VARCHAR(16) NOT NULL DEFAULT 'waiting',
        `last_hs_id` VARCHAR(32) NOT NULL DEFAULT '',
        `last_error` VARCHAR(255) NOT NULL DEFAULT '',
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        `released_at` DATETIME NULL,
        PRIMARY KEY (`customer_guid`),
        KEY `idx_hs_sync_hold_status` (`status`,`updated_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    $ready = (@$mysqli->query($sql) !== false);
    return $ready;
}

function hsCustomerSyncHoldIsRealHairSoftId($value)
{
    $value = trim((string) $value);
    if ($value === '' || $value === '0' || $value === HS_CUSTOMER_TEMP_HS_ID) {
        return false;
    }
    if (!preg_match('/^[0-9]+$/', $value)) {
        return false;
    }
    return ((int) $value) > 0;
}

function hsCustomerSyncHoldCustomerState($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '') {
        return null;
    }

    $stmt = $mysqli->prepare("SELECT `lidi_hs_id`,`lidi_sw_id`,`lidi_aktivni` FROM `klient_lidi` WHERE `lidi_guid`=? LIMIT 1");
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

function hsCustomerSyncHoldShouldWait($mysqli, $guid)
{
    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    if (!$state) {
        return false;
    }
    return !hsCustomerSyncHoldIsRealHairSoftId(isset($state['lidi_hs_id']) ? $state['lidi_hs_id'] : '');
}

function hsCustomerSyncHoldQueueGuid($mysqli, $guid, $branchId = 0, $forceStatus = '')
{
    $guid = trim((string) $guid);
    if ($guid === '' || !hsCustomerSyncHoldEnsureTable($mysqli)) {
        return false;
    }

    $branchId = (int) $branchId;
    if ($branchId <= 0) {
        $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
        if ($state && isset($state['lidi_sw_id'])) {
            $branchId = (int) $state['lidi_sw_id'];
        }
    }

    $forceStatus = trim((string) $forceStatus);
    $queueStatus = in_array($forceStatus, array('manual', 'waiting'), true) ? $forceStatus : (hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid) ? 'manual' : 'waiting');
    $sql = "INSERT INTO `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "`
            (`customer_guid`,`branch_id`,`status`,`last_hs_id`,`last_error`,`created_at`,`updated_at`,`released_at`)
            VALUES (?,?,?,'','',NOW(),NOW(),NULL)
            ON DUPLICATE KEY UPDATE
              `branch_id`=VALUES(`branch_id`),
              `status`=VALUES(`status`),
              `last_error`='',
              `updated_at`=NOW(),
              `released_at`=NULL";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('sis', $guid, $branchId, $queueStatus);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool) $ok;
}

/**
 * V206: stav fronty je autoritativni pro to, zda administrator rucni dokonceni
 * skutecne potvrdil. Samotna existence realneho lidi_hs_id neni potvrzeni -
 * HairSoft jej muze pri bezne synchronizaci zapsat zpet sam.
 */
function hsCustomerSyncHoldQueueStatus($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '' || !hsCustomerSyncHoldTableExists($mysqli)) {
        return '';
    }
    $stmt = $mysqli->prepare("SELECT `status` FROM `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` WHERE `customer_guid`=? LIMIT 1");
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param('s', $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return '';
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row && isset($row['status']) ? (string) $row['status'] : '';
}

function hsCustomerSyncHoldManualConfirmed($mysqli, $guid)
{
    return hsCustomerSyncHoldQueueStatus($mysqli, $guid) === 'manual_ready';
}

/**
 * Ochrana zaznamu vytvorenych pred V202, ktere jsou jeste u weboveho zakaznika
 * s docasnym ID. Volat jen pro konkretni GUID po overeni, ze stale nema realne HS ID.
 */
function hsCustomerSyncHoldQueueIsWaiting($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '' || !hsCustomerSyncHoldTableExists($mysqli)) {
        return false;
    }
    $stmt = $mysqli->prepare("SELECT 1 FROM `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` WHERE `customer_guid`=? AND `status`='waiting' LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $result = $stmt->get_result();
    $waiting = $result && $result->num_rows > 0;
    $stmt->close();
    return $waiting;
}

function hsCustomerSyncHoldRemoveQueueGuid($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '' || !hsCustomerSyncHoldTableExists($mysqli)) {
        return true;
    }
    $stmt = $mysqli->prepare("DELETE FROM `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` WHERE `customer_guid`=?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $guid);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool) $ok;
}

function hsCustomerSyncHoldStageLegacyPendingForGuid($mysqli, $guid)
{
    $guid = trim((string) $guid);
    if ($guid === '') {
        return false;
    }

    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    if (!$state || hsCustomerSyncHoldIsRealHairSoftId(isset($state['lidi_hs_id']) ? $state['lidi_hs_id'] : '')) {
        return true;
    }
    if (!hsCustomerSyncHoldQueueGuid($mysqli, $guid, isset($state['lidi_sw_id']) ? (int) $state['lidi_sw_id'] : 0)) {
        return false;
    }

    // Jen zaznamy, ktere PC jeste nikdy neprijalo. IDHS NULL chrani existujici historii.
    $stmt = $mysqli->prepare("UPDATE `timeline` SET `timelineDoPCZnak`=? WHERE `timelineOsobaGuid`=? AND `timelineIDHS` IS NULL AND (`timelineDoPCZnak`='N' OR `timelineDoPCZnak`='U')");
    if (!$stmt) {
        return false;
    }
    $hold = HS_CUSTOMER_TIMELINE_HOLD;
    $stmt->bind_param('ss', $hold, $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    $photoHold = (int) HS_CUSTOMER_PHOTO_HOLD;
    $stmt = $mysqli->prepare("UPDATE `klient_lidi_obrazky` SET `obrazek_stazeno`=? WHERE `obrazek_osoba_GUID`=? AND `obrazek_stazeno`=0");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('is', $photoHold, $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    $fileHold = (int) HS_CUSTOMER_FILE_HOLD;
    $stmt = $mysqli->prepare("UPDATE `soubory` SET `stav`=? WHERE `guid_cloveka`=? AND `stav`=0");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('is', $fileHold, $guid);
    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    return true;
}

function hsCustomerSyncHoldTimelineStampColumnExists($mysqli)
{
    $result = @$mysqli->query("SHOW COLUMNS FROM `timeline` LIKE 'timelineDoPCVlozeno'");
    return ($result && $result->num_rows > 0);
}

function hsCustomerSyncHoldReleaseGuid($mysqli, $guid, $knownHsId = '')
{
    $guid = trim((string) $guid);
    if ($guid === '') {
        return false;
    }

    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    if (!$state) {
        return false;
    }
    $hsId = $knownHsId !== '' ? (string) $knownHsId : (isset($state['lidi_hs_id']) ? (string) $state['lidi_hs_id'] : '');
    if (!hsCustomerSyncHoldIsRealHairSoftId($hsId)) {
        return false;
    }

    $transactionStarted = false;
    try {
        if (!$mysqli->begin_transaction()) {
            throw new RuntimeException('Nepodařilo se spustit uvolnění návazných dat zákazníka.');
        }
        $transactionStarted = true;

        $timelineHold = HS_CUSTOMER_TIMELINE_HOLD;
        if (hsCustomerSyncHoldTimelineStampColumnExists($mysqli)) {
            $stmt = $mysqli->prepare("UPDATE `timeline` SET `timelineDoPCZnak`='N',`timelineDoPCSynchro`=NULL,`timelineIDHS`=NULL,`timelineDoPCVlozeno`=CURRENT_TIMESTAMP WHERE `timelineOsobaGuid`=? AND `timelineDoPCZnak`=? AND `timelineValid`=1");
        } else {
            $stmt = $mysqli->prepare("UPDATE `timeline` SET `timelineDoPCZnak`='N',`timelineDoPCSynchro`=NULL,`timelineIDHS`=NULL WHERE `timelineOsobaGuid`=? AND `timelineDoPCZnak`=? AND `timelineValid`=1");
        }
        if (!$stmt) {
            throw new RuntimeException('Nepodařilo se připravit Timeline k uvolnění.');
        }
        $stmt->bind_param('ss', $guid, $timelineHold);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Timeline se nepodařilo uvolnit pro HairSoft.');
        }
        $stmt->close();

        $photoHold = (int) HS_CUSTOMER_PHOTO_HOLD;
        $stmt = $mysqli->prepare("UPDATE `klient_lidi_obrazky` SET `obrazek_stazeno`=0 WHERE `obrazek_osoba_GUID`=? AND `obrazek_stazeno`=? AND `obrazek_smazan`=0");
        if (!$stmt) {
            throw new RuntimeException('Nepodařilo se připravit fotografie k uvolnění.');
        }
        $stmt->bind_param('si', $guid, $photoHold);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Fotografie se nepodařilo uvolnit pro HairSoft.');
        }
        $stmt->close();

        $fileHold = (int) HS_CUSTOMER_FILE_HOLD;
        $stmt = $mysqli->prepare("UPDATE `soubory` SET `stav`=0 WHERE `guid_cloveka`=? AND `stav`=?");
        if (!$stmt) {
            throw new RuntimeException('Nepodařilo se připravit soubory k uvolnění.');
        }
        $stmt->bind_param('si', $guid, $fileHold);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Soubory se nepodařilo uvolnit pro HairSoft.');
        }
        $stmt->close();

        if (hsCustomerSyncHoldTableExists($mysqli)) {
            $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` SET `status`='released',`last_hs_id`=?,`last_error`='',`updated_at`=NOW(),`released_at`=NOW() WHERE `customer_guid`=?");
            if ($stmt) {
                $stmt->bind_param('ss', $hsId, $guid);
                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new RuntimeException('Fronta synchronizace se nepodařila potvrdit.');
                }
                $stmt->close();
            }
        }

        if (!$mysqli->commit()) {
            throw new RuntimeException('Uvolnění návazných dat se nepodařilo potvrdit.');
        }
        $transactionStarted = false;
        return true;
    } catch (Throwable $error) {
        if ($transactionStarted) {
            @$mysqli->rollback();
        }
        if (hsCustomerSyncHoldTableExists($mysqli)) {
            $message = substr((string) $error->getMessage(), 0, 250);
            $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` SET `last_error`=?,`updated_at`=NOW() WHERE `customer_guid`=?");
            if ($stmt) {
                $stmt->bind_param('ss', $message, $guid);
                @$stmt->execute();
                $stmt->close();
            }
        }
        return false;
    }
}


/**
 * V205: po rucnim zadani lokalniho HairSoft ID u TEST kopie uvolni pouze
 * fotografie a soubory. Timeline zustava v HOLD a administrator ji uvolnuje
 * po jednom radku tlacitkem v tabulce.
 */
function hsCustomerSyncHoldManualCopyLogExists($mysqli, $copyLogId)
{
    $copyLogId = (int) $copyLogId;
    if ($copyLogId <= 0) { return false; }
    $table = 'k_klient_customer_copy_test_log';
    $exists = @$mysqli->query("SHOW TABLES LIKE '" . $table . "'");
    if (!$exists || $exists->num_rows === 0) { return false; }
    $stmt = $mysqli->prepare("SELECT 1 FROM `" . $table . "` WHERE `id`=? AND `status` NOT IN ('removed','delete_requested') LIMIT 1");
    if (!$stmt) { return false; }
    $stmt->bind_param('i', $copyLogId);
    if (!$stmt->execute()) { $stmt->close(); return false; }
    $r = $stmt->get_result();
    $ok = $r && $r->num_rows > 0;
    $stmt->close();
    return $ok;
}

function hsCustomerSyncHoldManualAssignId($mysqli, $guid, $hsId, $copyLogId = 0)
{
    $guid = trim((string) $guid);
    $hsId = trim((string) $hsId);
    $copyLogId = (int) $copyLogId;
    $manualTarget = $copyLogId > 0 ? hsCustomerSyncHoldManualCopyLogExists($mysqli, $copyLogId) : hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid);
    if ($guid === '' || !$manualTarget || !hsCustomerSyncHoldIsRealHairSoftId($hsId)) {
        return false;
    }

    // DDL nesmi bezet uvnitr transakce. Frontu proto pripravime jeste pred begin_transaction().
    if (!hsCustomerSyncHoldEnsureTable($mysqli)) {
        throw new RuntimeException('Tabulku ruční synchronizace se nepodařilo připravit.');
    }

    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    if (!$state || (int) $state['lidi_aktivni'] !== 1) {
        return false;
    }
    $currentHsId = isset($state['lidi_hs_id']) ? trim((string) $state['lidi_hs_id']) : '';
    if (hsCustomerSyncHoldIsRealHairSoftId($currentHsId) && $currentHsId !== $hsId) {
        throw new RuntimeException('Zákazník už má přiřazené HairSoft ID ' . $currentHsId . '. Z bezpečnostních důvodů ho nelze tímto tlačítkem přepsat.');
    }

    $transactionStarted = false;
    try {
        if (!$mysqli->begin_transaction()) {
            throw new RuntimeException('Nepodařilo se spustit ruční přiřazení HairSoft ID.');
        }
        $transactionStarted = true;

        $branchId = isset($state['lidi_sw_id']) ? (int) $state['lidi_sw_id'] : 0;
        $stmt = $mysqli->prepare("SELECT `lidi_guid` FROM `klient_lidi` WHERE `lidi_hs_id`=? AND `lidi_sw_id`=? AND `lidi_guid`<>? AND `lidi_aktivni`=1 LIMIT 1");
        if (!$stmt) {
            throw new RuntimeException('Nepodařilo se ověřit HairSoft ID zákazníka.');
        }
        $stmt->bind_param('sis', $hsId, $branchId, $guid);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Nepodařilo se ověřit HairSoft ID zákazníka.');
        }
        $dupResult = $stmt->get_result();
        $duplicate = $dupResult && $dupResult->num_rows > 0;
        $stmt->close();
        if ($duplicate) {
            throw new RuntimeException('Toto HairSoft ID už v cílové pobočce používá jiný zákazník.');
        }

        // HairSoft už zákazníka založil. Zrušíme původní příznak N, aby se nemohl založit podruhé.
        $stmt = $mysqli->prepare("UPDATE `klient_lidi` SET `lidi_hs_id`=?,`lidi_web_pc`='' WHERE `lidi_guid`=? AND `lidi_aktivni`=1");
        if (!$stmt) {
            throw new RuntimeException('HairSoft ID se nepodařilo uložit.');
        }
        $stmt->bind_param('ss', $hsId, $guid);
        if (!$stmt->execute() || $stmt->affected_rows < 0) {
            $stmt->close();
            throw new RuntimeException('HairSoft ID se nepodařilo uložit.');
        }
        $stmt->close();

        $photoHold = (int) HS_CUSTOMER_PHOTO_HOLD;
        $stmt = $mysqli->prepare("UPDATE `klient_lidi_obrazky` SET `obrazek_stazeno`=0 WHERE `obrazek_osoba_GUID`=? AND `obrazek_stazeno`=? AND `obrazek_smazan`=0");
        if (!$stmt) {
            throw new RuntimeException('Fotografie se nepodařilo připravit pro HairSoft.');
        }
        $stmt->bind_param('si', $guid, $photoHold);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Fotografie se nepodařilo připravit pro HairSoft.');
        }
        $stmt->close();

        $fileHold = (int) HS_CUSTOMER_FILE_HOLD;
        $stmt = $mysqli->prepare("UPDATE `soubory` SET `stav`=0 WHERE `guid_cloveka`=? AND `stav`=?");
        if (!$stmt) {
            throw new RuntimeException('Soubory se nepodařilo připravit pro HairSoft.');
        }
        $stmt->bind_param('si', $guid, $fileHold);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Soubory se nepodařilo připravit pro HairSoft.');
        }
        $stmt->close();

        $stmt = $mysqli->prepare("INSERT INTO `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` (`customer_guid`,`branch_id`,`status`,`last_hs_id`,`last_error`,`created_at`,`updated_at`,`released_at`) VALUES (?,?,'manual_ready',?,'',NOW(),NOW(),NULL) ON DUPLICATE KEY UPDATE `branch_id`=VALUES(`branch_id`),`status`='manual_ready',`last_hs_id`=VALUES(`last_hs_id`),`last_error`='',`updated_at`=NOW(),`released_at`=NULL");
        if (!$stmt) {
            throw new RuntimeException('Ruční stav synchronizace se nepodařilo uložit.');
        }
        $stmt->bind_param('sis', $guid, $branchId, $hsId);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Ruční stav synchronizace se nepodařilo uložit.');
        }
        $stmt->close();

        if (!$mysqli->commit()) {
            throw new RuntimeException('Ruční přiřazení HairSoft ID se nepodařilo potvrdit.');
        }
        $transactionStarted = false;
        return true;
    } catch (Throwable $error) {
        if ($transactionStarted) {
            @$mysqli->rollback();
        }
        throw $error;
    }
}

function hsCustomerSyncHoldManualConfirmPreparedTimeline($mysqli, $guid, $copyLogId = 0)
{
    $guid = trim((string) $guid);
    $copyLogId = (int) $copyLogId;
    $manualTarget = $copyLogId > 0 ? hsCustomerSyncHoldManualCopyLogExists($mysqli, $copyLogId) : hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid);
    if ($guid === '' || !$manualTarget || !hsCustomerSyncHoldManualConfirmed($mysqli, $guid)) {
        return 0;
    }

    $stageTable = 'k_klient_customer_copy_timeline_stage';
    $stageExists = @$mysqli->query("SHOW TABLES LIKE '" . $stageTable . "'");
    if (!$stageExists || $stageExists->num_rows === 0) {
        return 0;
    }

    $transactionStarted = false;
    try {
        if (!$mysqli->begin_transaction()) {
            throw new RuntimeException('Nepodařilo se spustit potvrzení Timeline.');
        }
        $transactionStarted = true;

        if ($copyLogId > 0) {
            $stmt = $mysqli->prepare("SELECT `id`,`target_timeline_id` FROM `" . $stageTable . "` WHERE `copy_log_id`=? AND `status`='prepared' ORDER BY `id` ASC FOR UPDATE");
        } else {
            $stmt = $mysqli->prepare("SELECT `id`,`target_timeline_id` FROM `" . $stageTable . "` WHERE `target_guid`=? AND `status`='prepared' ORDER BY `id` ASC FOR UPDATE");
        }
        if (!$stmt) {
            throw new RuntimeException('Připravenou Timeline se nepodařilo načíst.');
        }
        if ($copyLogId > 0) { $stmt->bind_param('i', $copyLogId); }
        else { $stmt->bind_param('s', $guid); }
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Připravenou Timeline se nepodařilo načíst.');
        }
        $result = $stmt->get_result();
        $preparedIds = array();
        while ($result && ($row = $result->fetch_assoc())) {
            $preparedIds[] = (int) $row['id'];
        }
        $stmt->close();

        if (!$preparedIds) {
            $mysqli->rollback();
            $transactionStarted = false;
            return 0;
        }

        // HairSoft neposila pouzitelne ACK. Toto je proto vedome rucni potvrzeni
        // administratora, ze pripraveny zaznam uz v HairSoft opravdu zpracoval.
        // Soucasne uzavreme PRESNE transportni timeline radky vytvorene stagingem:
        // prazdny DoPC znak + technicky cas synchronizace zabrani tomu, aby se pri
        // dalsim cyklu znovu nabizely jako nova zmena. Historicky text i datum zustavaji.
        foreach ($preparedIds as $stageId) {
            $transportId = 0;
            $findStmt = $mysqli->prepare("SELECT `target_timeline_id` FROM `" . $stageTable . "` WHERE `id`=? AND `status`='prepared' LIMIT 1");
            if (!$findStmt) { throw new RuntimeException('Transportní Timeline se nepodařilo ověřit.'); }
            $findStmt->bind_param('i', $stageId);
            if (!$findStmt->execute()) { $findStmt->close(); throw new RuntimeException('Transportní Timeline se nepodařilo ověřit.'); }
            $findResult=$findStmt->get_result(); $findRow=$findResult?$findResult->fetch_assoc():null;
            $transportId=$findRow && isset($findRow['target_timeline_id']) ? (int)$findRow['target_timeline_id'] : 0;
            $findStmt->close();
            if ($transportId > 0) {
                $doneStmt = $mysqli->prepare("UPDATE `timeline` SET `timelineDoPCZnak`='',`timelineDoPCSynchro`=COALESCE(`timelineDoPCSynchro`,NOW()) WHERE `timelineID`=? AND `timelineOsobaGuid`=? AND `timelineValid`=1");
                if (!$doneStmt) { throw new RuntimeException('Transportní Timeline se nepodařilo uzavřít.'); }
                $doneStmt->bind_param('is', $transportId, $guid);
                if (!$doneStmt->execute()) { $doneStmt->close(); throw new RuntimeException('Transportní Timeline se nepodařilo uzavřít.'); }
                $doneStmt->close();
            }
        }

        if ($copyLogId > 0) {
            $stmt = $mysqli->prepare("UPDATE `" . $stageTable . "` SET `status`='sent' WHERE `copy_log_id`=? AND `status`='prepared'");
        } else {
            $stmt = $mysqli->prepare("UPDATE `" . $stageTable . "` SET `status`='sent' WHERE `target_guid`=? AND `status`='prepared'");
        }
        if (!$stmt) {
            throw new RuntimeException('Potvrzení Timeline se nepodařilo uložit.');
        }
        if ($copyLogId > 0) { $stmt->bind_param('i', $copyLogId); }
        else { $stmt->bind_param('s', $guid); }
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Potvrzení Timeline se nepodařilo uložit.');
        }
        $confirmed = max(0, (int) $stmt->affected_rows);
        $stmt->close();

        if (!$mysqli->commit()) {
            throw new RuntimeException('Potvrzení Timeline se nepodařilo dokončit.');
        }
        $transactionStarted = false;
        return $confirmed;
    } catch (Throwable $error) {
        if ($transactionStarted) {
            @$mysqli->rollback();
        }
        throw $error;
    }
}

function hsCustomerSyncHoldManualReleaseTimelineRow($mysqli, $guid, $timelineId, $copyLogId = 0)
{
    $guid = trim((string) $guid);
    $timelineId = (int) $timelineId;
    $copyLogId = (int) $copyLogId;
    $manualTarget = $copyLogId > 0 ? hsCustomerSyncHoldManualCopyLogExists($mysqli, $copyLogId) : hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid);
    if ($guid === '' || $timelineId <= 0 || !$manualTarget || !hsCustomerSyncHoldManualConfirmed($mysqli, $guid)) {
        return false;
    }
    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    if (!$state || !hsCustomerSyncHoldIsRealHairSoftId(isset($state['lidi_hs_id']) ? $state['lidi_hs_id'] : '')) {
        return false;
    }

    // V207: nejdriv hledej snapshot v izolovanem stagingu. Teprve klik uzivatele
    // vytvori JEDEN skutecny radek v produkcni timeline s normalnim priznakem N.
    $stageTable = 'k_klient_customer_copy_timeline_stage';
    $stageExists = @$mysqli->query("SHOW TABLES LIKE '" . $stageTable . "'");
    if ($stageExists && $stageExists->num_rows > 0) {
        $transactionStarted = false;
        try {
            if (!$mysqli->begin_transaction()) {
                throw new RuntimeException('Nepodařilo se spustit přípravu Timeline.');
            }
            $transactionStarted = true;

            // V210: zamek cele staging fronty dane kopie. Pokud uz je jeden radek
            // pripraveny pro HairSoft, zadny dalsi se nesmi vytvorit, dokud uzivatel
            // rucne nepotvrdi probehlou synchronizaci.
            if ($copyLogId > 0) {
                $lockStmt = $mysqli->prepare("SELECT `id`,`status` FROM `" . $stageTable . "` WHERE `copy_log_id`=? AND `status` IN ('waiting','prepared') ORDER BY `id` ASC FOR UPDATE");
                if (!$lockStmt) { throw new RuntimeException('Timeline frontu se nepodařilo uzamknout.'); }
                $lockStmt->bind_param('i', $copyLogId);
            } else {
                $lockStmt = $mysqli->prepare("SELECT `id`,`status` FROM `" . $stageTable . "` WHERE `target_guid`=? AND `status` IN ('waiting','prepared') ORDER BY `id` ASC FOR UPDATE");
                if (!$lockStmt) { throw new RuntimeException('Timeline frontu se nepodařilo uzamknout.'); }
                $lockStmt->bind_param('s', $guid);
            }
            if (!$lockStmt->execute()) { $lockStmt->close(); throw new RuntimeException('Timeline frontu se nepodařilo uzamknout.'); }
            $lockResult = $lockStmt->get_result();
            $hasPrepared = false;
            while ($lockResult && ($lockRow = $lockResult->fetch_assoc())) {
                if ((string) $lockRow['status'] === 'prepared') { $hasPrepared = true; break; }
            }
            $lockStmt->close();
            if ($hasPrepared) {
                throw new RuntimeException('Nejdříve potvrďte dokončení předchozí synchronizace Timeline v HairSoft.');
            }

            if ($copyLogId > 0) {
                $stmt = $mysqli->prepare("SELECT `id`,`target_guid`,`target_branch_id`,`timeline_datum_cas`,`timeline_text`,`timeline_obsluha`,`status` FROM `" . $stageTable . "` WHERE `id`=? AND `copy_log_id`=? AND `status`='waiting' LIMIT 1 FOR UPDATE");
            } else {
                $stmt = $mysqli->prepare("SELECT `id`,`target_guid`,`target_branch_id`,`timeline_datum_cas`,`timeline_text`,`timeline_obsluha`,`status` FROM `" . $stageTable . "` WHERE `id`=? AND `target_guid`=? AND `status`='waiting' LIMIT 1 FOR UPDATE");
            }
            if (!$stmt) {
                throw new RuntimeException('Čekající Timeline se nepodařilo načíst.');
            }
            if ($copyLogId > 0) { $stmt->bind_param('ii', $timelineId, $copyLogId); }
            else { $stmt->bind_param('is', $timelineId, $guid); }
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('Čekající Timeline se nepodařilo načíst.');
            }
            $result = $stmt->get_result();
            $stage = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($stage) {
                $branchId = (int) $stage['target_branch_id'];
                $timelineDate = $stage['timeline_datum_cas'];
                $timelineText = (string) $stage['timeline_text'];
                $timelineStaff = (string) $stage['timeline_obsluha'];

                if (hsCustomerSyncHoldTimelineStampColumnExists($mysqli)) {
                    $stmt = $mysqli->prepare("INSERT INTO `timeline` (`timelineOsobaGuid`,`timelineDatumCas`,`timelineText`,`timelineObsluha`,`timelineObsluhaID`,`timelineDoPCZnak`,`timelineDoPCSynchro`,`timelineDoPCVlozeno`,`timelineIDHS`,`timelineSwID`,`timelineValid`) VALUES (?,?,?,?,1,'N',NULL,CURRENT_TIMESTAMP,NULL,?,1)");
                } else {
                    $stmt = $mysqli->prepare("INSERT INTO `timeline` (`timelineOsobaGuid`,`timelineDatumCas`,`timelineText`,`timelineObsluha`,`timelineObsluhaID`,`timelineDoPCZnak`,`timelineDoPCSynchro`,`timelineIDHS`,`timelineSwID`,`timelineValid`) VALUES (?,?,?,?,1,'N',NULL,NULL,?,1)");
                }
                if (!$stmt) {
                    throw new RuntimeException('Timeline se nepodařilo připravit pro HairSoft.');
                }
                $stmt->bind_param('ssssi', $guid, $timelineDate, $timelineText, $timelineStaff, $branchId);
                if (!$stmt->execute()) {
                    $message = $stmt->error;
                    $stmt->close();
                    throw new RuntimeException('Timeline se nepodařilo připravit pro HairSoft: ' . $message);
                }
                $targetTimelineId = (int) $stmt->insert_id;
                $stmt->close();

                if ($copyLogId > 0) {
                    $stmt = $mysqli->prepare("UPDATE `" . $stageTable . "` SET `status`='prepared',`target_timeline_id`=?,`prepared_at`=NOW() WHERE `id`=? AND `copy_log_id`=? AND `status`='waiting'");
                } else {
                    $stmt = $mysqli->prepare("UPDATE `" . $stageTable . "` SET `status`='prepared',`target_timeline_id`=?,`prepared_at`=NOW() WHERE `id`=? AND `target_guid`=? AND `status`='waiting'");
                }
                if (!$stmt) {
                    throw new RuntimeException('Stav Timeline se nepodařilo uložit.');
                }
                if ($copyLogId > 0) { $stmt->bind_param('iii', $targetTimelineId, $timelineId, $copyLogId); }
                else { $stmt->bind_param('iis', $targetTimelineId, $timelineId, $guid); }
                if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                    $stmt->close();
                    throw new RuntimeException('Stav Timeline se mezitím změnil. Obnovte stránku.');
                }
                $stmt->close();

                if (!$mysqli->commit()) {
                    throw new RuntimeException('Přípravu Timeline se nepodařilo potvrdit.');
                }
                $transactionStarted = false;
                return true;
            }

            // Neni-li to staging ID, transakci ukoncime a zkusime jen zpetnou kompatibilitu
            // pro ciste V205/V206 HOLD radky.
            $mysqli->rollback();
            $transactionStarted = false;
        } catch (Throwable $error) {
            if ($transactionStarted) {
                @$mysqli->rollback();
            }
            throw $error;
        }
    }

    // Zpetna kompatibilita: stary HOLD radek z V205/V206 lze uvolnit po jednom.
    $hold = HS_CUSTOMER_TIMELINE_HOLD;
    if (hsCustomerSyncHoldTimelineStampColumnExists($mysqli)) {
        $stmt = $mysqli->prepare("UPDATE `timeline` SET `timelineDoPCZnak`='N',`timelineDoPCSynchro`=NULL,`timelineIDHS`=NULL,`timelineDoPCVlozeno`=CURRENT_TIMESTAMP WHERE `timelineID`=? AND `timelineOsobaGuid`=? AND `timelineDoPCZnak`=? AND `timelineIDHS` IS NULL AND `timelineValid`=1");
    } else {
        $stmt = $mysqli->prepare("UPDATE `timeline` SET `timelineDoPCZnak`='N',`timelineDoPCSynchro`=NULL,`timelineIDHS`=NULL WHERE `timelineID`=? AND `timelineOsobaGuid`=? AND `timelineDoPCZnak`=? AND `timelineIDHS` IS NULL AND `timelineValid`=1");
    }
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('iss', $timelineId, $guid, $hold);
    $ok = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    return (bool) $ok;
}

function hsCustomerSyncHoldManualState($mysqli, $guid, $copyLogId = 0)
{
    $guid = trim((string) $guid);
    $copyLogId = (int) $copyLogId;
    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    $manualTarget = $copyLogId > 0 ? hsCustomerSyncHoldManualCopyLogExists($mysqli, $copyLogId) : hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid);
    if (!$state || !$manualTarget) {
        return null;
    }
    $queueStatus = hsCustomerSyncHoldQueueStatus($mysqli, $guid);
    $out = array(
        'hs_id' => isset($state['lidi_hs_id']) ? (string) $state['lidi_hs_id'] : '',
        'paired' => hsCustomerSyncHoldIsRealHairSoftId(isset($state['lidi_hs_id']) ? $state['lidi_hs_id'] : ''),
        'queue_status' => $queueStatus,
        'manual_confirmed' => ($queueStatus === 'manual_ready'),
        'timeline_hold' => 0,
        'timeline_prepared' => 0,
        'timeline_sent' => 0,
        'photo_hold' => 0,
        'file_hold' => 0
    );
    // V207: nove kopie drzi historickou Timeline mimo produkcni tabulku.
    $stageTable = 'k_klient_customer_copy_timeline_stage';
    $stageTotal = 0;
    $stageExists = @$mysqli->query("SHOW TABLES LIKE '" . $stageTable . "'");
    if ($stageExists && $stageExists->num_rows > 0) {
        if ($copyLogId > 0) {
            $stmt = $mysqli->prepare("SELECT SUM(CASE WHEN `status`='waiting' THEN 1 ELSE 0 END) AS `hold_count`, SUM(CASE WHEN `status`='prepared' THEN 1 ELSE 0 END) AS `prepared_count`, SUM(CASE WHEN `status`='sent' THEN 1 ELSE 0 END) AS `sent_count` FROM `" . $stageTable . "` WHERE `copy_log_id`=?");
        } else {
            $stmt = $mysqli->prepare("SELECT SUM(CASE WHEN `status`='waiting' THEN 1 ELSE 0 END) AS `hold_count`, SUM(CASE WHEN `status`='prepared' THEN 1 ELSE 0 END) AS `prepared_count`, SUM(CASE WHEN `status`='sent' THEN 1 ELSE 0 END) AS `sent_count` FROM `" . $stageTable . "` WHERE `target_guid`=?");
        }
        if ($stmt) {
            if ($copyLogId > 0) { $stmt->bind_param('i', $copyLogId); }
            else { $stmt->bind_param('s', $guid); }
            if ($stmt->execute()) {
                $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null;
                if ($row) {
                    $out['timeline_hold']=(int)$row['hold_count'];
                    $out['timeline_prepared']=(int)$row['prepared_count'];
                    $out['timeline_sent']=(int)$row['sent_count'];
                    $stageTotal = $out['timeline_hold'] + $out['timeline_prepared'] + $out['timeline_sent'];
                }
            }
            $stmt->close();
        }
    }

    // Zpetna kompatibilita pouze pro stare V205/V206 kopie, ktere jeste nemaji
    // V207+ staging. U staging kopie by produkcni transportni radek N jinak po
    // rucnim potvrzeni znovu zablokoval dalsi polozku, i kdyz staging uz je sent.
    if ($stageTotal === 0) {
        $hold = HS_CUSTOMER_TIMELINE_HOLD;
        $stmt = $mysqli->prepare("SELECT SUM(CASE WHEN `timelineDoPCZnak`=? THEN 1 ELSE 0 END) AS `hold_count`, SUM(CASE WHEN `timelineDoPCZnak`='N' AND `timelineIDHS` IS NULL THEN 1 ELSE 0 END) AS `prepared_count` FROM `timeline` WHERE `timelineOsobaGuid`=? AND `timelineValid`=1");
        if ($stmt) {
            $stmt->bind_param('ss', $hold, $guid);
            if ($stmt->execute()) {
                $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null;
                if ($row) { $out['timeline_hold'] += (int)$row['hold_count']; if ($out['timeline_prepared'] === 0) { $out['timeline_prepared'] = (int)$row['prepared_count']; } }
            }
            $stmt->close();
        }
    }
    $photoHold=(int)HS_CUSTOMER_PHOTO_HOLD;
    $stmt=$mysqli->prepare("SELECT COUNT(*) AS `c` FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID`=? AND `obrazek_stazeno`=? AND `obrazek_smazan`=0");
    if ($stmt) { $stmt->bind_param('si',$guid,$photoHold); if($stmt->execute()){ $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; if($row)$out['photo_hold']=(int)$row['c']; } $stmt->close(); }
    $fileHold=(int)HS_CUSTOMER_FILE_HOLD;
    $stmt=$mysqli->prepare("SELECT COUNT(*) AS `c` FROM `soubory` WHERE `guid_cloveka`=? AND `stav`=?");
    if ($stmt) { $stmt->bind_param('si',$guid,$fileHold); if($stmt->execute()){ $r=$stmt->get_result(); $row=$r?$r->fetch_assoc():null; if($row)$out['file_hold']=(int)$row['c']; } $stmt->close(); }
    return $out;
}

/**
 * Synchronizuje stav jednoho zakaznika pri otevreni jeho karty.
 * - bezny webovy zakaznik: zachovava automatiku V202,
 * - TEST kopie mezi firmami: pouze drzi HOLD; uvolneni je od V205 vyhradne rucni.
 */
function hsCustomerSyncHoldSynchronizeGuid($mysqli, $guid)
{
    $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
    if (!$state) {
        return false;
    }
    $hsId = isset($state['lidi_hs_id']) ? (string) $state['lidi_hs_id'] : '';

    // V205: TEST kopie mezi firmami se nikdy neuvolnuje heartbeat/automatikou.
    // Dokud nema realne ID, pouze drzi H/2/2. Po rucnim zadani ID se fotky/soubory
    // uvolni explicitni akci a Timeline pouze po jednotlivych radcich.
    if (hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid)) {
        // V206: samotny navrat realneho ID z HairSoft NESMI byt povazovan za
        // souhlas uzivatele s uvolnenim navaznych dat.
        if (!hsCustomerSyncHoldManualConfirmed($mysqli, $guid)) {
            $branchId = isset($state['lidi_sw_id']) ? (int) $state['lidi_sw_id'] : 0;
            hsCustomerSyncHoldQueueGuid($mysqli, $guid, $branchId, 'manual');
        }
        if (hsCustomerSyncHoldIsRealHairSoftId($hsId)) {
            return true;
        }
        return hsCustomerSyncHoldStageLegacyPendingForGuid($mysqli, $guid);
    }

    if (hsCustomerSyncHoldIsRealHairSoftId($hsId)) {
        // Bez cekajiciho zaznamu neni na bezne karte zakaznika co uvolnovat.
        if (!hsCustomerSyncHoldQueueIsWaiting($mysqli, $guid)) {
            return true;
        }
        return hsCustomerSyncHoldReleaseGuid($mysqli, $guid, $hsId);
    }
    return hsCustomerSyncHoldStageLegacyPendingForGuid($mysqli, $guid);
}

/**
 * Lehke automaticke zpracovani fronty pri beznem pozadavku HS Klient.
 * Neprohledava velke tabulky timeline/fotek/souboru; cte jen izolovanou frontu.
 */
function hsCustomerSyncHoldProcessReady($mysqli, $limit = 50, $ownerId = 0)
{
    if (!hsCustomerSyncHoldTableExists($mysqli)) {
        return 0;
    }
    $limit = max(1, min(200, (int) $limit));
    $ownerId = (int) $ownerId;
    if ($ownerId <= 0) {
        return 0;
    }
    $ownerJoin = " JOIN `sw_email_pobocka` `e` ON `e`.`sw_id`=`q`.`branch_id` ";
    $ownerWhere = " AND `e`.`k_id`=" . $ownerId . " ";
    $sql = "SELECT DISTINCT `q`.`customer_guid`,`k`.`lidi_hs_id`,`q`.`updated_at`
            FROM `" . HS_CUSTOMER_SYNC_HOLD_TABLE . "` `q`
            JOIN `klient_lidi` `k` ON `k`.`lidi_guid`=`q`.`customer_guid`" . $ownerJoin . "
            WHERE `q`.`status`='waiting' AND `k`.`lidi_aktivni`=1" . $ownerWhere . "
            ORDER BY `q`.`updated_at` ASC
            LIMIT " . $limit;
    $result = @$mysqli->query($sql);
    if (!$result) {
        return 0;
    }

    $readyRows = array();
    while ($row = $result->fetch_assoc()) {
        $readyRows[] = $row;
    }
    if (method_exists($result, 'free')) {
        $result->free();
    }

    $released = 0;
    foreach ($readyRows as $row) {
        $guid = isset($row['customer_guid']) ? (string) $row['customer_guid'] : '';
        $hsId = isset($row['lidi_hs_id']) ? (string) $row['lidi_hs_id'] : '';

        // V206: absolutni pojistka proti automatickemu uvolneni TEST kopie.
        // To plati i pro stare zaznamy fronty, ktere zdedily status waiting.
        if ($guid !== '' && hsCustomerSyncHoldIsManualCopyTarget($mysqli, $guid)) {
            $state = hsCustomerSyncHoldCustomerState($mysqli, $guid);
            $branchId = $state && isset($state['lidi_sw_id']) ? (int) $state['lidi_sw_id'] : 0;
            hsCustomerSyncHoldQueueGuid($mysqli, $guid, $branchId, 'manual');
            continue;
        }

        if ($guid !== '' && hsCustomerSyncHoldIsRealHairSoftId($hsId)) {
            if (hsCustomerSyncHoldReleaseGuid($mysqli, $guid, $hsId)) {
                $released++;
            }
        }
    }
    return $released;
}
