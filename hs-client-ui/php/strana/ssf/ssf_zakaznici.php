<?php
// V168: čistý moderní server-side endpoint pro DataTables zákazníků.
// Záměrně neobsahuje legacy helper funkce ani obecný SpolecneFunkce.php,
// aby JSON nemohl shodit konflikt názvů nebo warning ze starého kódu.
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../_programs_data.php';
hsSsfRequireRight('Zakaznici', true);
header('Content-Type: application/json; charset=UTF-8');

function hsCustomerJsonResponse($draw, $total, $filtered, $rows, $programMeta = null)
{
    $payload = array(
        // DataTables 1.10+
        'draw' => (int) $draw,
        'recordsTotal' => (int) $total,
        'recordsFiltered' => (int) $filtered,
        'data' => $rows,
        // Legacy kompatibilita
        'sEcho' => (int) $draw,
        'iTotalRecords' => (int) $total,
        'iTotalDisplayRecords' => (int) $filtered,
        'aaData' => $rows,
    );

    if (is_array($programMeta)) {
        $payload['hsProgramMeta'] = $programMeta;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function hsCustomerFail($draw, $message, $throwable = null)
{
    if ($throwable instanceof Throwable) {
        error_log('HairSoft SSF zákazníci: ' . $throwable->getMessage());
    } else {
        error_log('HairSoft SSF zákazníci: ' . $message);
    }

    http_response_code(500);
    echo json_encode(array(
        'draw' => (int) $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => array(),
        'sEcho' => (int) $draw,
        'iTotalRecords' => 0,
        'iTotalDisplayRecords' => 0,
        'aaData' => array(),
        'error' => $message,
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function hsCustomerScalarGet($key, $default = '')
{
    if (!isset($_GET[$key]) || is_array($_GET[$key])) {
        return $default;
    }
    return $_GET[$key];
}

function hsCustomerText($value)
{
    if ($value === null) {
        return '';
    }

    $value = (string) $value;
    $value = str_replace('_e_', '&', $value);
    $value = str_replace('_34_', chr(39), $value);
    $value = str_replace('_38_', chr(38), $value);
    $value = str_replace('_39_', chr(39), $value);
    $value = str_replace('_64_', chr(64), $value);
    $value = str_replace('_47_', chr(47), $value);
    $value = str_replace('_92_', chr(92), $value);
    $value = str_replace('_43_', chr(43), $value);

    if (!preg_match('//u', $value) && function_exists('iconv')) {
        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);
        if (is_string($converted)) {
            $value = $converted;
        }
    }

    return $value;
}

function hsCustomerDate($value)
{
    if ($value === null || $value === '' || $value === '0000-00-00 00:00:00') {
        return '';
    }

    $timestamp = strtotime((string) $value);
    if ($timestamp === false || $timestamp <= 0) {
        return '';
    }

    return date('d.m.Y H:i', $timestamp);
}

$draw = (int) hsCustomerScalarGet('draw', 0);
$start = max(0, (int) hsCustomerScalarGet('start', 0));
$length = (int) hsCustomerScalarGet('length', 20);
if ($length < 1) {
    $length = 20;
}
$length = min($length, 100000);

$orderColumn = 0;
$orderDir = 'ASC';
if (isset($_GET['order'][0]) && is_array($_GET['order'][0])) {
    if (isset($_GET['order'][0]['column']) && !is_array($_GET['order'][0]['column'])) {
        $orderColumn = (int) $_GET['order'][0]['column'];
    }
    if (isset($_GET['order'][0]['dir']) && !is_array($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'desc') {
        $orderDir = 'DESC';
    }
}

$requestedProgramId = max(0, (int) hsCustomerScalarGet('hs_program_id', 0));

$search = '';
if (isset($_GET['search']['value']) && !is_array($_GET['search']['value'])) {
    $search = trim((string) $_GET['search']['value']);
}

$_SESSION['ZakazniciRazeniFiltrHledani'] = $search;
if (!isset($_SESSION['Blacklist']) || is_array($_SESSION['Blacklist'])) {
    $_SESSION['Blacklist'] = '0';
}
if (!isset($_SESSION['k_poduzivatele_skryt_citliva_data']) || is_array($_SESSION['k_poduzivatele_skryt_citliva_data'])) {
    $_SESSION['k_poduzivatele_skryt_citliva_data'] = '0';
}

$swId = hsSsfPobockaId();
if ($swId <= 0) {
    hsCustomerFail($draw, 'Není vybrána pobočka.');
}

// V224: PROGRAMS jsou zrcadlené HSBridge tabulky pro konkrétní PC + skupinu.
$groupId = hsProgramsGroupId($mysqli, (int) $swId);
$programMeta = hsProgramsActiveMeta($mysqli, (int) $swId, (int) $groupId, $requestedProgramId);
$selectedProgramId = isset($programMeta['selectedProgramId']) ? (int) $programMeta['selectedProgramId'] : 0;

$orderMap = array(
    1 => 'l.lidi_hs_surname',
    2 => 'l.lidi_hs_name',
    3 => 'l.lidi_hs_email',
    4 => 'l.lidi_hs_cell',
    5 => 's.stat_PocetNavstev',
    6 => 's.stat_ZruseneObjednavky',
    7 => 's.stat_PristiNavsteva',
    8 => 's.stat_PosledniNavsteva',
    10 => 'l.lidi_hs_loyalityPoints',
    11 => 'l.lidi_blacklist',
);
$orderSql = isset($orderMap[$orderColumn]) ? $orderMap[$orderColumn] . ' ' . $orderDir : 'l.lidi_hs_surname ASC, l.lidi_hs_name ASC';

$baseWhere = array('l.lidi_aktivni = 1', 'l.lidi_sw_id = ' . (int) $swId);
if ((string) $_SESSION['Blacklist'] === '1') {
    $baseWhere[] = 'l.lidi_blacklist = 1';
}
$baseWhereSql = implode(' AND ', $baseWhere);

$filteredWhereSql = $baseWhereSql;
if ($search !== '') {
    $escaped = $mysqli->real_escape_string($search);
    $like = "'%" . $escaped . "%'";
    $searchClauses = array(
        'l.lidi_guid LIKE ' . $like,
        'l.lidi_hs_surname LIKE ' . $like,
        'l.lidi_hs_name LIKE ' . $like,
        'l.lidi_hs_email LIKE ' . $like,
        'l.lidi_hs_cell LIKE ' . $like,
        'CAST(COALESCE(s.stat_PocetNavstev, 0) AS CHAR) LIKE ' . $like,
        'CAST(COALESCE(s.stat_ZruseneObjednavky, 0) AS CHAR) LIKE ' . $like,
        "DATE_FORMAT(s.stat_PristiNavsteva, '%Y-%m-%d %H:%i') LIKE " . $like,
        "DATE_FORMAT(s.stat_PosledniNavsteva, '%Y-%m-%d %H:%i') LIKE " . $like,
        'CAST(COALESCE(l.lidi_hs_loyalityPoints, 0) AS CHAR) LIKE ' . $like,
    );
    $filteredWhereSql .= ' AND (' . implode(' OR ', $searchClauses) . ')';
}

$fromSql = ' FROM klient_lidi l LEFT JOIN klient_lidi_statistika s ON l.lidi_guid = s.stat_klient_GUID ';

try {
    $totalSql = 'SELECT COUNT(*) AS cnt' . $fromSql . ' WHERE ' . $baseWhereSql;
    $totalResult = $mysqli->query($totalSql);
    if (!$totalResult) {
        throw new RuntimeException($mysqli->error);
    }
    $totalRow = $totalResult->fetch_assoc();
    $recordsTotal = isset($totalRow['cnt']) ? (int) $totalRow['cnt'] : 0;

    if ($search === '') {
        $recordsFiltered = $recordsTotal;
    } else {
        $filteredCountSql = 'SELECT COUNT(*) AS cnt' . $fromSql . ' WHERE ' . $filteredWhereSql;
        $filteredCountResult = $mysqli->query($filteredCountSql);
        if (!$filteredCountResult) {
            throw new RuntimeException($mysqli->error);
        }
        $filteredCountRow = $filteredCountResult->fetch_assoc();
        $recordsFiltered = isset($filteredCountRow['cnt']) ? (int) $filteredCountRow['cnt'] : 0;
    }

    $dataSql = 'SELECT '
        . 'l.lidi_guid, l.lidi_hs_id, l.lidi_hs_surname, l.lidi_hs_name, l.lidi_hs_email, l.lidi_hs_cell, '
        . 's.stat_PocetNavstev, s.stat_ZruseneObjednavky, s.stat_PristiNavsteva, s.stat_PosledniNavsteva, '
        . 'l.lidi_hs_loyalityPoints, l.lidi_blacklist '
        . $fromSql
        . ' WHERE ' . $filteredWhereSql
        . ' ORDER BY ' . $orderSql
        . ' LIMIT ' . (int) $start . ', ' . (int) $length;

    $result = $mysqli->query($dataSql);
    if (!$result) {
        throw new RuntimeException($mysqli->error);
    }

    $sourceRows = array();
    $guids = array();
    $customerHsIds = array();
    while ($source = $result->fetch_assoc()) {
        $sourceRows[] = $source;
        if (isset($source['lidi_guid']) && $source['lidi_guid'] !== '') {
            $guids[] = (string) $source['lidi_guid'];
        }
        if (isset($source['lidi_hs_id']) && (int) $source['lidi_hs_id'] > 0) {
            $customerHsIds[] = (int) $source['lidi_hs_id'];
        }
    }

    $photos = array();
    if (!empty($guids)) {
        $escapedGuids = array();
        foreach (array_unique($guids) as $guid) {
            $escapedGuids[] = "'" . $mysqli->real_escape_string($guid) . "'";
        }
        $photoSql = 'SELECT obrazek_osoba_GUID, obrazek_jmeno_GUID FROM klient_lidi_obrazky '
            . 'WHERE obrazek_smazan = 0 AND obrazek_profilovka = 1 '
            . 'AND obrazek_osoba_GUID IN (' . implode(',', $escapedGuids) . ')';
        $photoResult = $mysqli->query($photoSql);
        if ($photoResult) {
            while ($photo = $photoResult->fetch_assoc()) {
                $guid = isset($photo['obrazek_osoba_GUID']) ? (string) $photo['obrazek_osoba_GUID'] : '';
                if ($guid !== '' && !isset($photos[$guid])) {
                    $photos[$guid] = isset($photo['obrazek_jmeno_GUID']) ? (string) $photo['obrazek_jmeno_GUID'] : '';
                }
            }
        }
    }

    $programBalances = $selectedProgramId > 0
        ? hsProgramsBalancesForCustomers($mysqli, (int) $swId, (int) $groupId, $selectedProgramId, $customerHsIds)
        : array();

    $hideSensitive = (string) $_SESSION['k_poduzivatele_skryt_citliva_data'] === '1';
    $rows = array();
    foreach ($sourceRows as $source) {
        $guid = isset($source['lidi_guid']) ? (string) $source['lidi_guid'] : '';
        $photo = isset($photos[$guid]) && $photos[$guid] !== '' ? $photos[$guid] : 'NIC';
        $email = $hideSensitive ? 'Anonymizováno' : hsCustomerText(isset($source['lidi_hs_email']) ? $source['lidi_hs_email'] : '');
        $cell = $hideSensitive ? 'Anonymizováno' : hsCustomerText(isset($source['lidi_hs_cell']) ? $source['lidi_hs_cell'] : '');

        $customerHsId = isset($source['lidi_hs_id']) ? (int) $source['lidi_hs_id'] : 0;
        $programRemaining = ($selectedProgramId > 0 && $customerHsId > 0 && isset($programBalances[$customerHsId]))
            ? (int) $programBalances[$customerHsId]
            : '—';

        $rows[] = array(
            $guid . '**+-+**' . $photo,
            hsCustomerText(isset($source['lidi_hs_surname']) ? $source['lidi_hs_surname'] : ''),
            hsCustomerText(isset($source['lidi_hs_name']) ? $source['lidi_hs_name'] : ''),
            $email,
            $cell,
            isset($source['stat_PocetNavstev']) && $source['stat_PocetNavstev'] !== null ? $source['stat_PocetNavstev'] : 0,
            isset($source['stat_ZruseneObjednavky']) && $source['stat_ZruseneObjednavky'] !== null ? $source['stat_ZruseneObjednavky'] : 0,
            hsCustomerDate(isset($source['stat_PristiNavsteva']) ? $source['stat_PristiNavsteva'] : null),
            hsCustomerDate(isset($source['stat_PosledniNavsteva']) ? $source['stat_PosledniNavsteva'] : null),
            $programRemaining,
            isset($source['lidi_hs_loyalityPoints']) && $source['lidi_hs_loyalityPoints'] !== null ? $source['lidi_hs_loyalityPoints'] : 0,
            isset($source['lidi_blacklist']) && $source['lidi_blacklist'] !== null ? $source['lidi_blacklist'] : 0,
            '<CENTER><a onClick="if(!confirm(\'Opravdu chcete SMAZAT tuto osobu? Osoba bude trvale vymazána ze systému!\')){return false;}" href="index.php?strana=Zakaznici&SmazatZakaznikaZPC=1&Uzivatel_GET_GUID=' . htmlspecialchars($guid, ENT_QUOTES, 'UTF-8') . '" title="Smazat zákazníka"><i class="icon-trash"></i></a></CENTER>',
        );
    }

    hsCustomerJsonResponse($draw, $recordsTotal, $recordsFiltered, $rows, $programMeta);
} catch (Throwable $error) {
    hsCustomerFail($draw, 'Data zákazníků se nepodařilo načíst.', $error);
}