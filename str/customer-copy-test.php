<?php
/* HairSoft Klient V195 - verejny vstup pro izolovanou kopii zakaznika mezi firmami. */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/customer-copy-test.php';
chdir(__DIR__);
if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) {
        define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    }
    require $hsClientUiEntry;
    exit;
}
http_response_code(500);
exit('HairSoft Klient: modul kopie zákazníka není dostupný.');
