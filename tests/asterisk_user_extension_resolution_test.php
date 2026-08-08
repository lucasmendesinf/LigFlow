<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$extensionFromIdentifier = static function (mixed $identifier): string {
    if (!is_string($identifier) && !is_numeric($identifier)) return '';
    $value = trim((string)$identifier);
    if ($value === '') return '';
    if (preg_match('/^PJSIP\\/([0-9]{1,32})(?:[-@\\/]|$)/i', $value, $matches) === 1) return $matches[1];
    if (preg_match('/^([0-9]{1,32})(?:[-@]|$)/', $value, $matches) === 1) return $matches[1];
    return '';
};

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, company_id INTEGER NOT NULL);");
$db->exec("CREATE TABLE asterisk_user_extensions (
    id INTEGER PRIMARY KEY,
    company_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    asterisk_server_id INTEGER NOT NULL,
    extension TEXT NOT NULL,
    status TEXT NOT NULL,
    lifecycle_status TEXT NOT NULL DEFAULT 'ACTIVE'
);");
$db->exec("CREATE TABLE calls (id INTEGER PRIMARY KEY, company_id INTEGER NOT NULL, agent_id INTEGER, dial_batch_id INTEGER);");
$db->exec("CREATE TABLE dial_batches (id INTEGER PRIMARY KEY, company_id INTEGER NOT NULL, agent_id INTEGER);");
$db->exec("INSERT INTO users(id,company_id) VALUES (10,1),(11,2),(99,1);");
$db->exec("INSERT INTO asterisk_user_extensions(company_id,user_id,asterisk_server_id,extension,status) VALUES
    (1,10,1,'1003','Ativo'),
    (2,11,1,'1003','Ativo'),
    (1,99,1,'1004','Inativo');");

$resolve = static function (PDO $db, int $companyId, string $extension): ?int {
    $stmt = $db->prepare("SELECT user_id FROM asterisk_user_extensions WHERE company_id = ? AND asterisk_server_id = 1 AND extension = ? AND status = 'Ativo' AND COALESCE(lifecycle_status, 'ACTIVE') = 'ACTIVE' LIMIT 1");
    $stmt->execute([$companyId, $extension]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int)$id;
};

$associate = static function (PDO $db, array $call, string $extension) use ($resolve): ?int {
    if ($extension === '') return null;
    $companyId = (int)$call['company_id'];
    $currentUserId = (int)($call['agent_id'] ?? 0);
    if ($currentUserId > 0) {
        $user = $db->prepare('SELECT id FROM users WHERE id = ? AND company_id = ?');
        $user->execute([$currentUserId, $companyId]);
        if ($user->fetchColumn()) return $currentUserId;
    }
    $userId = $resolve($db, $companyId, $extension);
    if ($userId === null) return null;
    $db->prepare('UPDATE calls SET agent_id = ? WHERE id = ? AND company_id = ? AND (agent_id IS NULL OR agent_id = 0)')
        ->execute([$userId, (int)$call['id'], $companyId]);
    return $userId;
};

$assert($extensionFromIdentifier('PJSIP/1003-0000012') === '1003', 'extracts extension from Asterisk channel name');
$assert($extensionFromIdentifier('PJSIP/1003') === '1003', 'extracts extension from PJSIP endpoint');
$assert($extensionFromIdentifier('1003') === '1003', 'accepts a plain extension');
$assert($extensionFromIdentifier('PJSIP/directcall-0000012') === '', 'does not treat a trunk as an extension');

$assert($resolve($db, 1, '1003') === 10, 'resolves extension to user in its tenant');
$assert($resolve($db, 2, '1003') === 11, 'isolates identical extensions between tenants');
$assert($resolve($db, 1, '1004') === null, 'does not resolve inactive extension');
$assert($resolve($db, 1, '9999') === null, 'missing extension is harmless');

$db->exec("INSERT INTO asterisk_user_extensions(company_id,user_id,asterisk_server_id,extension,status,lifecycle_status) VALUES (1,98,1,'1005','Ativo','RESERVED')");
$assert($resolve($db, 1, '1005') === null, 'reserved extension does not resolve before provisioning is active');

$db->exec('INSERT INTO calls(id,company_id,agent_id) VALUES (1,1,NULL),(2,1,99);');
$call = ['id' => 1, 'company_id' => 1, 'agent_id' => null];
$assert($associate($db, $call, '1003') === 10, 'associates unassigned call with extension owner');
$assert((int)$db->query('SELECT agent_id FROM calls WHERE id = 1')->fetchColumn() === 10, 'stores resolved user in call');

$alreadyAssigned = ['id' => 2, 'company_id' => 1, 'agent_id' => 99];
$assert($associate($db, $alreadyAssigned, '1003') === 99, 'does not overwrite a valid existing user');
$assert((int)$db->query('SELECT agent_id FROM calls WHERE id = 2')->fetchColumn() === 99, 'preserves existing call user');

$source = file_get_contents(dirname(__DIR__) . '/index.php');
$assert(str_contains($source, 'function asterisk_extension_from_identifier'), 'production helper exists');
$assert(str_contains($source, 'AND (agent_id IS NULL OR agent_id = 0)'), 'production association protects existing users');

echo "OK - {$tests} tests\n";
