<?php
declare(strict_types=1);
require_once __DIR__.'/app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['ok'=>true,'service'=>'Bonfero HairSoft Bridge Directory','protocol'=>2],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
