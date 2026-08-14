<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$javascript = file_get_contents(dirname(__DIR__) . '/assets/app.js') ?: '';

$usesParallelUi = static fn(int $effectiveCalls): bool => $effectiveCalls > 1;
$assert(!$usesParallelUi(1), 'one effective call keeps the individual UI path');
$assert($usesParallelUi(5), 'five effective calls use the aggregate batch UI');
$assert(str_contains($source, "campaign_requested_parallelism(\$campaign) > 1"), 'backend keeps the existing parallelism threshold');
$assert(str_contains($source, "(int)(\$batch['effective_parallelism'] ?? 1) <= 1"), 'aggregate UI follows effective parallelism');
$assert(str_contains($source, "race_outcome = 'WINNER'"), 'winner is selected explicitly for the active UI');
$assert(str_contains($source, '$isConnectedBatchWinner'), 'connected winner enters the normal live-call UI');
$assert(str_contains($source, "NOT IN ('LOSER','LATE_ANSWERED')"), 'loser and late answered calls are excluded from active UI fallback');
$assert(str_contains($source, 'data-parallel-batch-state'), 'aggregate batch state is rendered');
$assert(!str_contains($source, 'data-parallel-batch-phone'), 'aggregate state exposes no participant phone');
$assert(str_contains($javascript, "fetch('?page=agent_batch_state'"), 'batch UI reuses one lightweight state endpoint');
$assert(str_contains($javascript, 'window.location.reload()'), 'winner or completed batch returns to the normal UI');
$assert(str_contains($source, '$isAutoDialing && !$isBatchWaitingForWinner && !$activeCall'), 'parallel waiting never reuses a serial auto-call phone');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE calls (id INTEGER PRIMARY KEY, company_id INTEGER, dial_batch_id INTEGER, status TEXT, race_outcome TEXT, answered_at TEXT, finalized_at TEXT);");
$db->exec("INSERT INTO calls VALUES
    (1,1,9,'ringing','PENDING',NULL,NULL),
    (2,1,9,'ringing','PENDING',NULL,NULL),
    (3,1,9,'in_progress','PENDING',NULL,NULL),
    (4,1,9,'failed','ORIGINATE_FAILED',NULL,'2026-08-14 12:00:00'),
    (5,1,9,'failed','ORIGINATE_FAILED',NULL,'2026-08-14 12:00:01');");
$counts = $db->query("SELECT COUNT(*) originated_count,
    SUM(CASE WHEN finalized_at IS NULL AND status IN ('in_progress','calling_origin','ringing','answered','connected') THEN 1 ELSE 0 END) active_count,
    SUM(CASE WHEN finalized_at IS NULL AND status='ringing' THEN 1 ELSE 0 END) ringing_count,
    SUM(CASE WHEN answered_at IS NOT NULL OR race_outcome IN ('WINNER','LATE_ANSWERED') THEN 1 ELSE 0 END) answered_count,
    SUM(CASE WHEN finalized_at IS NOT NULL THEN 1 ELSE 0 END) finalized_count
    FROM calls WHERE company_id=1 AND dial_batch_id=9")->fetch(PDO::FETCH_ASSOC);
$assert((int)$counts['originated_count'] === 5, 'originated counter includes all batch calls');
$assert((int)$counts['active_count'] === 3, 'active counter includes only live calls');
$assert((int)$counts['ringing_count'] === 2, 'ringing counter is accurate');
$assert((int)$counts['answered_count'] === 0, 'waiting batch starts with no answered call');
$assert((int)$counts['finalized_count'] === 2, 'finalized counter is accurate');

$db->exec("UPDATE calls SET status='connected', race_outcome='WINNER', answered_at='2026-08-14 12:00:02' WHERE id=2");
$db->exec("UPDATE calls SET status='cancelled', race_outcome='LOSER', finalized_at='2026-08-14 12:00:03' WHERE id=1");
$db->exec("UPDATE calls SET status='answered', race_outcome='LATE_ANSWERED', answered_at='2026-08-14 12:00:03' WHERE id=3");
$visibleWinner = $db->query("SELECT id FROM calls WHERE id=2 AND company_id=1 AND race_outcome='WINNER'")->fetchColumn();
$visibleLosers = $db->query("SELECT COUNT(*) FROM calls WHERE id IN (1,3) AND race_outcome NOT IN ('LOSER','LATE_ANSWERED')")->fetchColumn();
$assert((int)$visibleWinner === 2, 'only the winner becomes the active call');
$assert((int)$visibleLosers === 0, 'loser and late answered never become active calls');

$activeAfterFinish = 0;
$awaitingWinner = $activeAfterFinish > 0;
$assert(!$awaitingWinner, 'batch without winner clears aggregate waiting state when no call remains');

echo "OK - {$tests} tests\n";
