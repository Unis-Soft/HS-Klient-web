<?php
/**
 * HairSoft Klient V194 - vice firem, dlouhodobe prihlaseni a posledni pracovni sekce pro kazdou firmu.
 *
 * Hesla se do cookie ani do nove tabulky neukladaji. Prohlizec drzi pouze
 * nahodny bearer token; v DB je ulozen jen SHA-256 hash jeho validatoru.
 */

if (!defined('HS_MULTI_COOKIE_NAME')) {
    define('HS_MULTI_COOKIE_NAME', 'hs_klient_accounts');
}
if (!defined('HS_MULTI_TOKEN_DAYS')) {
    define('HS_MULTI_TOKEN_DAYS', 365);
}
if (!defined('HS_MULTI_MAX_ACCOUNTS')) {
    define('HS_MULTI_MAX_ACCOUNTS', 30);
}

function hsMultiBase64UrlEncode($value)
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function hsMultiBase64UrlDecode($value)
{
    $value = strtr((string) $value, '-_', '+/');
    $padding = strlen($value) % 4;
    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }
    return base64_decode($value, true);
}

function hsMultiEmptyCookiePayload()
{
    return array('v' => 1, 'active' => '', 'accounts' => array());
}

function hsMultiReadCookie()
{
    $payload = hsMultiEmptyCookiePayload();
    if (!isset($_COOKIE[HS_MULTI_COOKIE_NAME]) || is_array($_COOKIE[HS_MULTI_COOKIE_NAME])) {
        return $payload;
    }

    $decoded = hsMultiBase64UrlDecode($_COOKIE[HS_MULTI_COOKIE_NAME]);
    if ($decoded === false || $decoded === '') {
        return $payload;
    }

    $data = json_decode($decoded, true);
    if (!is_array($data) || !isset($data['accounts']) || !is_array($data['accounts'])) {
        return $payload;
    }

    $clean = array();
    foreach ($data['accounts'] as $selector => $validator) {
        if (!is_string($selector) || !preg_match('/^[a-f0-9]{24}$/', $selector)) {
            continue;
        }
        if (!is_string($validator) || !preg_match('/^[A-Za-z0-9_-]{40,60}$/', $validator)) {
            continue;
        }
        $clean[$selector] = $validator;
        if (count($clean) >= HS_MULTI_MAX_ACCOUNTS) {
            break;
        }
    }

    $active = isset($data['active']) && is_string($data['active']) ? $data['active'] : '';
    if ($active !== '' && !isset($clean[$active])) {
        $active = '';
    }

    return array('v' => 1, 'active' => $active, 'accounts' => $clean);
}

function hsMultiWriteCookie($payload)
{
    if (!is_array($payload) || !isset($payload['accounts']) || !is_array($payload['accounts'])) {
        $payload = hsMultiEmptyCookiePayload();
    }

    if (count($payload['accounts']) === 0) {
        if (!headers_sent()) {
            setcookie(HS_MULTI_COOKIE_NAME, '', array(
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ));
        }
        unset($_COOKIE[HS_MULTI_COOKIE_NAME]);
        return;
    }

    if (!isset($payload['active']) || !isset($payload['accounts'][$payload['active']])) {
        $keys = array_keys($payload['accounts']);
        $payload['active'] = count($keys) ? $keys[0] : '';
    }

    $encoded = hsMultiBase64UrlEncode(json_encode(array(
        'v' => 1,
        'active' => $payload['active'],
        'accounts' => $payload['accounts']
    ), JSON_UNESCAPED_SLASHES));

    if (!headers_sent()) {
        setcookie(HS_MULTI_COOKIE_NAME, $encoded, array(
            'expires' => time() + (HS_MULTI_TOKEN_DAYS * 86400),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ));
        $_COOKIE[HS_MULTI_COOKIE_NAME] = $encoded;
    }
}

function hsMultiEnsureStateColumns($mysqli)
{
    $column = @$mysqli->query("SHOW COLUMNS FROM `k_klient_remember_tokens` LIKE 'last_page'");
    if (!$column || $column->num_rows === 0) {
        if (@$mysqli->query("ALTER TABLE `k_klient_remember_tokens` ADD COLUMN `last_page` VARCHAR(64) NOT NULL DEFAULT '' AFTER `last_branch_id`") === false) {
            return false;
        }
    }
    return true;
}

