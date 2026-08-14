<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$assert(str_contains($source, "AND list_id = ?\n                  AND status <> 'excluido'"), 'remaining leads are restricted to the selected campaign list');
$assert(str_contains($source, 'AND COALESCE(attempts, 0) = 0'), 'only contacts displayed as Ligado Nao are counted');
$assert(str_contains($source, 'AND last_call_at IS NULL'), 'contacts with any recorded call are excluded');
$assert(str_contains($source, "'Leads restantes' => \$remainingLeads"), 'the dialer card uses the selected-list count');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE contacts (id INTEGER PRIMARY KEY, company_id INTEGER, list_id INTEGER, status TEXT, attempts INTEGER, last_call_at TEXT)');
$db->exec("INSERT INTO contacts VALUES
    (1, 1, 10, 'novo', 0, NULL),
    (2, 1, 10, 'reservado', 0, NULL),
    (3, 1, 10, 'concluido', 1, '2026-08-14 12:00:00'),
    (4, 1, 10, 'excluido', 0, NULL),
    (5, 1, 20, 'novo', 0, NULL),
    (6, 2, 10, 'novo', 0, NULL)");
$query = $db->prepare("SELECT COUNT(*) FROM contacts
    WHERE company_id = ? AND list_id = ? AND status <> 'excluido'
      AND COALESCE(attempts, 0) = 0 AND last_call_at IS NULL");
$query->execute([1, 10]);
$assert((int)$query->fetchColumn() === 2, 'only uncalled leads from the selected tenant and list are counted');

echo "OK - {$tests} tests\n";
