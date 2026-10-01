<?php
// Verejny vstup je /str/customer-copy-test.php.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    header('Location: ../../str/login.php');
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../cfg/nastaveni.php';
require_once '../fce/SpolecneFunkce.php';
require_once __DIR__ . '/multi_company_auth.php';
require_once __DIR__ . '/customer-copy-test-lib.php';
require_once __DIR__ . '/customer-dependent-sync.php';

function hsCustomerCopyTestRedirect($sourceGuid = '')
{
    $url = 'https://klient.hairsoft.cz/str/index.php?strana=Zakaznici';
    if (is_string($sourceGuid) && $sourceGuid !== '') {
        $url = 'https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&osoba_guid=' . rawurlencode($sourceGuid);
    }
    header('Location: ' . $url);
    exit;
}

function hsCustomerCopyTestFail($message, $sourceGuid = '')
{
    hsMultiSetFlash('error', (string) $message);
    hsCustomerCopyTestRedirect($sourceGuid);
}

function hsCustomerCopyTestPostString($key)
{
    return isset($_POST[$key]) && !is_array($_POST[$key]) ? trim((string) $_POST[$key]) : '';
}

if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
    header('Location: https://klient.hairsoft.cz/str/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$sourceGuid = hsCustomerCopyTestPostString('source_guid');
$csrf = hsCustomerCopyTestPostString('csrf');
if (!hsMultiCheckCsrf($csrf)) {
    http_response_code(403);
    exit('Neplatný bezpečnostní token. Obnovte stránku a zkuste to znovu.');
}

$currentIdentity = hsCustomerCopyTestCurrentIdentity($mysqli);
if (!$currentIdentity) {
    hsCustomerCopyTestFail('Kopii zákazníka může provést administrátor nebo manažer s oprávněním k zákazníkům.', $sourceGuid);
}
$currentOwnerId = (int) $currentIdentity['owner_id'];
$currentSoft = isset($currentIdentity['soft']) ? (string) $currentIdentity['soft'] : 'HairSoft';
$action = hsCustomerCopyTestPostString('action');

if ($action === 'copy') {
    if ($sourceGuid === '' || strlen($sourceGuid) > 96) {
        hsCustomerCopyTestFail('Zdrojového zákazníka se nepodařilo bezpečně určit.', '');
    }

    $targetSelector = hsCustomerCopyTestPostString('target_selector');
    $targetBranchId = (int) hsCustomerCopyTestPostString('target_branch_id');
    if (!preg_match('/^[a-f0-9]{24}$/', $targetSelector) || $targetBranchId <= 0) {
        hsCustomerCopyTestFail('Vyberte cílovou firmu a pobočku.', $sourceGuid);
    }

    $targetValidation = hsMultiFindAccountBySelector($mysqli, $targetSelector);
    if (!$targetValidation || !isset($targetValidation['identity'])) {
        hsCustomerCopyTestFail('Cílová firma už není bezpečně přihlášená v přepínači. Přidejte ji znovu.', $sourceGuid);
    }
    $targetIdentity = $targetValidation['identity'];
    if (!isset($targetIdentity['type']) || !in_array((string) $targetIdentity['type'], array('admin', 'manager'), true)) {
        hsCustomerCopyTestFail('Cílová firma musí být v přepínači přihlášená administrátorem nebo manažerem s oprávněním k zákazníkům.', $sourceGuid);
    }
    $targetOwnerId = (int) $targetIdentity['owner_id'];
    if ($targetOwnerId <= 0 || $targetOwnerId === $currentOwnerId) {
        hsCustomerCopyTestFail('Cílem musí být jiná firma.', $sourceGuid);
    }
    if (strcasecmp($currentSoft, (string) $targetIdentity['soft']) !== 0) {
        hsCustomerCopyTestFail('Cílový účet patří do jiného systému.', $sourceGuid);
    }
    if (!hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $targetIdentity, $targetBranchId)) {
        hsCustomerCopyTestFail('Vybraná pobočka nepatří do cílové firmy nebo k ní přihlášený účet nemá oprávnění pro práci se zákazníky.', $sourceGuid);
    }

    $targetBranches = hsCustomerCopyTestAllowedBranches($mysqli, $targetIdentity);
    $targetBranch = null;
    foreach ($targetBranches as $branch) {
        if ((int) $branch['id'] === $targetBranchId) {
            $targetBranch = $branch;
            break;
        }
    }
    if (!$targetBranch || (int) $targetBranch['group_id'] <= 0) {
        hsCustomerCopyTestFail('U cílové pobočky se nepodařilo určit HairSoft skupinu.', $sourceGuid);
    }
    $targetGroupId = (int) $targetBranch['group_id'];

    $stmt = $mysqli->prepare("SELECT * FROM `klient_lidi` WHERE `lidi_guid`=? AND `lidi_aktivni`=1 LIMIT 1");
    if (!$stmt) {
        hsCustomerCopyTestFail('Zdrojového zákazníka se nepodařilo načíst.', $sourceGuid);
    }
    $stmt->bind_param('s', $sourceGuid);
    if (!$stmt->execute()) {
        $stmt->close();
        hsCustomerCopyTestFail('Zdrojového zákazníka se nepodařilo načíst.', $sourceGuid);
    }
    $result = $stmt->get_result();
    $source = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$source) {
        hsCustomerCopyTestFail('Zdrojový zákazník už neexistuje nebo není aktivní.', $sourceGuid);
    }

    $sourceBranchId = isset($source['lidi_sw_id']) ? (int) $source['lidi_sw_id'] : 0;
    $sourceGroupId = isset($source['lidi_skupinaID']) ? (int) $source['lidi_skupinaID'] : 0;
    if ($sourceBranchId <= 0 || !hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $currentIdentity, $sourceBranchId)) {
        hsCustomerCopyTestFail('Zdrojový zákazník nepatří do dostupné pobočky této firmy nebo přihlášený účet nemá oprávnění pro práci se zákazníky.', $sourceGuid);
    }
    if ($sourceGroupId > 0 && $sourceGroupId === $targetGroupId) {
        hsCustomerCopyTestFail('Zdrojová a cílová pobočka používají stejnou HairSoft skupinu. Kopie byla z bezpečnostních důvodů zastavena.', $sourceGuid);
    }

    if (!hsCustomerCopyTestEnsureTable($mysqli)) {
        hsCustomerCopyTestFail('Nepodařilo se připravit izolovaný audit kopie. Databáze zákazníků nebyla změněna.', $sourceGuid);
    }
    if (!hsCustomerCopyTestEnsureTimelineSyncStampColumn($mysqli)) {
        hsCustomerCopyTestFail('Nepodařilo se připravit technické datum Timeline pro synchronizaci. Zdrojová ani cílová firma nebyla změněna.', $sourceGuid);
    }
    if (!hsCustomerSyncHoldEnsureTable($mysqli)) {
        hsCustomerCopyTestFail('Nepodařilo se připravit bezpečnou frontu pro dvoufázovou synchronizaci. Zdrojová ani cílová firma nebyla změněna.', $sourceGuid);
    }
    if (!hsCustomerCopyTestEnsureTimelineStageTable($mysqli)) {
        hsCustomerCopyTestFail('Nepodařilo se připravit izolovaný mezisklad Timeline. Zdrojová ani cílová firma nebyla změněna.', $sourceGuid);
    }

    try {
        if (hsCustomerCopyTestExistingActiveCopy($mysqli, $currentOwnerId, $sourceGuid, $targetOwnerId, $targetBranchId)) {
            throw new RuntimeException('Do této firmy a pobočky už existuje aktivní kopie tohoto zákazníka.');
        }
        if (hsCustomerCopyTestTargetDuplicateExists($mysqli, $source, $targetGroupId)) {
            throw new RuntimeException('V cílové firmě už existuje aktivní zákazník se stejným mobilem, telefonem nebo e-mailem. Kopie nebyla vytvořena.');
        }

        $photoRows = hsCustomerCopyTestLoadPhotoRows($mysqli, $sourceGuid);
        $photoPlan = hsCustomerCopyTestBuildPhotoPlan($photoRows);
        $photoColumns = count($photoPlan) > 0 ? hsCustomerCopyTestPhotoColumns($mysqli) : array();

        $targetGuid = hsCustomerCopyTestGenerateGuid();
        $stmt = $mysqli->prepare("SELECT 1 FROM `klient_lidi` WHERE `lidi_guid`=? LIMIT 1");
        if (!$stmt) {
            throw new RuntimeException('Nepodařilo se ověřit nový identifikátor zákazníka.');
        }
        $stmt->bind_param('s', $targetGuid);
        $stmt->execute();
        $existingGuidResult = $stmt->get_result();
        $guidExists = $existingGuidResult && $existingGuidResult->num_rows > 0;
        $stmt->close();
        if ($guidExists) {
            throw new RuntimeException('Nepodařilo se vytvořit unikátní identifikátor zákazníka. Zkuste akci znovu.');
        }

        $createdFiles = array();
        hsCustomerCopyTestCopyPhotoFiles($photoPlan, $createdFiles);

        $transactionStarted = false;
        try {
            if (!$mysqli->begin_transaction()) {
                throw new RuntimeException('Databázovou transakci se nepodařilo spustit.');
            }
            $transactionStarted = true;

            $targetCustomerId = hsCustomerCopyTestInsertCustomer($mysqli, $source, $targetGuid, $targetBranchId, $targetGroupId);
            if (!hsCustomerSyncHoldQueueGuid($mysqli, $targetGuid, $targetBranchId, 'manual')) {
                throw new RuntimeException('Cílového zákazníka se nepodařilo zařadit do bezpečné fronty pro čekání na HairSoft ID.');
            }
            // V207: Timeline se zatim NESMI objevit v produkcni tabulce timeline.
            // Pocet si spocitame, snapshot vlozime az po vytvoreni auditniho logu.
            $timelineCount = hsCustomerCopyTestCountTimeline($mysqli, $sourceGuid);

            $photoCount = 0;
            foreach ($photoPlan as $photoItem) {
                hsCustomerCopyTestClonePhotoMetadata(
                    $mysqli,
                    $photoColumns,
                    $photoItem['row'],
                    $targetGuid,
                    $photoItem['target_name'],
                    $targetBranchId,
                    $targetGroupId
                );
                $photoCount++;
            }

            $targetCompanyLabel = hsMultiCompanyLabel($mysqli, $targetIdentity, $targetBranchId);
            $customerName = trim(hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_name') . ' ' . hsCustomerCopyTestSafeSourceValue($source, 'lidi_hs_surname'));
            $copyLogId = hsCustomerCopyTestInsertLog($mysqli, array(
                'source_guid' => $sourceGuid,
                'target_guid' => $targetGuid,
                'target_customer_id' => $targetCustomerId,
                'source_owner_k_id' => $currentOwnerId,
                'target_owner_k_id' => $targetOwnerId,
                'source_branch_id' => $sourceBranchId,
                'target_branch_id' => $targetBranchId,
                'target_company_label' => $targetCompanyLabel,
                'target_branch_name' => (string) $targetBranch['name'],
                'customer_name' => $customerName,
                'timeline_count' => $timelineCount,
                'photo_count' => $photoCount
            ));

            $stagedTimelineCount = hsCustomerCopyTestStageTimeline($mysqli, $copyLogId, $sourceGuid, $targetGuid, $targetBranchId);
            if ($stagedTimelineCount !== $timelineCount) {
                throw new RuntimeException('Kontrola Timeline neprošla: očekáváno ' . $timelineCount . ', do meziskladu uloženo ' . $stagedTimelineCount . '.');
            }

            if (!$mysqli->commit()) {
                throw new RuntimeException('Databázovou transakci se nepodařilo potvrdit.');
            }
            $transactionStarted = false;

            hsMultiSetFlash(
                'success',
                'Kopie byla vytvořena v ' . (string) $targetBranch['name'] . '. Nejprve synchronizujte samotného zákazníka do HairSoft. Potom v cílové kartě přes Správu dat zadejte jeho skutečné HairSoft ID. Fotografie a soubory se uvolní hromadně, Timeline zůstane připravena k ručnímu odesílání po jednotlivých řádcích; timeline: ' . $timelineCount . ', fotografie: ' . $photoCount . '. Zdrojová firma zůstala beze změny.'
            );
            hsCustomerCopyTestRedirect($sourceGuid);
        } catch (Throwable $error) {
            if ($transactionStarted) {
                @$mysqli->rollback();
            }
            // Kompenzacni uklid chrani i instalace, kde by nektera legacy tabulka nebyla transakcni.
            hsCustomerCopyTestCompensateTarget($mysqli, $targetGuid);
            hsCustomerCopyTestDeleteFiles($createdFiles);
            throw $error;
        }
    } catch (Throwable $error) {
        hsCustomerCopyTestFail($error->getMessage(), $sourceGuid);
    }
}

