<?php
/* HairSoft Klient V241 - public PROGRAMS photo upload/count entry. */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/program-photo.php';
chdir(__DIR__);
if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    require $hsClientUiEntry;
    exit;
}
http_response_code(500);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(array('ok'=>false,'error'=>'HairSoft Klient: modul fotografií programu není dostupný.'));