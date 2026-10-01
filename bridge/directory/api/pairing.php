<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$route=trim((string)($_GET['route'] ?? ''),'/');
$method=$_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($route==='health' && $method==='GET') { db()->query('SELECT 1')->fetchColumn(); json_response(['ok'=>true,'service'=>'Bonfero HairSoft Bridge Directory','protocol'=>2,'storage'=>'sqlite']); }

    if ($route==='register' && $method==='POST') {
        rate_limit('register',60,300);
        $in=request_json();
        $bridgeId=clean($in['bridgeId'] ?? '',80); $token=clean($in['token'] ?? '',200); $code=clean($in['pairingCode'] ?? '',12);
        $version=clean($in['version'] ?? '',40); $machine=clean($in['machineName'] ?? '',120);
        $rc=is_array($in['rcIdentity'] ?? null)?$in['rcIdentity']:[];
        $rc3=clean($rc['setting3'] ?? '',80); $rc10=clean($rc['setting10'] ?? '',80);
        if (!preg_match('/^hsb_[a-f0-9]{16,64}$/',$bridgeId)) throw new InvalidArgumentException('Neplatné Bridge ID.');
        if (!preg_match('/^[a-f0-9]{64}$/i',$token)) throw new InvalidArgumentException('Neplatný Bridge token.');
        if (!preg_match('/^\d{6}$/',$code)) throw new InvalidArgumentException('Neplatný párovací kód.');
        $pdo=db(); $now=now_iso(); $tokenHash=secret_hash($token); $codeHash=secret_hash('pair:'.$code); $expires=gmdate('c',time()+15*60);
        $st=$pdo->prepare('SELECT * FROM bridges WHERE bridge_id=? LIMIT 1'); $st->execute([$bridgeId]); $row=$st->fetch();
        if ($row) {
            if (!hash_equals((string)$row['token_hash'],$tokenHash)) json_response(['error'=>'Bridge ID již existuje s jiným tokenem.'],409);
            $stored3=(string)($row['rc_setting3'] ?? ''); $stored10=(string)($row['rc_setting10'] ?? '');
            if ($rc3!=='' && $stored3!=='' && !hash_equals($stored3,$rc3)) json_response(['error'=>'Identita PC Setting3 se proti první registraci změnila.'],409);
            if ($rc10!=='' && $stored10!=='' && !hash_equals($stored10,$rc10)) json_response(['error'=>'Identita PC Setting10 se proti první registraci změnila.'],409);
            if ($stored3==='' && $rc3!=='') $stored3=$rc3;
            if ($stored10==='' && $rc10!=='') $stored10=$rc10;
            if ((string)$row['state']==='paired') {
                $pdo->prepare('UPDATE bridges SET machine_name=?,version=?,rc_setting3=?,rc_setting10=?,last_seen_at=?,updated_at=? WHERE bridge_id=?')
                    ->execute([$machine,$version,$stored3,$stored10,$now,$now,$bridgeId]);
                $st->execute([$bridgeId]); $row=$st->fetch();
                json_response(array_merge(['registered'=>true],bridge_payload($row ?: [])));
            }
            $pdo->prepare('UPDATE bridges SET pairing_code_hash=?,pairing_expires_at=?,machine_name=?,version=?,rc_setting3=?,rc_setting10=?,state="pending",last_seen_at=?,updated_at=? WHERE bridge_id=?')
                ->execute([$codeHash,$expires,$machine,$version,$stored3,$stored10,$now,$now,$bridgeId]);
        } else {
            $pdo->prepare('INSERT INTO bridges(bridge_id,token_hash,pairing_code_hash,pairing_expires_at,machine_name,version,rc_setting3,rc_setting10,state,last_seen_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?, "pending",?,?,?)')
                ->execute([$bridgeId,$tokenHash,$codeHash,$expires,$machine,$version,$rc3,$rc10,$now,$now,$now]);
        }
        json_response(['registered'=>true,'paired'=>false,'expiresAt'=>$expires,'directoryProtocol'=>2],201);
    }

    if ($route==='status' && $method==='GET') {
        $row=bridge_auth();
        json_response(bridge_payload($row));
    }

    // Minimal authenticated identity endpoint for HS Klient and other
    // server-side integrations. It intentionally never returns siteToken.
    if ($route==='identity' && $method==='GET') {
        $row=bridge_auth();
        json_response([
            'ok'=>true,
            'bridgeId'=>(string)$row['bridge_id'],
            'machineName'=>(string)$row['machine_name'],
            'version'=>(string)$row['version'],
            'rcIdentity'=>[
                'setting3'=>(string)($row['rc_setting3'] ?? ''),
                'setting10'=>(string)($row['rc_setting10'] ?? ''),
            ],
            'directoryProtocol'=>2,
        ]);
    }

    if ($route==='claim' && $method==='POST') {
        rate_limit('claim',12,300);
        $siteId=require_site_auth(); $in=request_json();
        $code=clean($in['code'] ?? '',12); $base=normalize_site_url(clean($in['siteBaseUrl'] ?? '',500));
        $siteName=clean($in['siteName'] ?? '',160); $instance=clean($in['siteInstanceId'] ?? '',160);
        if (!preg_match('/^\d{6}$/',$code)) throw new InvalidArgumentException('Zadejte šestimístný párovací kód.');
        if ($instance==='') throw new InvalidArgumentException('Chybí ID webu/provozovny.');
        $now=now_iso(); $hash=secret_hash('pair:'.$code); $pdo=db();
        $st=$pdo->prepare('SELECT * FROM bridges WHERE state="pending" AND pairing_code_hash=? AND pairing_expires_at>=? ORDER BY created_at DESC LIMIT 1'); $st->execute([$hash,$now]); $bridge=$st->fetch();
        if (!$bridge) json_response(['error'=>'Párovací kód není platný nebo již vypršel.'],404);
        $siteToken=bin2hex(random_bytes(32)); $pairedAt=$now;
        $pdo->prepare('UPDATE bridges SET state="paired",site_id=?,site_instance_id=?,target_base_url=?,target_name=?,site_token_enc=?,paired_at=?,pairing_code_hash="",pairing_expires_at=NULL,updated_at=? WHERE bridge_id=?')
            ->execute([$siteId,$instance,$base,$siteName,encrypt_secret($siteToken),$pairedAt,$now,$bridge['bridge_id']]);
        json_response([
            'paired'=>true,'bridgeId'=>(string)$bridge['bridge_id'],'siteToken'=>$siteToken,
            'machineName'=>(string)$bridge['machine_name'],'version'=>(string)$bridge['version'],'pairedAt'=>$pairedAt,
            'siteBaseUrl'=>$base,'siteInstanceId'=>$instance,'directoryProtocol'=>2,
        ]);
    }

    if ($route==='bind' && $method==='POST') {
        $siteId=require_site_auth(); $in=request_json();
        $bridgeId=clean($in['bridgeId'] ?? '',80); $instance=clean($in['siteInstanceId'] ?? '',160); $base=normalize_site_url(clean($in['siteBaseUrl'] ?? '',500)); $siteName=clean($in['siteName'] ?? '',160);
        $st=db()->prepare('SELECT * FROM bridges WHERE bridge_id=? LIMIT 1'); $st->execute([$bridgeId]); $row=$st->fetch();
        if (!$row || (string)$row['state']!=='paired') json_response(['error'=>'Bridge není spárovaný.'],404);
        if (!hash_equals((string)$row['site_id'],$siteId) || !hash_equals((string)$row['site_instance_id'],$instance)) json_response(['error'=>'Bridge patří jinému webu/provozovně.'],409);
        db()->prepare('UPDATE bridges SET target_base_url=?,target_name=?,updated_at=? WHERE bridge_id=?')->execute([$base,$siteName,now_iso(),$bridgeId]);
        json_response(['ok'=>true,'siteBaseUrl'=>$base]);
    }

    if ($route==='release' && $method==='POST') {
        $siteId=require_site_auth(); $in=request_json();
        $bridgeId=clean($in['bridgeId'] ?? '',80); $instance=clean($in['siteInstanceId'] ?? '',160);
        $st=db()->prepare('SELECT * FROM bridges WHERE bridge_id=? LIMIT 1'); $st->execute([$bridgeId]); $row=$st->fetch();
        if (!$row) json_response(['ok'=>true,'released'=>false]);
        if ((string)$row['state']==='paired' && (!hash_equals((string)$row['site_id'],$siteId) || !hash_equals((string)$row['site_instance_id'],$instance))) json_response(['error'=>'Bridge patří jinému webu/provozovně.'],409);
        db()->prepare('UPDATE bridges SET state="pending",site_id="",site_instance_id="",target_base_url="",target_name="",site_token_enc="",paired_at=NULL,pairing_code_hash="",pairing_expires_at=NULL,updated_at=? WHERE bridge_id=?')->execute([now_iso(),$bridgeId]);
        json_response(['ok'=>true,'released'=>true]);
    }

    json_response(['error'=>'Požadovaná adresa neexistuje.'],404);
} catch (InvalidArgumentException $e) { json_response(['error'=>$e->getMessage()],400); }
catch (Throwable $e) { error_log('Bridge Directory: '.$e->getMessage()); json_response(['error'=>'Operaci párovacího serveru se nepodařilo dokončit.'],500); }
