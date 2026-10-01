<?php
/* HairSoft Klient V191 - verejny vstup pro prepinani firem. */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/company-switch.php';
chdir(__DIR__);
if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) {
        define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    }
    require $hsClientUiEntry;
    exit;
}
http_response_code(500);
exit('HairSoft Klient: modul přepínání firem není dostupný.');
