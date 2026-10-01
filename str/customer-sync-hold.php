<?php
/* HairSoft Klient V202 - verejny vstup pro heartbeat dvoufazove synchronizace. */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/customer-sync-hold.php';
chdir(__DIR__);
if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) {
        define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    }
    require $hsClientUiEntry;
    exit;
}
http_response_code(500);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(array('ok' => false, 'released' => 0));