function hsMultiEnsureTable($mysqli)
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    // Bezne requesty delaji jen lehkou kontrolu existence. DDL probiha pouze pri prvnim nasazeni.
    $exists = @$mysqli->query("SHOW TABLES LIKE 'k_klient_remember_tokens'");
    if ($exists && $exists->num_rows > 0) {
        $ready = hsMultiEnsureStateColumns($mysqli);
        return $ready;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `k_klient_remember_tokens` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `selector` CHAR(24) NOT NULL,
        `validator_hash` CHAR(64) NOT NULL,
        `account_type` VARCHAR(16) NOT NULL,
        `principal_id` INT NOT NULL,
        `owner_k_id` INT NOT NULL,
        `soft` VARCHAR(32) NOT NULL DEFAULT 'HairSoft',
        `credential_fingerprint` CHAR(64) NOT NULL,
        `last_branch_id` INT NOT NULL DEFAULT 0,
        `last_page` VARCHAR(64) NOT NULL DEFAULT '',
        `created_at` DATETIME NOT NULL,
        `last_used_at` DATETIME NULL,
        `expires_at` DATETIME NOT NULL,
        `revoked_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_k_klient_remember_selector` (`selector`),
        KEY `idx_k_klient_remember_owner` (`owner_k_id`),
        KEY `idx_k_klient_remember_expiry` (`expires_at`)
    ) ENGINE=InnoDB";

    $ready = (@$mysqli->query($sql) !== false);
    if ($ready) {
        $ready = hsMultiEnsureStateColumns($mysqli);
    }
    return $ready;
}

