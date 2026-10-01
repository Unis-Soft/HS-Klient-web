<?php
/*
 * HairSoft Klient UI V41
 *
 * Veřejný vstup pouze volí samostatnou moderní nebo původní větev.
 * Funkční PHP obou větví se zde nemíchá.
 */
$hsClientUiRoot = dirname(__DIR__);
$hsClientUiEntry = $hsClientUiRoot . '/hs-client-ui/php/index.php';
$hsClientUiFallback = __DIR__ . '/index_TMK.php';

chdir(__DIR__);

if (is_file($hsClientUiEntry)) {
    if (!defined('HS_CLIENT_UI_ROOT')) {
        define('HS_CLIENT_UI_ROOT', $hsClientUiRoot);
    }

    $hsClientUiBufferLevel = ob_get_level();
    require $hsClientUiEntry;

    while (ob_get_level() > $hsClientUiBufferLevel) {
        ob_end_flush();
    }
    exit;
}

if (is_file($hsClientUiFallback)) {
    require $hsClientUiFallback;
    exit;
}

http_response_code(500);
exit('HairSoft Klient: nebyla nalezena moderní ani původní větev.');
