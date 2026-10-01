<?php
declare(strict_types=1);

const ROOT_DIR = __DIR__ . '/..';

function load_env(string $path): array {
    if (!is_file($path)) return [];
    $result = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key); $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) $value = substr($value, 1, -1);
        if ($key !== '') $result[$key] = $value;
    }
    return $result;
}

$GLOBALS['APP_ENV_VALUES'] = load_env(ROOT_DIR . '/.env');

function env_value(string $key, string $default = ''): string {
    return (string)($GLOBALS['APP_ENV_VALUES'][$key] ?? $_ENV[$key] ?? getenv($key) ?: $default);
}

function is_https(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (is_https()) header('Strict-Transport-Security: max-age=31536000');
}
security_headers();
ini_set('display_errors', env_value('APP_ENV','production') === 'development' ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('UTC');

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array {
    $body = json_decode((string)file_get_contents('php://input'), true);
    return is_array($body) ? $body : [];
}

function clean(mixed $value, int $max = 300): string {
    $s = trim(is_string($value) ? $value : '');
    return function_exists('mb_substr') ? mb_substr($s,0,$max) : substr($s,0,$max);
}

function now_iso(): string { return gmdate('c'); }

function app_key(): string {
    $raw = env_value('DIRECTORY_APP_KEY');
    if (strlen($raw) < 32 || str_contains($raw,'CHANGE_ME') || str_contains($raw,'SEM_VLOZTE')) {
        throw new RuntimeException('DIRECTORY_APP_KEY není bezpečně nastavený.');
    }
    return hash('sha256',$raw,true);
}

function secret_hash(string $value): string { return hash_hmac('sha256',$value,app_key()); }

function b64url_encode(string $value): string { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
function b64url_decode(string $value): string|false { return base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true); }

function encrypt_secret(string $plain): string {
    $iv = random_bytes(12); $tag='';
    $cipher = openssl_encrypt($plain,'aes-256-gcm',app_key(),OPENSSL_RAW_DATA,$iv,$tag);
    if ($cipher === false) throw new RuntimeException('Šifrování selhalo.');
    return 'v1.'.b64url_encode($iv).'.'.b64url_encode($tag.$cipher);
}

function decrypt_secret(string $encoded): string {
    $parts=explode('.',$encoded);
    if (count($parts)!==3 || $parts[0]!=='v1') return '';
    $iv=b64url_decode($parts[1]); $data=b64url_decode($parts[2]);
    if ($iv===false || $data===false || strlen($data)<17) return '';
    $plain=openssl_decrypt(substr($data,16),'aes-256-gcm',app_key(),OPENSSL_RAW_DATA,$iv,substr($data,0,16));
    return $plain===false?'':$plain;
}

function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $storage=ROOT_DIR.'/storage';
    if (!is_dir($storage) && !mkdir($storage,0770,true) && !is_dir($storage)) throw new RuntimeException('Nelze vytvořit storage.');
    $pdo=new PDO('sqlite:'.$storage.'/pairing.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON; PRAGMA busy_timeout=5000;');
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS bridges (
  bridge_id TEXT PRIMARY KEY,
  token_hash TEXT NOT NULL,
  pairing_code_hash TEXT NOT NULL DEFAULT '',
  pairing_expires_at TEXT,
  machine_name TEXT NOT NULL DEFAULT '',
  version TEXT NOT NULL DEFAULT '',
  rc_setting3 TEXT NOT NULL DEFAULT '',
  rc_setting10 TEXT NOT NULL DEFAULT '',
  state TEXT NOT NULL DEFAULT 'pending',
  site_id TEXT NOT NULL DEFAULT '',
  site_instance_id TEXT NOT NULL DEFAULT '',
  target_base_url TEXT NOT NULL DEFAULT '',
  target_name TEXT NOT NULL DEFAULT '',
  site_token_enc TEXT NOT NULL DEFAULT '',
  paired_at TEXT,
  last_seen_at TEXT,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_bridges_pairing ON bridges(pairing_code_hash,state,pairing_expires_at);
CREATE TABLE IF NOT EXISTS rate_limits (
  bucket TEXT PRIMARY KEY,
  window_start INTEGER NOT NULL,
  attempts INTEGER NOT NULL
);
SQL);
    // Safe in-place migration for existing Directory installations.
    $cols=[];
    foreach ($pdo->query('PRAGMA table_info(bridges)')->fetchAll() ?: [] as $col) {
        $cols[strtolower((string)($col['name'] ?? ''))]=true;
    }
    if (!isset($cols['rc_setting3'])) $pdo->exec("ALTER TABLE bridges ADD COLUMN rc_setting3 TEXT NOT NULL DEFAULT ''");
    if (!isset($cols['rc_setting10'])) $pdo->exec("ALTER TABLE bridges ADD COLUMN rc_setting10 TEXT NOT NULL DEFAULT ''");
    return $pdo;
}