function hsMultiCsrfToken()
{
    if (!isset($_SESSION['hs_multi_csrf']) || !is_string($_SESSION['hs_multi_csrf']) || strlen($_SESSION['hs_multi_csrf']) < 32) {
        $_SESSION['hs_multi_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['hs_multi_csrf'];
}

function hsMultiCheckCsrf($token)
{
    return is_string($token)
        && isset($_SESSION['hs_multi_csrf'])
        && is_string($_SESSION['hs_multi_csrf'])
        && hash_equals($_SESSION['hs_multi_csrf'], $token);
}

function hsMultiCredentialFingerprint($type, $principalId, $storedPasswordHash)
{
    return hash('sha256', (string) $type . '|' . (int) $principalId . '|' . (string) $storedPasswordHash);
}

function hsMultiFetchOwner($mysqli, $ownerId)
{
    $stmt = $mysqli->prepare("SELECT `k_id`,`k_email`,`k_celejmeno`,`k_heslo`,`k_soft`,`k_aktivni`,`k_informace_blokace` FROM `k_uzivatele` WHERE `k_id`=? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $ownerId = (int) $ownerId;
    $stmt->bind_param('i', $ownerId);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$row || (string) $row['k_aktivni'] !== '1') {
        return null;
    }
    return $row;
}

function hsMultiLoadIdentity($mysqli, $type, $principalId)
{
    $type = (string) $type;
    $principalId = (int) $principalId;
    if ($principalId <= 0) {
        return null;
    }

    if ($type === 'admin') {
        $owner = hsMultiFetchOwner($mysqli, $principalId);
        if (!$owner) {
            return null;
        }
        return array(
            'type' => 'admin',
            'principal_id' => (int) $owner['k_id'],
            'owner_id' => (int) $owner['k_id'],
            'soft' => trim((string) $owner['k_soft']) !== '' ? (string) $owner['k_soft'] : 'HairSoft',
            'display_name' => trim((string) $owner['k_celejmeno']) !== '' ? (string) $owner['k_celejmeno'] : (string) $owner['k_email'],
            'login_label' => (string) $owner['k_email'],
            'credential_fingerprint' => hsMultiCredentialFingerprint('admin', (int) $owner['k_id'], $owner['k_heslo']),
            'session' => array(
                'uzivatel_prihlasen' => 'ano',
                'JePoduzivatel' => '0',
                'JeObsluha' => '0',
                'uzivatel_prijmeni_jmeno' => (string) $owner['k_celejmeno'],
                'k_informace_blokace' => (string) $owner['k_informace_blokace'],
                'k_aktivni' => (string) $owner['k_aktivni'],
                'k_id' => (string) $owner['k_id'],
                'k_email' => (string) $owner['k_email'],
                'k_poduzivatele_id' => '',
                'k_poduzivatele_skryt_citliva_data' => '0',
                'k_soft' => trim((string) $owner['k_soft']) !== '' ? (string) $owner['k_soft'] : 'HairSoft',
                'ObsluhaFoto' => '',
                'k_poduzivatele_foto' => ''
            )
        );
    }

    if ($type === 'manager') {
        $stmt = $mysqli->prepare("SELECT `k_poduzivatele_id`,`k_poduzivatele_hlavni`,`k_poduzivatele_email`,`k_poduzivatele_heslo`,`k_poduzivatele_jmeno`,`k_poduzivatele_foto` FROM `k_poduzivatele` WHERE `k_poduzivatele_id`=? LIMIT 1");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $principalId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if (!$row) {
            return null;
        }
        $owner = hsMultiFetchOwner($mysqli, (int) $row['k_poduzivatele_hlavni']);
        if (!$owner) {
            return null;
        }
        return array(
            'type' => 'manager',
            'principal_id' => (int) $row['k_poduzivatele_id'],
            'owner_id' => (int) $owner['k_id'],
            'soft' => trim((string) $owner['k_soft']) !== '' ? (string) $owner['k_soft'] : 'HairSoft',
            'display_name' => trim((string) $row['k_poduzivatele_jmeno']) !== '' ? (string) $row['k_poduzivatele_jmeno'] : (string) $row['k_poduzivatele_email'],
            'login_label' => (string) $row['k_poduzivatele_email'],
            'credential_fingerprint' => hsMultiCredentialFingerprint('manager', (int) $row['k_poduzivatele_id'], $row['k_poduzivatele_heslo']),
            'session' => array(
                'uzivatel_prihlasen' => 'ano',
                'JePoduzivatel' => '1',
                'JeObsluha' => '0',
                'uzivatel_prijmeni_jmeno' => (string) $row['k_poduzivatele_jmeno'],
                'k_informace_blokace' => '',
                'k_aktivni' => '1',
                'k_id' => (string) $owner['k_id'],
                'k_email' => (string) $owner['k_email'],
                'k_poduzivatele_id' => (string) $row['k_poduzivatele_id'],
                'k_poduzivatele_foto' => (string) $row['k_poduzivatele_foto'],
                'k_poduzivatele_skryt_citliva_data' => '0',
                'k_soft' => trim((string) $owner['k_soft']) !== '' ? (string) $owner['k_soft'] : 'HairSoft',
                'ObsluhaFoto' => '',
                'k_poduzivatele_obsluha_jmeno' => ''
            )
        );
    }

    if ($type === 'staff') {
        $stmt = $mysqli->prepare("SELECT `k_poduzivatele_obsluha_id`,`k_poduzivatele_obsluha_hlavni`,`k_poduzivatele_obsluha_login`,`k_poduzivatele_obsluha_heslo`,`k_poduzivatele_obsluha_jmeno`,`k_poduzivatele_obsluha_sw_id`,`k_poduzivatele_obsluha_id_hs`,`k_poduzivatele_skryt_citliva_data`,`k_poduzivatele_obsluha_foto` FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_id`=? LIMIT 1");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $principalId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if (!$row) {
            return null;
        }
        $owner = hsMultiFetchOwner($mysqli, (int) $row['k_poduzivatele_obsluha_hlavni']);
        if (!$owner) {
            return null;
        }
        return array(
            'type' => 'staff',
            'principal_id' => (int) $row['k_poduzivatele_obsluha_id'],
            'owner_id' => (int) $owner['k_id'],
            'soft' => trim((string) $owner['k_soft']) !== '' ? (string) $owner['k_soft'] : 'HairSoft',
            'display_name' => trim((string) $row['k_poduzivatele_obsluha_jmeno']) !== '' ? (string) $row['k_poduzivatele_obsluha_jmeno'] : (string) $row['k_poduzivatele_obsluha_login'],
            'login_label' => (string) $row['k_poduzivatele_obsluha_login'],
            'credential_fingerprint' => hsMultiCredentialFingerprint('staff', (int) $row['k_poduzivatele_obsluha_id'], $row['k_poduzivatele_obsluha_heslo']),
            'session' => array(
                'uzivatel_prihlasen' => 'ano',
                'JePoduzivatel' => '0',
                'JeObsluha' => '1',
                'uzivatel_prijmeni_jmeno' => (string) $row['k_poduzivatele_obsluha_jmeno'],
                'k_poduzivatele_obsluha_jmeno' => (string) $row['k_poduzivatele_obsluha_jmeno'],
                'k_poduzivatele_obsluha_id_hs' => (string) $row['k_poduzivatele_obsluha_id_hs'],
                'k_poduzivatele_obsluha_sw_id' => (string) $row['k_poduzivatele_obsluha_sw_id'],
                'k_informace_blokace' => '',
                'k_aktivni' => '1',
                'k_id' => (string) $owner['k_id'],
                'k_email' => (string) $owner['k_email'],
                'k_poduzivatele_id' => (string) $row['k_poduzivatele_obsluha_id'],
                'k_poduzivatele_skryt_citliva_data' => ((string) $row['k_poduzivatele_skryt_citliva_data'] === '1') ? '1' : '0',
                'k_soft' => trim((string) $owner['k_soft']) !== '' ? (string) $owner['k_soft'] : 'HairSoft',
                'ObsluhaFoto' => (string) $row['k_poduzivatele_obsluha_foto'],
                'k_poduzivatele_foto' => ''
            )
        );
    }

    return null;
}

function hsMultiCurrentSessionOwnerId()
{
    if (!isset($_SESSION['uzivatel_prihlasen']) || $_SESSION['uzivatel_prihlasen'] !== 'ano') {
        return 0;
    }
    return isset($_SESSION['k_id']) && !is_array($_SESSION['k_id']) ? (int) $_SESSION['k_id'] : 0;
}

function hsMultiCurrentIdentity($mysqli)
{
    $ownerId = hsMultiCurrentSessionOwnerId();
    if ($ownerId <= 0 || $ownerId === 2) {
        return null;
    }

    if (isset($_SESSION['JeObsluha']) && (string) $_SESSION['JeObsluha'] === '1') {
        $principalId = isset($_SESSION['k_poduzivatele_id']) ? (int) $_SESSION['k_poduzivatele_id'] : 0;
        return hsMultiLoadIdentity($mysqli, 'staff', $principalId);
    }
    if (isset($_SESSION['JePoduzivatel']) && (string) $_SESSION['JePoduzivatel'] === '1') {
        $principalId = isset($_SESSION['k_poduzivatele_id']) ? (int) $_SESSION['k_poduzivatele_id'] : 0;
        return hsMultiLoadIdentity($mysqli, 'manager', $principalId);
    }
    return hsMultiLoadIdentity($mysqli, 'admin', $ownerId);
}

function hsMultiTokenRow($mysqli, $selector)
{
    if (!preg_match('/^[a-f0-9]{24}$/', (string) $selector)) {
        return null;
    }
    $stmt = $mysqli->prepare("SELECT * FROM `k_klient_remember_tokens` WHERE `selector`=? AND `revoked_at` IS NULL AND `expires_at` > NOW() LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $selector);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function hsMultiValidateToken($mysqli, $selector, $validator)
{
    if (!hsMultiEnsureTable($mysqli)) {
        return null;
    }
    $row = hsMultiTokenRow($mysqli, $selector);
    if (!$row) {
        return null;
    }
    $actualHash = hash('sha256', (string) $validator);
    if (!hash_equals((string) $row['validator_hash'], $actualHash)) {
        return null;
    }

    $identity = hsMultiLoadIdentity($mysqli, (string) $row['account_type'], (int) $row['principal_id']);
    if (!$identity || (int) $identity['owner_id'] !== (int) $row['owner_k_id']) {
        return null;
    }
    if (!hash_equals((string) $row['credential_fingerprint'], (string) $identity['credential_fingerprint'])) {
        return null;
    }

    return array('row' => $row, 'identity' => $identity);
}

function hsMultiRevokeSelector($mysqli, $selector)
{
    if (!hsMultiEnsureTable($mysqli) || !preg_match('/^[a-f0-9]{24}$/', (string) $selector)) {
        return;
    }
    $stmt = $mysqli->prepare("UPDATE `k_klient_remember_tokens` SET `revoked_at`=NOW() WHERE `selector`=? AND `revoked_at` IS NULL");
    if ($stmt) {
        $stmt->bind_param('s', $selector);
        @$stmt->execute();
        $stmt->close();
    }
}

function hsMultiNormalizeLastPage($page)
{
    $page = is_string($page) ? trim($page) : '';
    if ($page === '') {
        return '';
    }

    // Ukladame jen bezpecnou sekci aplikace, ne identifikatory zakazniku nebo jine detailni parametry.
    $parentMap = array(
        'KartaOsoby' => 'Zakaznici',
        'VoucherHistorie' => 'Voucher',
        'NastaveniDetailUzivatele' => 'NastaveniPristupy',
        'NastaveniDetailObsluhy' => 'NastaveniPristupy',
        'KameraPage' => 'NastaveniKamerovyDohled'
    );
    if (isset($parentMap[$page])) {
        return $parentMap[$page];
    }

    $allowed = array(
        'Zakaznici',
        'RezervaceBonfero',
        'CelkoveTrzby', 'MesicniTrzby', 'TrzbyDleObsluhy', 'TrzbyOdPocatku',
        'Sklad', 'SkladXml',
        'Voucher',
        'SpokojenostStatistika', 'SpokojenostNastaveni',
        'Sms', 'PrichoziSMS', 'OdchoziSMS', 'PrichoziHovory',
        'Uzivatele', 'SeznamObsluh', 'ZmenaObsluhy', 'Dochazka',
        'NastaveniKamerovyDohled', 'NastaveniPristupy', 'NastaveniCiselnikPojistoven',
        'Cenik', 'MujUcet'
    );
    return in_array($page, $allowed, true) ? $page : '';
}

function hsMultiBuildAppUrl($branchId, $page)
{
    $branchId = max(0, (int) $branchId);
    $page = hsMultiNormalizeLastPage($page);
    $query = array();
    if ($branchId > 0) {
        $query['zmena_pobocky'] = '1';
        $query['aktivni_pobocka'] = (string) $branchId;
    }
    if ($page !== '') {
        $query['strana'] = $page;
    }
    $url = 'https://klient.hairsoft.cz/str/index.php';
    if (count($query) > 0) {
        $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
    return $url;
}

function hsMultiIssueToken($mysqli, $identity, $lastBranchId, $lastPage = '')
{
    if (!hsMultiEnsureTable($mysqli) || !$identity) {
        return null;
    }

    $payload = hsMultiReadCookie();
    if (count($payload['accounts']) >= HS_MULTI_MAX_ACCOUNTS) {
        return null;
    }

    $selector = bin2hex(random_bytes(12));
    $validator = hsMultiBase64UrlEncode(random_bytes(32));
    $validatorHash = hash('sha256', $validator);
    $type = (string) $identity['type'];
    $principalId = (int) $identity['principal_id'];
    $ownerId = (int) $identity['owner_id'];
    $soft = (string) $identity['soft'];
    $fingerprint = (string) $identity['credential_fingerprint'];
    $lastBranchId = max(0, (int) $lastBranchId);
    $lastPage = hsMultiNormalizeLastPage($lastPage);
    $expiresAt = date('Y-m-d H:i:s', time() + (HS_MULTI_TOKEN_DAYS * 86400));

    $stmt = $mysqli->prepare("INSERT INTO `k_klient_remember_tokens` (`selector`,`validator_hash`,`account_type`,`principal_id`,`owner_k_id`,`soft`,`credential_fingerprint`,`last_branch_id`,`last_page`,`created_at`,`last_used_at`,`expires_at`,`revoked_at`) VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW(),?,NULL)");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('sssiississ', $selector, $validatorHash, $type, $principalId, $ownerId, $soft, $fingerprint, $lastBranchId, $lastPage, $expiresAt);
    $ok = $stmt->execute();
    $stmt->close();
    if (!$ok) {
        return null;
    }

    $payload['accounts'][$selector] = $validator;
    $payload['active'] = $selector;
    hsMultiWriteCookie($payload);

    return array('selector' => $selector, 'validator' => $validator, 'last_branch_id' => $lastBranchId, 'last_page' => $lastPage);
}

function hsMultiAuthenticateCredentials($mysqli, $login, $password)
{
    $login = htmlspecialchars((string) $login, ENT_COMPAT);
    $passwordHash = sha1(htmlspecialchars((string) $password, ENT_COMPAT));
    if ($login === '' || $password === '' || strtoupper($login) === 'DEMO') {
        return null;
    }

    $stmt = $mysqli->prepare("SELECT `k_poduzivatele_obsluha_id` FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_login`=? AND `k_poduzivatele_obsluha_heslo`=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('ss', $login, $passwordHash);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            if ($row) {
                $stmt->close();
                return hsMultiLoadIdentity($mysqli, 'staff', (int) $row['k_poduzivatele_obsluha_id']);
            }
        }
        $stmt->close();
    }

    $stmt = $mysqli->prepare("SELECT `k_id` FROM `k_uzivatele` WHERE `k_email`=? AND `k_heslo`=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('ss', $login, $passwordHash);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            if ($row) {
                $stmt->close();
                return hsMultiLoadIdentity($mysqli, 'admin', (int) $row['k_id']);
            }
        }
        $stmt->close();
    }

    $stmt = $mysqli->prepare("SELECT `k_poduzivatele_id` FROM `k_poduzivatele` WHERE `k_poduzivatele_email`=? AND `k_poduzivatele_heslo`=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('ss', $login, $passwordHash);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            if ($row) {
                $stmt->close();
                return hsMultiLoadIdentity($mysqli, 'manager', (int) $row['k_poduzivatele_id']);
            }
        }
        $stmt->close();
    }

    return null;
}

