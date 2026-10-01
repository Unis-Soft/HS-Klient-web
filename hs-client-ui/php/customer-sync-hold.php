<?php
// V202 - AJAX heartbeat pro dvoufázovou synchronizaci nových zákazníků.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    http_response_code(404);
    exit;
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('ok' => false, 'released' => 0));
    exit;
}

if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
    http_response_code(401);
    echo json_encode(array('ok' => false, 'released' => 0));
    exit;
}

require_once '../cfg/nastaveni.php';
require_once __DIR__ . '/multi_company_auth.php';
require_once __DIR__ . '/customer-dependent-sync.php';

// Zpracovat aktualni firmu i dalsi HairSoft firmy, ktere jsou bezpecne ulozene v novem switchi.
// Tim se muze cil B uvolnit i v okamziku, kdy uzivatel zustal otevreny ve firme A.
$ownerIds = array();
$currentOwnerId = isset($_SESSION['k_id']) && !is_array($_SESSION['k_id']) ? (int) $_SESSION['k_id'] : 0;
if ($currentOwnerId > 0) {
    $ownerIds[$currentOwnerId] = true;
}

if (function_exists('hsMultiListAccounts') && function_exists('hsMultiFindAccountBySelector')) {
    $accounts = hsMultiListAccounts($mysqli, 'HairSoft');
    foreach ($accounts as $account) {
        $selector = isset($account['selector']) ? (string) $account['selector'] : '';
        if ($selector === '') {
            continue;
        }
        $validation = hsMultiFindAccountBySelector($mysqli, $selector);
        if ($validation && isset($validation['identity']['owner_id'])) {
            $ownerId = (int) $validation['identity']['owner_id'];
            if ($ownerId > 0) {
                $ownerIds[$ownerId] = true;
            }
        }
    }
}

$released = 0;
foreach (array_keys($ownerIds) as $ownerId) {
    $released += hsCustomerSyncHoldProcessReady($mysqli, 50, (int) $ownerId);
}

echo json_encode(array('ok' => true, 'released' => (int) $released));
