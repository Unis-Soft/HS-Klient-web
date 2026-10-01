<?php
// HairSoft Klient V210 - rucni dokonceni synchronizace kopie v cilove firme.
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

function hsCustomerCopySyncPostString($key)
{
    return isset($_POST[$key]) && !is_array($_POST[$key]) ? trim((string) $_POST[$key]) : '';
}

function hsCustomerCopySyncRedirect($guid, $timeline = false)
{
    $url = 'https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&osoba_guid=' . rawurlencode((string) $guid);
    if ($timeline) {
        $url .= '&NavratTimeline=1';
    }
    header('Location: ' . $url);
    exit;
}

function hsCustomerCopySyncFail($message, $guid, $timeline = false)
{
    hsMultiSetFlash('error', (string) $message);
    hsCustomerCopySyncRedirect($guid, $timeline);
}

if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
    header('Location: https://klient.hairsoft.cz/str/login.php');
    exit;
}
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$guid = hsCustomerCopySyncPostString('target_guid');
$action = hsCustomerCopySyncPostString('action');
$csrf = hsCustomerCopySyncPostString('csrf');

if ($guid === '' || strlen($guid) > 96) {
    http_response_code(400);
    exit('Neplatný zákazník.');
}
if (!hsMultiCheckCsrf($csrf)) {
    http_response_code(403);
    exit('Neplatný bezpečnostní token. Obnovte stránku a zkuste to znovu.');
}

$identity = hsCustomerCopyTestCurrentIdentity($mysqli);
if (!$identity) {
    hsCustomerCopySyncFail('Ruční synchronizaci kopie může dokončit administrátor nebo manažer s oprávněním k zákazníkům.', $guid, true);
}
$ownerId = (int) $identity['owner_id'];
$targetLog = hsCustomerCopyTestTargetLog($mysqli, $ownerId, $guid);
if (!$targetLog) {
    hsCustomerCopySyncFail('Tento zákazník není aktivní kopií této firmy.', $guid, true);
}
$targetBranchId = (int) $targetLog['target_branch_id'];
$copyLogId = (int) $targetLog['id'];
if (!hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $identity, $targetBranchId)) {
    hsCustomerCopySyncFail('Přihlášený účet nemá oprávnění dokončit synchronizaci této cílové pobočky.', $guid, true);
}

if ($action === 'assign_id') {
    $hsId = hsCustomerCopySyncPostString('hairsoft_id');
    if (!preg_match('/^[0-9]{1,10}$/', $hsId) || !hsCustomerSyncHoldIsRealHairSoftId($hsId)) {
        hsCustomerCopySyncFail('Zadejte platné číselné ID zákazníka z HairSoft.', $guid, false);
    }

    try {
        if (!hsCustomerSyncHoldManualAssignId($mysqli, $guid, $hsId, $copyLogId)) {
            throw new RuntimeException('HairSoft ID se nepodařilo přiřadit.');
        }
        hsMultiSetFlash('success', 'HairSoft ID ' . $hsId . ' bylo přiřazeno. Fotografie a soubory jsou nyní připravené pro cílový HairSoft. Timeline posílejte jednotlivě tlačítkem synchronizace u každého řádku.');
        hsCustomerCopySyncRedirect($guid, true);
    } catch (Throwable $error) {
        hsCustomerCopySyncFail($error->getMessage(), $guid, false);
    }
}

if ($action === 'timeline_confirm') {
    $manualState = hsCustomerSyncHoldManualState($mysqli, $guid, $copyLogId);
    if (!$manualState || empty($manualState['manual_confirmed'])) {
        hsCustomerCopySyncFail('Nejdříve ve Správě dat ručně potvrďte skutečné HairSoft ID zákazníka.', $guid, true);
    }
    if ((int) $manualState['timeline_prepared'] <= 0) {
        hsCustomerCopySyncFail('Není zde žádný Timeline záznam čekající na potvrzení synchronizace.', $guid, true);
    }

    try {
        $confirmed = hsCustomerSyncHoldManualConfirmPreparedTimeline($mysqli, $guid, $copyLogId);
        if ($confirmed <= 0) {
            throw new RuntimeException('Potvrzení předchozí Timeline synchronizace se nepodařilo uložit.');
        }
        hsMultiSetFlash('success', 'Synchronizace Timeline byla potvrzena. Můžete připravit další záznam.');
        hsCustomerCopySyncRedirect($guid, true);
    } catch (Throwable $error) {
        hsCustomerCopySyncFail($error->getMessage(), $guid, true);
    }
}

if ($action === 'timeline_release') {
    $timelineId = (int) hsCustomerCopySyncPostString('timeline_id');
    if ($timelineId <= 0) {
        hsCustomerCopySyncFail('Timeline záznam se nepodařilo určit.', $guid, true);
    }

    $manualState = hsCustomerSyncHoldManualState($mysqli, $guid, $copyLogId);
    if (!$manualState || empty($manualState['manual_confirmed'])) {
        hsCustomerCopySyncFail('Nejdříve ve Správě dat ručně potvrďte skutečné HairSoft ID zákazníka.', $guid, true);
    }
    if ((int) $manualState['timeline_prepared'] > 0) {
        hsCustomerCopySyncFail('Nejdříve potvrďte dokončení předchozí synchronizace Timeline v HairSoft.', $guid, true);
    }
    if (!hsCustomerSyncHoldManualReleaseTimelineRow($mysqli, $guid, $timelineId, $copyLogId)) {
        hsCustomerCopySyncFail('Tento Timeline záznam už byl připraven nebo ho nelze bezpečně odeslat.', $guid, true);
    }

    hsMultiSetFlash('success', 'Jeden Timeline záznam byl připraven pro HairSoft. Než připravíte další řádek, nechte proběhnout synchronizaci v HairSoft.');
    hsCustomerCopySyncRedirect($guid, true);
}

hsCustomerCopySyncFail('Neznámá akce synchronizace.', $guid, false);