function hsMultiApplyIdentityToSession($identity, $selector, $lastBranchId, $lastPage = '')
{
    $language = isset($_SESSION['languageCombo']) && is_string($_SESSION['languageCombo']) ? $_SESSION['languageCombo'] : 'cs';
    $_SESSION = array();
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        @session_regenerate_id(true);
    }

    $_SESSION['languageCombo'] = $language;
    foreach ($identity['session'] as $key => $value) {
        $_SESSION[$key] = $value;
    }
    $_SESSION['SeznamPravPoduzivatele'] = array();
    $_SESSION['SeznamPravPoduzivateleNaPobocky'] = array();
    $_SESSION['PocetPovolenychPobocek'] = 0;
    $_SESSION['SQL_ROK'] = date('Y');
    $_SESSION['pobocka_id'] = '';
    $_SESSION['pobocka_jmeno'] = '';
    $_SESSION['Mena_Klienta'] = '';
    $_SESSION['skupina_id'] = '';
    $_SESSION['PoduzivatelProbehloPresmerovani'] = '';
    $_SESSION['hs_multi_active_selector'] = (string) $selector;
    $_SESSION['hs_restore_branch_id'] = max(0, (int) $lastBranchId);
    $_SESSION['hs_restore_page'] = hsMultiNormalizeLastPage($lastPage);
    $_SESSION['hs_multi_csrf'] = bin2hex(random_bytes(24));
}

