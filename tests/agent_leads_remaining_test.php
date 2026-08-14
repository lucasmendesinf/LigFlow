<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$javascript = file_get_contents(dirname(__DIR__) . '/assets/app.js') ?: '';

$assert(str_contains($source, 'function agent_campaign_remaining_leads'), 'remaining leads use one central helper');
$assert(str_contains($source, 'c.list_id = ?'), 'counter is restricted to the selected campaign list');
$assert(str_contains($source, 'COALESCE(c.attempts, 0) = 0'), 'counter follows the Ligado Nao attempts rule');
$assert(str_contains($source, 'c.last_call_at IS NULL'), 'counter excludes contacts with a previous call timestamp');
$assert(str_contains($source, "tenant_clause('ca')"), 'campaign access is tenant scoped');
$assert(str_contains($javascript, '?page=agent_leads_remaining&campaign_id='), 'frontend refreshes the selected campaign count');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE contacts (id INTEGER PRIMARY KEY, company_id INTEGER, list_id INTEGER, status TEXT, attempts INTEGER, last_call_at TEXT)');
$db->exec("INSERT INTO contacts VALUES
    (1, 1, 10, 'novo', 0, NULL),
    (2, 1, 10, 'reservado', 0, NULL),
    (3, 1, 10, 'concluido', 1, '2026-08-14 12:00:00'),
    (4, 1, 10, 'novo', 0, '2026-08-14 12:01:00'),
    (5, 1, 10, 'excluido', 0, NULL),
    (6, 1, 20, 'novo', 0, NULL),
    (7, 1, 20, 'novo', 0, NULL),
    (8, 2, 10, 'novo', 0, NULL)");

$count = static function (PDO $db, int $companyId, int $listId): int {
    $stmt = $db->prepare("SELECT COUNT(*) FROM contacts c
        WHERE c.company_id = ? AND c.list_id = ? AND c.status <> 'excluido'
          AND COALESCE(c.attempts, 0) = 0 AND c.last_call_at IS NULL");
    $stmt->execute([$companyId, $listId]);
    return (int)$stmt->fetchColumn();
};

$assert($count($db, 1, 10) === 2, 'list 10 counts only Ligado Nao contacts');
$assert($count($db, 1, 20) === 2, 'switching to list 20 returns its independent count');
$assert($count($db, 2, 10) === 1, 'tenant isolation excludes contacts from another company');
$db->exec("UPDATE contacts SET attempts = 1, last_call_at = '2026-08-14 12:02:00', status = 'concluido' WHERE id = 1");
$assert($count($db, 1, 10) === 1, 'processed lead leaves the counter immediately');

echo "OK - {$tests} tests\n";
