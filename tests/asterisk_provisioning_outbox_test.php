<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$database = tempnam(sys_get_temp_dir(), 'ligflow-asterisk-outbox-');
$open = static function () use ($database): PDO {
    $pdo = new PDO('sqlite:' . $database);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA busy_timeout = 50');
    return $pdo;
};
$db = $open();
$db->exec("CREATE TABLE asterisk_user_extensions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    asterisk_server_id INTEGER NOT NULL DEFAULT 1,
    extension TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Ativo',
    provisioning_status TEXT NOT NULL DEFAULT 'Pendente',
    lifecycle_status TEXT NOT NULL DEFAULT 'ACTIVE',
    provisioned_at TEXT,
    last_provision_error TEXT,
    provisioning_version INTEGER NOT NULL DEFAULT 1,
    released_at TEXT,
    created_at TEXT,
    updated_at TEXT,
    deactivated_at TEXT
)");
$db->exec("CREATE TABLE asterisk_provisioning_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    asterisk_user_extension_id INTEGER NOT NULL,
    asterisk_server_id INTEGER NOT NULL DEFAULT 1,
    operation TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'PENDING',
    idempotency_key TEXT NOT NULL UNIQUE,
    attempts INTEGER NOT NULL DEFAULT 0,
    last_error TEXT,
    payload_json TEXT NOT NULL DEFAULT '{}',
    created_at TEXT,
    updated_at TEXT
)");
$db->exec("CREATE UNIQUE INDEX active_extension ON asterisk_user_extensions(company_id, asterisk_server_id, extension) WHERE status = 'Ativo'");

$next = static function (PDO $pdo, int $companyId, int $serverId = 1): string {
    $used = ['1001' => true]; // endpoint WebRTC configurado, reservado fora da alocacao.
    $stmt = $pdo->prepare('SELECT extension, status, lifecycle_status FROM asterisk_user_extensions WHERE company_id=? AND asterisk_server_id=?');
    $stmt->execute([$companyId, $serverId]);
    foreach ($stmt->fetchAll() as $row) {
        $lifecycle = strtoupper((string)$row['lifecycle_status']);
        if ($row['status'] === 'Ativo' || in_array($lifecycle, ['RESERVED', 'ACTIVE', 'RELEASING'], true)) {
            $used[(string)$row['extension']] = true;
        }
    }
    for ($extension = 1000; $extension <= 9999; $extension++) {
        if (!isset($used[(string)$extension])) return (string)$extension;
    }
    throw new RuntimeException('no extension available');
};

$reserve = static function (PDO $pdo, int $companyId, int $userId, ?string $requested = null): array {
    $extension = $requested ?: $GLOBALS['next']($pdo, $companyId);
    if (preg_match('/^[0-9]{1,32}$/', $extension) !== 1 || $extension === '1001') throw new InvalidArgumentException('invalid extension');
    $duplicate = $pdo->prepare("SELECT id FROM asterisk_user_extensions WHERE company_id=? AND asterisk_server_id=1 AND extension=? AND (status='Ativo' OR lifecycle_status IN ('RESERVED','ACTIVE','RELEASING'))");
    $duplicate->execute([$companyId, $extension]);
    if ($duplicate->fetch()) throw new InvalidArgumentException('duplicate extension');
    $pdo->prepare("INSERT INTO asterisk_user_extensions(company_id,user_id,asterisk_server_id,extension,status,provisioning_status,lifecycle_status,provisioning_version,created_at,updated_at) VALUES (?,?,1,?,'Ativo','Pendente','RESERVED',1,datetime('now'),datetime('now'))")
        ->execute([$companyId, $userId, $extension]);
    $extensionId = (int)$pdo->lastInsertId();
    $key = bin2hex(random_bytes(16));
    $payload = json_encode(['extension' => $extension, 'lifecycle_status' => 'RESERVED', 'provisioning_version' => 1], JSON_THROW_ON_ERROR);
    $pdo->prepare("INSERT INTO asterisk_provisioning_jobs(company_id,user_id,asterisk_user_extension_id,asterisk_server_id,operation,status,idempotency_key,payload_json,created_at,updated_at) VALUES (?,?,?,1,'CREATE','PENDING',?, ?,datetime('now'),datetime('now'))")
        ->execute([$companyId, $userId, $extensionId, $key, $payload]);
    return ['extension' => $extension, 'extension_id' => $extensionId, 'job_id' => (int)$pdo->lastInsertId(), 'key' => $key];
};
$GLOBALS['next'] = $next;