function hsMultiSetActiveInCookie($selector)
{
    $payload = hsMultiReadCookie();
    if (isset($payload['accounts'][$selector])) {
        $payload['active'] = $selector;
        hsMultiWriteCookie($payload);
    }
}

function hsMultiTryAutoLogin($mysqli, $requiredSoft = null)
{
    if (!hsMultiEnsureTable($mysqli)) {
        return false;
    }
    if (isset($_SESSION['uzivatel_prihlasen']) && $_SESSION['uzivatel_prihlasen'] === 'ano') {
        return true;
    }

    $payload = hsMultiReadCookie();
    if (count($payload['accounts']) === 0) {
        return false;
    }

    $selectors = array_keys($payload['accounts']);
    if ($payload['active'] !== '' && isset($payload['accounts'][$payload['active']])) {
        $selectors = array_values(array_unique(array_merge(array($payload['active']), $selectors)));
    }

    $changed = false;
    foreach ($selectors as $selector) {
        $validation = hsMultiValidateToken($mysqli, $selector, $payload['accounts'][$selector]);
        if (!$validation) {
            unset($payload['accounts'][$selector]);
            if ($payload['active'] === $selector) {
                $payload['active'] = '';
            }
            $changed = true;
            continue;
        }
        if ($requiredSoft !== null && $requiredSoft !== '' && strcasecmp((string) $requiredSoft, (string) $validation['identity']['soft']) !== 0) {
            continue;
        }

        $stmt = $mysqli->prepare("UPDATE `k_klient_remember_tokens` SET `last_used_at`=NOW() WHERE `selector`=?");
        if ($stmt) {
            $stmt->bind_param('s', $selector);
            @$stmt->execute();
            $stmt->close();
        }
        $payload['active'] = $selector;
        hsMultiWriteCookie($payload);
        hsMultiApplyIdentityToSession($validation['identity'], $selector, (int) $validation['row']['last_branch_id'], isset($validation['row']['last_page']) ? (string) $validation['row']['last_page'] : '');
        return true;
    }

    if ($changed) {
        hsMultiWriteCookie($payload);
    }
    return false;
}