function client_ip(): string {
    $ip=trim((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    return preg_match('/^[0-9a-fA-F:.]{2,64}$/',$ip)?$ip:'unknown';
}

function rate_limit(string $scope, int $limit=20, int $windowSeconds=300): void {
    $bucket=$scope.':'.client_ip(); $now=time(); $pdo=db();
    $st=$pdo->prepare('SELECT window_start,attempts FROM rate_limits WHERE bucket=? LIMIT 1'); $st->execute([$bucket]); $row=$st->fetch();
    if (!$row || $now-(int)$row['window_start'] >= $windowSeconds) {
        $pdo->prepare('INSERT INTO rate_limits(bucket,window_start,attempts) VALUES(?,?,1) ON CONFLICT(bucket) DO UPDATE SET window_start=excluded.window_start,attempts=1')->execute([$bucket,$now]);
        return;
    }
    $attempts=(int)$row['attempts']+1;
    $pdo->prepare('UPDATE rate_limits SET attempts=? WHERE bucket=?')->execute([$attempts,$bucket]);
    if ($attempts>$limit) json_response(['error'=>'Příliš mnoho pokusů. Zkuste to později.'],429);
}

function normalize_site_url(string $raw): string {
    $raw=rtrim(trim($raw),'/');
    if ($raw==='' || !filter_var($raw,FILTER_VALIDATE_URL)) throw new InvalidArgumentException('Neplatná cílová URL webu.');
    $u=parse_url($raw);
    if (!is_array($u) || strtolower((string)($u['scheme']??''))!=='https' || empty($u['host'])) throw new InvalidArgumentException('Cílová URL musí být HTTPS.');
    if (isset($u['query']) || isset($u['fragment']) || isset($u['user']) || isset($u['pass'])) throw new InvalidArgumentException('Cílová URL nesmí obsahovat query, fragment ani přihlašovací údaje.');
    return $raw;
}

function site_keys(): array {
    $json=env_value('DIRECTORY_SITE_KEYS','{}');
    $map=json_decode($json,true);
    return is_array($map)?$map:[];
}

function require_site_auth(): string {
    $siteId=clean($_SERVER['HTTP_X_HS_SITE_ID'] ?? '',80);
    $siteKey=clean($_SERVER['HTTP_X_HS_SITE_KEY'] ?? '',300);
    $keys=site_keys();
    if ($siteId==='' || $siteKey==='' || !isset($keys[$siteId]) || !is_string($keys[$siteId]) || !hash_equals((string)$keys[$siteId],$siteKey)) {
        json_response(['error'=>'Neplatná autentizace webu.'],401);
    }
    return $siteId;
}

function bridge_auth(): array {
    $bridgeId=clean($_SERVER['HTTP_X_HS_BRIDGE_ID'] ?? '',80);
    $token=clean($_SERVER['HTTP_X_HS_BRIDGE_TOKEN'] ?? '',200);
    if ($bridgeId==='' || $token==='') json_response(['error'=>'Chybí autentizace Bridge.'],401);
    $st=db()->prepare('SELECT * FROM bridges WHERE bridge_id=? LIMIT 1'); $st->execute([$bridgeId]); $row=$st->fetch();
    if (!$row || !hash_equals((string)$row['token_hash'],secret_hash($token))) json_response(['error'=>'Neplatná autentizace Bridge.'],401);
    $now=now_iso(); db()->prepare('UPDATE bridges SET last_seen_at=?,updated_at=? WHERE bridge_id=?')->execute([$now,$now,$bridgeId]); $row['last_seen_at']=$now;
    return $row;
}

function bridge_payload(array $row): array {
    $paired=(string)$row['state']==='paired' && (string)$row['target_base_url']!=='' && (string)$row['site_token_enc']!=='';
    $out=[
        'paired'=>$paired,
        'directoryProtocol'=>2,
        'rcIdentity'=>[
            'setting3'=>(string)($row['rc_setting3'] ?? ''),
            'setting10'=>(string)($row['rc_setting10'] ?? ''),
        ],
    ];
    if ($paired) {
        $out['siteBaseURL']=(string)$row['target_base_url'];
        $out['siteBaseUrl']=(string)$row['target_base_url']; // compatibility
        $out['siteName']=(string)$row['target_name'];
        $out['siteInstanceID']=(string)$row['site_instance_id'];
        $out['siteInstanceId']=(string)$row['site_instance_id']; // compatibility
        $out['siteToken']=decrypt_secret((string)$row['site_token_enc']);
    }
    return $out;
}
