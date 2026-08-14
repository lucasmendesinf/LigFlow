<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$mask = static function (string $value): string {
    $digits = preg_replace('/\D+/', '', $value);
    return $digits === '' ? '-' : str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
};

$assert($mask('+5541996310725') === '*********0725', 'phone is masked');
$assert($mask('') === '-', 'empty phone is masked');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE dial_batches (id INTEGER PRIMARY KEY, company_id INTEGER, campaign_id INTEGER, agent_id INTEGER, requested_parallelism INTEGER, effective_parallelism INTEGER, status TEXT, winner_call_id INTEGER, created_at TEXT, updated_at TEXT); CREATE TABLE calls (id INTEGER PRIMARY KEY, company_id INTEGER, dial_batch_id INTEGER, status TEXT, race_outcome TEXT, finalized_at TEXT, last_event_at TEXT, updated_at TEXT, created_at TEXT, provider_bridge_id TEXT);");
$db->exec("INSERT INTO dial_batches VALUES (1,1,10,7,3,2,'CONNECTED',11,'2026-08-07 12:00:00','2026-08-07 12:00:05'),(2,2,10,8,1,1,'ORIGINATING',21,'2026-08-07 12:00:00','2026-08-07 12:00:05')");
$db->exec("INSERT INTO calls VALUES (11,1,1,'connected','WINNER',NULL,'2026-08-07 12:00:05','2026-08-07 12:00:05','2026-08-07 12:00:00','bridge-1'),(12,1,1,'failed','LOSER','2026-08-07 12:00:04','2026-08-07 12:00:04','2026-08-07 12:00:04','2026-08-07 12:00:00',NULL),(13,1,1,'ringing','LATE_ANSWERED',NULL,'2026-08-07 12:00:05','2026-08-07 12:00:05','2026-08-07 12:00:00',NULL),(21,2,2,'ringing','PENDING',NULL,'2026-08-07 12:00:05','2026-08-07 12:00:05','2026-08-07 12:00:00',NULL)");

$stmt = $db->prepare("SELECT b.id, COUNT(c.id) originated, SUM(CASE WHEN c.race_outcome='WINNER' THEN 1 ELSE 0 END) winner, SUM(CASE WHEN c.race_outcome='LOSER' THEN 1 ELSE 0 END) loser, SUM(CASE WHEN c.race_outcome='LATE_ANSWERED' THEN 1 ELSE 0 END) late, SUM(CASE WHEN c.race_outcome IN ('LOSER','LATE_ANSWERED') AND c.finalized_at IS NULL AND c.status IN ('in_progress','calling_origin','ringing','answered','connected') THEN 1 ELSE 0 END) active_loser FROM dial_batches b LEFT JOIN calls c ON c.dial_batch_id=b.id AND c.company_id=b.company_id WHERE b.company_id=? GROUP BY b.id ORDER BY b.id LIMIT 10 OFFSET 0");
$stmt->execute([1]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$assert((int)$row['id'] === 1, 'tenant isolation excludes other tenant batches');
$assert((int)$row['originated'] === 3, 'batch call count is correct');
$assert((int)$row['winner'] === 1 && (int)$row['loser'] === 1 && (int)$row['late'] === 1, 'winner loser late metrics are correct');
$assert((int)$row['active_loser'] === 1, 'active late answer triggers loser alert');

$source = file_get_contents(__DIR__ . '/../index.php');
$assert(str_contains($source, "if (!asterisk_diagnostics_can_access(\$user))"), 'endpoint enforces permission');
$assert(str_contains($source, "WHERE ca.company_id = ? AND ca.dial_batch_id = ?"), 'detail query is tenant isolated');
$assert(str_contains($source, "unset(\$call['destination_number']);"), 'endpoint omits full phone from payload');
$assert(str_contains($source, "LIMIT {\$perPage} OFFSET {\$offset}"), 'batch and call queries are paginated');
$assert(str_contains($source, "Worker ARI sem evento recente para lote ativo."), 'worker health alert exists');
$assert(str_contains($source, "Vencedora sem bridge conhecida."), 'winner bridge alert exists');
$assert(str_contains($source, "Perdedora ainda ativa apos a eleicao."), 'active loser alert exists');
$assert(str_contains($source, "Mais de um lote ativo para este consultor."), 'duplicate active batch alert exists');
$assert(str_contains($source, "Paralelismo efetivo menor que o solicitado."), 'parallelism alert exists');
$assert(str_contains($source, "ensure_column(\$pdo, 'dial_batches', 'requested_parallelism'"), 'legacy dial batches gain requested parallelism');
$assert(str_contains($source, "ensure_column(\$pdo, 'dial_batches', 'effective_parallelism'"), 'legacy dial batches gain effective parallelism');
$assert(str_contains($source, "ensure_column(\$pdo, 'dial_batches', 'telephony_trunk'"), 'legacy dial batches gain telephony trunk');
$assert(str_contains($source, "ensure_column(\$pdo, 'dial_batches', 'next_started_at'"), 'legacy dial batches gain continuation timestamp');

$workerSource = file_get_contents(__DIR__ . '/../asterisk_ari_worker.php') ?: '';
$deploySource = file_get_contents(__DIR__ . '/../.cpanel.yml') ?: '';
$assert(str_contains($workerSource, 'LOCK_EX | LOCK_NB'), 'ARI worker prevents duplicate instances');
$assert(str_contains($deploySource, 'asterisk_ari_worker.php $DEPLOYPATH/'), 'cPanel deploy includes the ARI worker');
$assert(str_contains($deploySource, '/usr/bin/nohup /usr/bin/env php'), 'cPanel deploy starts the ARI worker');

echo "OK - {$tests} tests\n";
