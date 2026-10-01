<?php
/* HairSoft Klient V210 - verejny vstup pro rucni dokonceni kopie zakaznika. */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/customer-copy-sync.php';
chdir(__DIR__);
if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) {
        define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    }
    require $hsClientUiEntry;
    exit;
}
http_response_code(500);
exit('HairSoft Klient: modul ruční synchronizace kopie zákazníka není dostupný.');