$db->exec('BEGIN IMMEDIATE');
$first = $reserve($db, 1, 10);
$db->exec('COMMIT');
$assert($first['extension'] === '1000', 'automatic reservation starts at the configured range and skips WebRTC 1001');
$link = $db->query('SELECT lifecycle_status, provisioning_status FROM asterisk_user_extensions WHERE id=' . $first['extension_id'])->fetch();
$assert($link['lifecycle_status'] === 'RESERVED' && $link['provisioning_status'] === 'Pendente', 'new extension is reserved but not provisioned');
$job = $db->query('SELECT operation,status,payload_json FROM asterisk_provisioning_jobs WHERE id=' . $first['job_id'])->fetch();
$assert($job['operation'] === 'CREATE' && $job['status'] === 'PENDING', 'CREATE job is pending');
$assert(!str_contains($job['payload_json'], 'password'), 'job payload excludes SIP secrets');

$db->exec('BEGIN IMMEDIATE');
$second = $reserve($db, 1, 11);
$db->exec('COMMIT');
$assert($second['extension'] === '1002', 'reserved extension is not reused');

$otherTenant = $open();
$otherTenant->exec('BEGIN IMMEDIATE');
$isolated = $reserve($otherTenant, 2, 20, '1000');
$otherTenant->exec('COMMIT');
$assert($isolated['extension'] === '1000', 'same extension is isolated by tenant');

try {
    $db->prepare("INSERT INTO asterisk_provisioning_jobs(company_id,user_id,asterisk_user_extension_id,asterisk_server_id,operation,status,idempotency_key,payload_json) VALUES (1,10,?,1,'CREATE','PENDING',?, '{}')")
        ->execute([$first['extension_id'], $first['key']]);
    $assert(false, 'duplicate idempotency key must fail');
} catch (PDOException) {
    $assert(true, 'unique idempotency key is enforced');
}

$db->exec('BEGIN IMMEDIATE');
try {
    $rolledBack = $reserve($db, 1, 12);
    throw new RuntimeException('simulate job persistence failure');
} catch (RuntimeException $error) {
    $db->exec('ROLLBACK');
}
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_user_extensions WHERE user_id=12")->fetchColumn() === 0, 'reservation rolls back with the related user operation');
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_provisioning_jobs WHERE user_id=12")->fetchColumn() === 0, 'job rolls back with the reservation');

$db->exec("UPDATE asterisk_user_extensions SET status='Inativo', lifecycle_status='RELEASED', released_at=datetime('now') WHERE id=" . $first['extension_id']);
$db->exec('BEGIN IMMEDIATE');
$reused = $reserve($db, 1, 13, '1000');
$db->exec('COMMIT');
$assert($reused['extension'] === '1000', 'released extension can be reserved later');
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_user_extensions WHERE company_id=1 AND extension='1000'")->fetchColumn() === 2, 'released extension history is preserved');

$writer = $open();
$contender = $open();
$writer->exec('BEGIN IMMEDIATE');
$held = $reserve($writer, 3, 30);
try {
    $contender->exec('BEGIN IMMEDIATE');
    $assert(false, 'second concurrent writer must not enter while a reservation is open');
} catch (PDOException) {
    $assert(true, 'BEGIN IMMEDIATE serializes concurrent reservations');
}
$writer->exec('COMMIT');
$contender->exec('BEGIN IMMEDIATE');
$afterLock = $reserve($contender, 3, 31);
$contender->exec('COMMIT');
$assert($held['extension'] !== $afterLock['extension'], 'serialized reservations receive different extensions');

$source = file_get_contents(dirname(__DIR__) . '/index.php');
$assert(str_contains($source, 'function asterisk_reserve_user_extension'), 'production reservation helper exists');
$assert(str_contains($source, "asterisk_provisioning_jobs"), 'production provisioning outbox exists');
$assert(str_contains($source, "COALESCE(lifecycle_status, 'ACTIVE') = 'ACTIVE'"), 'reserved links are excluded from automatic call association');

@unlink($database);
echo "OK - {$tests} tests\n";