function hsMultiEnsureCurrentSessionRegistered($mysqli)
{
    if (!hsMultiEnsureTable($mysqli)) {
        return false;
    }
    $ownerId = hsMultiCurrentSessionOwnerId();
    if ($ownerId <= 0 || $ownerId === 2) {
        return false;
    }

    $payload = hsMultiReadCookie();
    $activeSelector = isset($_SESSION['hs_multi_active_selector']) && is_string($_SESSION['hs_multi_active_selector']) ? $_SESSION['hs_multi_active_selector'] : '';
    if ($activeSelector !== '') {
        if (isset($payload['accounts'][$activeSelector])) {
            $validation = hsMultiValidateToken($mysqli, $activeSelector, $payload['accounts'][$activeSelector]);
            if ($validation && (int) $validation['identity']['owner_id'] === $ownerId) {
                $payload['active'] = $activeSelector;
                hsMultiWriteCookie($payload);
                return true;
            }
            // Token byl odvolan, expiroval nebo se zmenilo heslo. Aktualni PHP session
            // muze dobehnout, ale nesmi si bez noveho zadani hesla vyrobit novy remember token.
            unset($payload['accounts'][$activeSelector]);
            if ($payload['active'] === $activeSelector) {
                $payload['active'] = '';
            }
            hsMultiWriteCookie($payload);
        }
        $_SESSION['hs_multi_active_selector'] = '';
        return false;
    }

    $identity = hsMultiCurrentIdentity($mysqli);
    if (!$identity) {
        return false;
    }

    foreach ($payload['accounts'] as $selector => $validator) {
        $validation = hsMultiValidateToken($mysqli, $selector, $validator);
        if ($validation
            && (int) $validation['identity']['owner_id'] === $ownerId
            && (string) $validation['identity']['type'] === (string) $identity['type']
            && (int) $validation['identity']['principal_id'] === (int) $identity['principal_id']) {
            $_SESSION['hs_multi_active_selector'] = $selector;
            $payload['active'] = $selector;
            hsMultiWriteCookie($payload);
            return true;
        }
    }
    $lastBranchId = isset($_SESSION['pobocka_id']) && !is_array($_SESSION['pobocka_id']) ? (int) $_SESSION['pobocka_id'] : 0;
    $currentPage = (isset($_GET['strana']) && !is_array($_GET['strana'])) ? hsMultiNormalizeLastPage((string) $_GET['strana']) : '';
    $issued = hsMultiIssueToken($mysqli, $identity, $lastBranchId, $currentPage);
    if (!$issued) {
        return false;
    }
    $_SESSION['hs_multi_active_selector'] = $issued['selector'];
    hsMultiCsrfToken();
    return true;
}

function hsMultiUpdateLastBranch($mysqli, $branchId)
{
    $selector = isset($_SESSION['hs_multi_active_selector']) && is_string($_SESSION['hs_multi_active_selector']) ? $_SESSION['hs_multi_active_selector'] : '';
    $ownerId = hsMultiCurrentSessionOwnerId();
    $branchId = (int) $branchId;
    if ($selector === '' || $ownerId <= 0 || $branchId <= 0 || !hsMultiEnsureTable($mysqli)) {
        return;
    }
    $stmt = $mysqli->prepare("UPDATE `k_klient_remember_tokens` SET `last_branch_id`=?, `last_used_at`=NOW() WHERE `selector`=? AND `owner_k_id`=? AND `revoked_at` IS NULL");
    if ($stmt) {
        $stmt->bind_param('isi', $branchId, $selector, $ownerId);
        @$stmt->execute();
        $stmt->close();
    }
}

