<?php
/* HairSoft Klient UI V41 – přepínač přihlášení obsluhy. */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/loginObsluha.php';
$hsClientUiFallback = __DIR__ . '/loginObsluha_TMK.php';

chdir(__DIR__);

if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) {
        define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    }
    require $hsClientUiEntry;
    exit;
}

if (is_file($hsClientUiFallback)) {
    require $hsClientUiFallback;
    exit;
}

http_response_code(500);
exit('HairSoft Klient: nebyla nalezena přihlašovací větev obsluhy.');
