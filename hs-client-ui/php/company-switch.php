<?php
// Verejny vstup je /str/company-switch.php.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    header('Location: ../../str/login.php');
    exit;
}

session_start();
require_once '../cfg/nastaveni.php';
require_once __DIR__ . '/multi_company_auth.php';
require_once 'strana/_VytvoritTabulkyTrzebProNovyRok.php';

function hsCompanySwitchRedirect($url)
{
    header('Location: ' . $url);
    exit;
}

function hsCompanySwitchRequireCsrf()
{
    $csrf = isset($_POST['csrf']) && !is_array($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    if (!hsMultiCheckCsrf($csrf)) {
        http_response_code(403);
        exit('Neplatny bezpecnostni token. Obnovte stranku a zkuste to znovu.');
    }
}

function hsCompanySwitchPrepareYearTables($mysqli)
{
    $year = date('Y');
    OtestujVytvorTabulkuJednotlivci($year, $mysqli);
    OtestujVytvorTabulkuStrediska($year, $mysqli);
}

$action = isset($_POST['action']) && !is_array($_POST['action']) ? (string) $_POST['action'] : '';

if ($action === 'logout_all') {
    hsCompanySwitchRequireCsrf();
    $payload = hsMultiReadCookie();
    foreach ($payload['accounts'] as $selector => $validator) {
        hsMultiRevokeSelector($mysqli, $selector);
    }
    hsMultiWriteCookie(hsMultiEmptyCookiePayload());
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/login.php');
}

if ($action === 'logout_current') {
    hsCompanySwitchRequireCsrf();
    $currentSoft = isset($_SESSION['k_soft']) && !is_array($_SESSION['k_soft']) ? (string) $_SESSION['k_soft'] : 'HairSoft';
    $currentSelector = isset($_SESSION['hs_multi_active_selector']) && is_string($_SESSION['hs_multi_active_selector']) ? $_SESSION['hs_multi_active_selector'] : '';
    if ($currentSelector !== '') {
        hsMultiRevokeSelector($mysqli, $currentSelector);
        hsMultiRemoveCookieAccount($currentSelector);
    }

    hsMultiClearSessionKeepLanguage();
    if (hsMultiTryAutoLogin($mysqli, $currentSoft)) {
        hsCompanySwitchPrepareYearTables($mysqli);
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }
    hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/login.php');
}

if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
    hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/login.php');
}

hsCompanySwitchRequireCsrf();

if ($action === 'switch') {
    $selector = isset($_POST['selector']) && !is_array($_POST['selector']) ? (string) $_POST['selector'] : '';
    $validation = hsMultiFindAccountBySelector($mysqli, $selector);
    if (!$validation) {
        hsMultiRemoveCookieAccount($selector);
        hsMultiSetFlash('error', 'Přihlášení této firmy již není platné. Přidejte ji prosím znovu.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }

    $currentSoft = isset($_SESSION['k_soft']) && !is_array($_SESSION['k_soft']) ? (string) $_SESSION['k_soft'] : 'HairSoft';
    if (strcasecmp($currentSoft, (string) $validation['identity']['soft']) !== 0) {
        hsMultiSetFlash('error', 'Tento účet patří do jiného systému a zde ho nelze přepnout.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }

    hsMultiSetActiveInCookie($selector);
    hsMultiApplyIdentityToSession($validation['identity'], $selector, (int) $validation['row']['last_branch_id'], isset($validation['row']['last_page']) ? (string) $validation['row']['last_page'] : '');
    hsCompanySwitchPrepareYearTables($mysqli);
    hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
}

if ($action === 'add') {
    $login = isset($_POST['company_login']) && !is_array($_POST['company_login']) ? (string) $_POST['company_login'] : '';
    $password = isset($_POST['company_password']) && !is_array($_POST['company_password']) ? (string) $_POST['company_password'] : '';
    $identity = hsMultiAuthenticateCredentials($mysqli, $login, $password);

    if (!$identity) {
        hsMultiSetFlash('error', 'Přihlášení se nepodařilo. Zkontrolujte login a heslo.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }

    $currentSoft = isset($_SESSION['k_soft']) && !is_array($_SESSION['k_soft']) ? (string) $_SESSION['k_soft'] : 'HairSoft';
    if (strcasecmp($currentSoft, (string) $identity['soft']) !== 0) {
        hsMultiSetFlash('error', 'Tento účet patří do jiného systému. Přepínač nyní spojuje pouze účty stejného systému.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }

    // Pokud uz je stejny ucet v tomto prohlizeci ulozen, nevytvarej dalsi token.
    $payload = hsMultiReadCookie();
    foreach ($payload['accounts'] as $selector => $validator) {
        $existing = hsMultiValidateToken($mysqli, $selector, $validator);
        if ($existing
            && $existing['identity']['type'] === $identity['type']
            && (int) $existing['identity']['principal_id'] === (int) $identity['principal_id']
            && (int) $existing['identity']['owner_id'] === (int) $identity['owner_id']) {
            hsMultiSetActiveInCookie($selector);
            hsMultiApplyIdentityToSession($existing['identity'], $selector, (int) $existing['row']['last_branch_id'], isset($existing['row']['last_page']) ? (string) $existing['row']['last_page'] : '');
            hsCompanySwitchPrepareYearTables($mysqli);
            hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
        }
    }

    $payload = hsMultiReadCookie();
    if (count($payload['accounts']) >= HS_MULTI_MAX_ACCOUNTS) {
        hsMultiSetFlash('error', 'Na tomto zařízení lze mít uložených maximálně ' . HS_MULTI_MAX_ACCOUNTS . ' firem.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }

    $issued = hsMultiIssueToken($mysqli, $identity, 0, '');
    if (!$issued) {
        hsMultiSetFlash('error', 'Firmu se nepodařilo bezpečně uložit. Zkuste to prosím znovu.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }

    hsMultiApplyIdentityToSession($identity, $issued['selector'], 0, '');
    hsCompanySwitchPrepareYearTables($mysqli);
    hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
}

if ($action === 'remove') {
    $selector = isset($_POST['selector']) && !is_array($_POST['selector']) ? (string) $_POST['selector'] : '';
    $currentSelector = isset($_SESSION['hs_multi_active_selector']) && is_string($_SESSION['hs_multi_active_selector']) ? $_SESSION['hs_multi_active_selector'] : '';
    if ($selector === $currentSelector) {
        hsMultiSetFlash('error', 'Aktuální firmu odpojíte volbou Odhlásit tuto firmu.');
        hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
    }
    $validation = hsMultiFindAccountBySelector($mysqli, $selector);
    if ($validation) {
        hsMultiRevokeSelector($mysqli, $selector);
    }
    hsMultiRemoveCookieAccount($selector);
    hsMultiSetFlash('success', 'Uložené přihlášení firmy bylo odebráno z tohoto zařízení.');
    hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
}

hsCompanySwitchRedirect('https://klient.hairsoft.cz/str/index.php');