function hsMultiUpdateLastPage($mysqli, $page)
{
    $selector = isset($_SESSION['hs_multi_active_selector']) && is_string($_SESSION['hs_multi_active_selector']) ? $_SESSION['hs_multi_active_selector'] : '';
    $ownerId = hsMultiCurrentSessionOwnerId();
    $page = hsMultiNormalizeLastPage($page);
    if ($selector === '' || $ownerId <= 0 || !hsMultiEnsureTable($mysqli)) {
        return;
    }
    $stmt = $mysqli->prepare("UPDATE `k_klient_remember_tokens` SET `last_page`=?, `last_used_at`=NOW() WHERE `selector`=? AND `owner_k_id`=? AND `revoked_at` IS NULL");
    if ($stmt) {
        $stmt->bind_param('ssi', $page, $selector, $ownerId);
        @$stmt->execute();
        $stmt->close();
    }
}

function hsMultiBranchAllowed($mysqli, $identity, $branchId)
{
    $branchId = (int) $branchId;
    if ($branchId <= 0 || !$identity) {
        return false;
    }

    $ownerId = (int) $identity['owner_id'];
    $stmt = $mysqli->prepare("SELECT 1 FROM `sw_email_pobocka` WHERE `sw_id`=? AND `k_id`=? LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ii', $branchId, $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ownerHasBranch = $result && $result->num_rows > 0;
    $stmt->close();
    if (!$ownerHasBranch) {
        return false;
    }

    if ($identity['type'] === 'admin') {
        return true;
    }
    $principalId = (int) $identity['principal_id'];
    if ($identity['type'] === 'staff') {
        $stmt = $mysqli->prepare("SELECT 1 FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_je_obsluha`=1 AND `k_poduzivatele_id_obsluha`=? AND `pobocka_id`=? AND `prava_uzivatel_pravo`=1 LIMIT 1");
    } else {
        $stmt = $mysqli->prepare("SELECT 1 FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id`=? AND `pobocka_id`=? AND `prava_uzivatel_pravo`=1 LIMIT 1");
    }
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ii', $principalId, $branchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $allowed = $result && $result->num_rows > 0;
    $stmt->close();
    return $allowed;
}

function hsMultiConsumeRestoreState($mysqli)
{
    $branchId = isset($_SESSION['hs_restore_branch_id']) && !is_array($_SESSION['hs_restore_branch_id']) ? (int) $_SESSION['hs_restore_branch_id'] : 0;
    $page = isset($_SESSION['hs_restore_page']) && is_string($_SESSION['hs_restore_page']) ? hsMultiNormalizeLastPage($_SESSION['hs_restore_page']) : '';
    $_SESSION['hs_restore_branch_id'] = 0;
    $_SESSION['hs_restore_page'] = '';
    if ($branchId <= 0 && $page === '') {
        return array('branch_id' => 0, 'page' => '');
    }

    $payload = hsMultiReadCookie();
    $selector = isset($_SESSION['hs_multi_active_selector']) && is_string($_SESSION['hs_multi_active_selector']) ? $_SESSION['hs_multi_active_selector'] : '';
    if ($selector === '' || !isset($payload['accounts'][$selector])) {
        return array('branch_id' => 0, 'page' => '');
    }
    $validation = hsMultiValidateToken($mysqli, $selector, $payload['accounts'][$selector]);
    if (!$validation) {
        return array('branch_id' => 0, 'page' => '');
    }
    if ($branchId > 0 && !hsMultiBranchAllowed($mysqli, $validation['identity'], $branchId)) {
        $branchId = 0;
    }
    return array('branch_id' => $branchId, 'page' => $page);
}

function hsMultiConsumeRestoreBranch($mysqli)
{
    $state = hsMultiConsumeRestoreState($mysqli);
    return (int) $state['branch_id'];
}

function hsMultiCompanyLabel($mysqli, $identity, $lastBranchId)
{
    $branchName = '';
    $branchId = (int) $lastBranchId;
    if ($branchId > 0 && hsMultiBranchAllowed($mysqli, $identity, $branchId)) {
        $stmt = $mysqli->prepare("SELECT `sw_jmeno_pobocky` FROM `sw_info` WHERE `sw_id`=? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $branchId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($row) {
                $branchName = trim((string) $row['sw_jmeno_pobocky']);
            }
        }
    }

    if ($branchName === '' && $identity['type'] === 'staff') {
        $principalId = (int) $identity['principal_id'];
        $stmt = $mysqli->prepare("SELECT `sw_info`.`sw_jmeno_pobocky` FROM `k_poduzivatele_prava` JOIN `sw_info` ON `sw_info`.`sw_id`=`k_poduzivatele_prava`.`pobocka_id` WHERE `k_poduzivatele_prava`.`k_poduzivatele_je_obsluha`=1 AND `k_poduzivatele_prava`.`k_poduzivatele_id_obsluha`=? AND `k_poduzivatele_prava`.`prava_uzivatel_pravo`=1 ORDER BY `sw_info`.`sw_id` ASC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $principalId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($row) {
                $branchName = trim((string) $row['sw_jmeno_pobocky']);
            }
        }
        if ($branchName === '') {
            $staffBranchId = isset($identity['session']['k_poduzivatele_obsluha_sw_id']) ? (int) $identity['session']['k_poduzivatele_obsluha_sw_id'] : 0;
            if ($staffBranchId > 0) {
                $stmt = $mysqli->prepare("SELECT `sw_jmeno_pobocky` FROM `sw_info` WHERE `sw_id`=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('i', $staffBranchId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result ? $result->fetch_assoc() : null;
                    $stmt->close();
                    if ($row) {
                        $branchName = trim((string) $row['sw_jmeno_pobocky']);
                    }
                }
            }
        }
    }

    if ($branchName === '' && $identity['type'] === 'manager') {
        $principalId = (int) $identity['principal_id'];
        $stmt = $mysqli->prepare("SELECT `sw_info`.`sw_jmeno_pobocky` FROM `k_poduzivatele_prava` JOIN `sw_info` ON `sw_info`.`sw_id`=`k_poduzivatele_prava`.`pobocka_id` WHERE `k_poduzivatele_prava`.`k_poduzivatele_id`=? AND `k_poduzivatele_prava`.`prava_uzivatel_pravo`=1 ORDER BY `sw_info`.`sw_id` ASC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $principalId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($row) {
                $branchName = trim((string) $row['sw_jmeno_pobocky']);
            }
        }
    }

    if ($branchName === '' && $identity['type'] === 'admin') {
        $ownerId = (int) $identity['owner_id'];
        $stmt = $mysqli->prepare("SELECT `sw_info`.`sw_jmeno_pobocky` FROM `sw_email_pobocka` JOIN `sw_info` ON `sw_info`.`sw_id`=`sw_email_pobocka`.`sw_id` WHERE `sw_email_pobocka`.`k_id`=? ORDER BY `sw_info`.`sw_id` ASC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $ownerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();
            if ($row) {
                $branchName = trim((string) $row['sw_jmeno_pobocky']);
            }
        }
    }

    return $branchName !== '' ? $branchName : (string) $identity['display_name'];
}

