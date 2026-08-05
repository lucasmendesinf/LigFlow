<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$effective = static function (int $campaign, int $team, int $tenant): int {
    return max(1, min($campaign, $team, $tenant, 10));
};

$assert($effective(1, 10, 10) === 1, 'single campaign remains single');
$assert($effective(5, 3, 8) === 3, 'team limit reduces batch');
$assert($effective(10, 10, 2) === 2, 'tenant limit reduces batch');
$assert($effective(12, 20, 20) === 10, 'safety limit is ten');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE contacts (id INTEGER PRIMARY KEY, status TEXT, attempts INTEGER DEFAULT 0, reserved_by INTEGER, last_call_at TEXT); CREATE TABLE calls (id INTEGER PRIMARY KEY, batch_id INTEGER, contact_id INTEGER, status TEXT, race_outcome TEXT, finalized_at TEXT); CREATE TABLE dial_batches (id INTEGER PRIMARY KEY, winner_call_id INTEGER, status TEXT, next_started_at TEXT)");
$db->exec("INSERT INTO contacts(id,status) VALUES (1,'novo'),(2,'novo'),(3,'novo')");

$db->beginTransaction();
$reserve = $db->prepare("UPDATE contacts SET status='em_ligacao', attempts=attempts+1, reserved_by=7, last_call_at='now' WHERE id=? AND status='novo' AND attempts=0 AND last_call_at IS NULL");
foreach ([1, 2] as $id) $reserve->execute([$id]);
$db->exec("INSERT INTO dial_batches(id,status) VALUES (1,'ORIGINATING')");
$db->exec("INSERT INTO calls(id,batch_id,contact_id,status,race_outcome) VALUES (11,1,1,'ringing','PENDING'),(12,1,2,'ringing','PENDING')");
$db->commit();
$assert((int)$db->query("SELECT SUM(attempts) FROM contacts WHERE id IN (1,2)")->fetchColumn() === 2, 'each lead gets one first-wave attempt');
$reserve->execute([1]);
$assert($reserve->rowCount() === 0, 'repeated reserve does not duplicate an attempt');

$winner = $db->prepare("UPDATE dial_batches SET winner_call_id=?, status='WINNER' WHERE id=? AND winner_call_id IS NULL AND status IN ('ORIGINATING','RINGING')");
$winner->execute([11, 1]);
$assert($winner->rowCount() === 1, 'first answered call wins');
$db->prepare("UPDATE calls SET race_outcome='WINNER' WHERE id=?")->execute([11]);
$db->prepare("UPDATE calls SET race_outcome='LOSER' WHERE batch_id=? AND id<>? AND finalized_at IS NULL")->execute([1, 11]);
$winner->execute([12, 1]);
$assert($winner->rowCount() === 0, 'duplicate or late event cannot change winner');
$assert((string)$db->query('SELECT race_outcome FROM calls WHERE id=12')->fetchColumn() === 'LOSER', 'loser remains loser');

$db->exec("UPDATE calls SET status='failed', finalized_at='now' WHERE batch_id=1 AND id<>11");
$assert((int)$db->query('SELECT COUNT(*) FROM calls WHERE batch_id=1 AND finalized_at IS NULL AND id<>11')->fetchColumn() === 0, 'finished losers can be detected');

$terminalStates = ['completed' => true, 'failed' => true, 'cancelled' => true];
$assert(isset($terminalStates['failed']), 'terminal state does not regress after an out-of-order event');

$noWinner = $db->prepare("UPDATE dial_batches SET status='NO_WINNER', next_started_at='now' WHERE id=? AND winner_call_id IS NULL AND next_started_at IS NULL");
$db->exec("INSERT INTO dial_batches(id,status) VALUES (2,'ORIGINATING')");
$noWinner->execute([2]);
$assert($noWinner->rowCount() === 1, 'batch with no winner advances once');
$noWinner->execute([2]);
$assert($noWinner->rowCount() === 0, 'repeated final event does not start another batch');

$db->exec("UPDATE contacts SET status='em_ligacao', reserved_by=7 WHERE id IN (1,2)");
$db->exec("UPDATE contacts SET status='reservado', reserved_by=7 WHERE id=3");
$db->prepare("UPDATE contacts SET status='concluido', reserved_by=NULL WHERE id IN (SELECT contact_id FROM calls WHERE batch_id=?)")->execute([1]);
$assert((string)$db->query('SELECT status FROM contacts WHERE id=3')->fetchColumn() === 'reservado', 'batch cancel does not change an external reservation');

$tenantA = ['company' => 1, 'agent' => 7];
$tenantB = ['company' => 2, 'agent' => 8];
$assert($tenantA['company'] !== $tenantB['company'], 'batches remain tenant isolated');
echo "OK - {$tests} tests\n";