if ($action === 'cleanup') {
    $logId = (int) hsCustomerCopyTestPostString('log_id');
    if ($logId <= 0 || !hsCustomerCopyTestTableExists($mysqli)) {
        hsCustomerCopyTestFail('Kopii se nepodařilo dohledat.', $sourceGuid);
    }

    $stmt = $mysqli->prepare("SELECT * FROM `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` WHERE `id`=? AND `source_owner_k_id`=? LIMIT 1");
    if (!$stmt) {
        hsCustomerCopyTestFail('Kopii se nepodařilo dohledat.', $sourceGuid);
    }
    $stmt->bind_param('ii', $logId, $currentOwnerId);
    if (!$stmt->execute()) {
        $stmt->close();
        hsCustomerCopyTestFail('Kopii se nepodařilo dohledat.', $sourceGuid);
    }
    $result = $stmt->get_result();
    $log = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$log || ($sourceGuid !== '' && (string) $log['source_guid'] !== $sourceGuid)) {
        hsCustomerCopyTestFail('Tato kopie nepatří k otevřenému zákazníkovi.', $sourceGuid);
    }
    $sourceGuid = (string) $log['source_guid'];

    $rememberedTarget = hsCustomerCopyTestFindRememberedOwner($mysqli, (int) $log['target_owner_k_id'], $currentSoft, (int) $log['target_branch_id']);
    if (!$rememberedTarget) {
        hsCustomerCopyTestFail('Cílová firma už není přihlášená v přepínači. Přidejte ji znovu a potom lze kopii bezpečně odstranit.', $sourceGuid);
    }
    $targetIdentity = $rememberedTarget['validation']['identity'];
    if (!hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $targetIdentity, (int) $log['target_branch_id'])) {
        hsCustomerCopyTestFail('Cílová pobočka už není dostupná pod přihlášenou cílovou firmou nebo účet nemá oprávnění k zákazníkům.', $sourceGuid);
    }

    $targetGuid = (string) $log['target_guid'];
    $stmt = $mysqli->prepare("SELECT `lidi_web_pc`,`lidi_aktivni` FROM `klient_lidi` WHERE `lidi_guid`=? LIMIT 1");
    if (!$stmt) {
        hsCustomerCopyTestFail('Stav kopie se nepodařilo ověřit.', $sourceGuid);
    }
    $stmt->bind_param('s', $targetGuid);
    $stmt->execute();
    $targetResult = $stmt->get_result();
    $target = $targetResult ? $targetResult->fetch_assoc() : null;
    $stmt->close();

    $photoNames = hsCustomerCopyTestPhotoNamesForGuid($mysqli, $targetGuid);

    if (!$target) {
        // Zaznam uz byl odstraneny; uklidime pouze osirela testovaci data pod jeho GUID.
        $mysqli->begin_transaction();
        hsCustomerCopyTestDeleteTimelineStageByTarget($mysqli, $targetGuid);
        $stmt = $mysqli->prepare("DELETE FROM `timeline` WHERE `timelineOsobaGuid`=?");
        if ($stmt) { $stmt->bind_param('s', $targetGuid); $stmt->execute(); $stmt->close(); }
        $stmt = $mysqli->prepare("DELETE FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID`=?");
        if ($stmt) { $stmt->bind_param('s', $targetGuid); $stmt->execute(); $stmt->close(); }
        hsCustomerSyncHoldRemoveQueueGuid($mysqli, $targetGuid);
        $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` SET `status`='removed',`updated_at`=NOW() WHERE `id`=?");
        if ($stmt) { $stmt->bind_param('i', $logId); $stmt->execute(); $stmt->close(); }
        $mysqli->commit();
        hsCustomerCopyTestDeletePhotoFilesByNames($photoNames);
        hsMultiSetFlash('success', 'Kopie už v cíli nebyla aktivní; zbylá data kopie byla uklizena. Zdrojová firma nebyla změněna.');
        hsCustomerCopyTestRedirect($sourceGuid);
    }

    if ((string) $target['lidi_web_pc'] === 'N') {
        // HairSoft PC ji jeste neprevzal: lze ji bezpecne odstranit jako ciste webovy zaznam kopie.
        $transactionStarted = false;
        try {
            if (!$mysqli->begin_transaction()) {
                throw new RuntimeException('Odstranění kopie se nepodařilo spustit.');
            }
            $transactionStarted = true;
            if (!hsCustomerCopyTestDeleteTimelineStageByTarget($mysqli, $targetGuid)) { throw new RuntimeException('Čekající Timeline kopie se nepodařilo odstranit.'); }
            $stmt = $mysqli->prepare("DELETE FROM `timeline` WHERE `timelineOsobaGuid`=?");
            if (!$stmt) { throw new RuntimeException('Timeline kopie se nepodařilo odstranit.'); }
            $stmt->bind_param('s', $targetGuid);
            if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('Timeline kopie se nepodařilo odstranit.'); }
            $stmt->close();

            $stmt = $mysqli->prepare("DELETE FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID`=?");
            if (!$stmt) { throw new RuntimeException('Fotografie kopie se nepodařilo odstranit.'); }
            $stmt->bind_param('s', $targetGuid);
            if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('Fotografie kopie se nepodařilo odstranit.'); }
            $stmt->close();

            $stmt = $mysqli->prepare("DELETE FROM `klient_lidi` WHERE `lidi_guid`=? AND `lidi_web_pc`='N'");
            if (!$stmt) { throw new RuntimeException('Zkopírovaného zákazníka se nepodařilo odstranit.'); }
            $stmt->bind_param('s', $targetGuid);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) { $stmt->close(); throw new RuntimeException('Stav zákazníka se mezitím změnil. Obnovte stránku a akci zopakujte.'); }
            $stmt->close();

            if (!hsCustomerSyncHoldRemoveQueueGuid($mysqli, $targetGuid)) {
                throw new RuntimeException('Čekající synchronizační frontu kopie se nepodařilo odstranit.');
            }

            $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` SET `status`='removed',`updated_at`=NOW() WHERE `id`=?");
            if (!$stmt) { throw new RuntimeException('Audit kopie se nepodařilo aktualizovat.'); }
            $stmt->bind_param('i', $logId);
            if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('Audit kopie se nepodařilo aktualizovat.'); }
            $stmt->close();

            if (!$mysqli->commit()) {
                throw new RuntimeException('Odstranění kopie se nepodařilo potvrdit.');
            }
            $transactionStarted = false;
            hsCustomerCopyTestDeletePhotoFilesByNames($photoNames);
            hsMultiSetFlash('success', 'Kopie byla odstraněna ještě před synchronizací do HairSoft. Zdrojová firma zůstala beze změny.');
            hsCustomerCopyTestRedirect($sourceGuid);
        } catch (Throwable $error) {
            if ($transactionStarted) {
                @$mysqli->rollback();
            }
            hsCustomerCopyTestFail($error->getMessage(), $sourceGuid);
        }
    }

    // PC ji uz pravdepodobne prevzalo. Pouzijeme stejnou znacku smazani jako standardni karta zakaznika,
    // aby odstraneni proslo existujici synchronizaci misto primeho mazani dat z DB.
    $transactionStarted = false;
    try {
        if (!$mysqli->begin_transaction()) {
            throw new RuntimeException('Požadavek na odstranění se nepodařilo spustit.');
        }
        $transactionStarted = true;
        if (!hsCustomerCopyTestDeleteTimelineStageByTarget($mysqli, $targetGuid)) { throw new RuntimeException('Čekající Timeline kopie se nepodařilo odstranit.'); }
        $stmt = $mysqli->prepare("UPDATE `klient_lidi` SET `lidi_web_pc`='D',`lidi_aktivni`=0 WHERE `lidi_guid`=?");
        if (!$stmt) { throw new RuntimeException('Požadavek na odstranění se nepodařilo připravit.'); }
        $stmt->bind_param('s', $targetGuid);
        if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('Požadavek na odstranění se nepodařilo uložit.'); }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `" . HS_CUSTOMER_COPY_TEST_LOG_TABLE . "` SET `status`='delete_requested',`updated_at`=NOW() WHERE `id`=?");
        if (!$stmt) { throw new RuntimeException('Audit kopie se nepodařilo aktualizovat.'); }
        $stmt->bind_param('i', $logId);
        if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('Audit kopie se nepodařilo aktualizovat.'); }
        $stmt->close();

        if (!$mysqli->commit()) {
            throw new RuntimeException('Požadavek na odstranění se nepodařilo potvrdit.');
        }
        $transactionStarted = false;
        hsMultiSetFlash('success', 'Kopie už byla předána HairSoft. Proto byla bezpečně označena ke smazání standardní synchronizací cílové firmy; zdrojová firma zůstala beze změny.');
        hsCustomerCopyTestRedirect($sourceGuid);
    } catch (Throwable $error) {
        if ($transactionStarted) {
            @$mysqli->rollback();
        }
        hsCustomerCopyTestFail($error->getMessage(), $sourceGuid);
    }
}

hsCustomerCopyTestFail('Neznámá akce kopie.', $sourceGuid);