function hsMultiListAccounts($mysqli, $requiredSoft = null)
{
    $items = array();
    if (!hsMultiEnsureTable($mysqli)) {
        return $items;
    }

    $payload = hsMultiReadCookie();
    $changed = false;
    foreach ($payload['accounts'] as $selector => $validator) {
        $validation = hsMultiValidateToken($mysqli, $selector, $validator);
        if (!$validation) {
            unset($payload['accounts'][$selector]);
            if ($payload['active'] === $selector) {
                $payload['active'] = '';
            }
            $changed = true;
            continue;
        }
        $identity = $validation['identity'];
        if ($requiredSoft !== null && $requiredSoft !== '' && strcasecmp((string) $requiredSoft, (string) $identity['soft']) !== 0) {
            continue;
        }
        $lastBranchId = (int) $validation['row']['last_branch_id'];
        $items[] = array(
            'selector' => $selector,
            'label' => hsMultiCompanyLabel($mysqli, $identity, $lastBranchId),
            'login_label' => (string) $identity['login_label'],
            'display_name' => (string) $identity['display_name'],
            'type' => (string) $identity['type'],
            'soft' => (string) $identity['soft'],
            'last_branch_id' => $lastBranchId,
            'last_page' => isset($validation['row']['last_page']) ? hsMultiNormalizeLastPage((string) $validation['row']['last_page']) : '',
            'active' => isset($_SESSION['hs_multi_active_selector']) && $_SESSION['hs_multi_active_selector'] === $selector
        );
    }

    if ($changed) {
        hsMultiWriteCookie($payload);
    }
    return $items;
}

function hsMultiFindAccountBySelector($mysqli, $selector)
{
    $payload = hsMultiReadCookie();
    if (!isset($payload['accounts'][$selector])) {
        return null;
    }
    return hsMultiValidateToken($mysqli, $selector, $payload['accounts'][$selector]);
}

function hsMultiRemoveCookieAccount($selector)
{
    $payload = hsMultiReadCookie();
    if (isset($payload['accounts'][$selector])) {
        unset($payload['accounts'][$selector]);
    }
    if ($payload['active'] === $selector) {
        $payload['active'] = '';
    }
    hsMultiWriteCookie($payload);
}

function hsMultiSetFlash($type, $message)
{
    $_SESSION['hs_multi_flash'] = array('type' => (string) $type, 'message' => (string) $message);
}

function hsMultiTakeFlash()
{
    $flash = isset($_SESSION['hs_multi_flash']) && is_array($_SESSION['hs_multi_flash']) ? $_SESSION['hs_multi_flash'] : null;
    unset($_SESSION['hs_multi_flash']);
    return $flash;
}

function hsMultiClearSessionKeepLanguage()
{
    $language = isset($_SESSION['languageCombo']) && is_string($_SESSION['languageCombo']) ? $_SESSION['languageCombo'] : 'cs';
    $_SESSION = array();
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        @session_regenerate_id(true);
    }
    $_SESSION['languageCombo'] = $language;
    $_SESSION['hs_multi_csrf'] = bin2hex(random_bytes(24));
}
