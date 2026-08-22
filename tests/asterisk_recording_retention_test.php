<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_recording.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};
$config = asterisk_recording_retention_config([
    'short_threshold_seconds' => 5,
    'discard_grace_hours' => 24,
    'retention_days' => 90,
    'disk_threshold_percent' => 80,
    'batch_size' => 20,
    'storage_usage_percent' => null,
]);
$base = new DateTimeImmutable('2026-08-22 12:00:00', new DateTimeZone('UTC'));
$shortPolicy = asterisk_recording_retention_policy(4.9, '2026-08-22 12:00:00', $config, $base);
$assert($shortPolicy['status'] === 'DISCARD_PENDING', '4.9 seconds becomes DISCARD_PENDING');
$assert($shortPolicy['discard_eligible_at'] === '2026-08-23 12:00:00', 'short recording receives a 24 hour grace period');
$normalPolicy = asterisk_recording_retention_policy(5.0, '2026-08-22 12:00:00', $config, $base);
$assert($normalPolicy['status'] === 'READY', '5 seconds is not a short recording');
$assert($normalPolicy['retention_until'] === '2026-11-20 12:00:00', 'normal recording receives 90 day retention');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec("CREATE TABLE call_recordings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, company_id INTEGER NOT NULL, call_id INTEGER NOT NULL UNIQUE,
    recording_name TEXT NOT NULL UNIQUE, format TEXT NOT NULL DEFAULT 'wav', status TEXT NOT NULL,
    duration_seconds REAL, size_bytes INTEGER, started_at TEXT, finished_at TEXT, failure_reason TEXT,
    discard_eligible_at TEXT, discarded_at TEXT, retention_until TEXT, last_cleanup_error TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP
)");
$insert = $db->prepare("INSERT INTO call_recordings
    (company_id,call_id,recording_name,status,duration_seconds,finished_at,created_at)
    VALUES (?,?,?,?,?,?,?)");
$name = static fn(int $company, int $call): string => asterisk_bridge_recording_name($company, $call, str_pad((string)$call, 16, '0', STR_PAD_LEFT));
$insert->execute([1, 101, $name(1, 101), 'READY', 4.9, '2026-08-22 12:00:00', '2026-08-22 12:00:00']);
$insert->execute([1, 102, $name(1, 102), 'READY', 5.0, '2026-08-22 12:00:00', '2026-08-22 12:00:00']);
$insert->execute([2, 201, $name(2, 201), 'READY', 20.0, '2026-08-22 12:00:00', '2026-08-22 12:00:00']);
$insert->execute([1, 103, $name(1, 103), 'RECORDING', null, null, '2026-01-01 00:00:00']);
$insert->execute([1, 104, $name(1, 104), 'FAILED', null, '2026-01-01 00:00:00', '2026-01-01 00:00:00']);

$deleted = [];
$fetch = static fn(string $recordingName): array => throw new RuntimeException('fetch should not run when duration exists');
$delete = static function (string $recordingName) use (&$deleted): string { $deleted[] = $recordingName; return 'DELETED'; };
$stats = asterisk_run_recording_retention($db, $config, $fetch, $delete, $base);
$short = $db->query('SELECT * FROM call_recordings WHERE call_id=101')->fetch();
$normal = $db->query('SELECT * FROM call_recordings WHERE call_id=102')->fetch();
$assert($short['status'] === 'DISCARD_PENDING' && $short['discarded_at'] === null, 'short recording is preserved before grace expires');
$assert($normal['status'] === 'READY' && $normal['retention_until'] === '2026-11-20 12:00:00', 'normal recording stays READY inside retention');
$assert($deleted === [], 'nothing is deleted before eligibility');
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=103")->fetchColumn() === 'RECORDING', 'active recording is untouched');
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=104")->fetchColumn() === 'FAILED', 'failed recording is untouched');

asterisk_run_recording_retention($db, $config, $fetch, $delete, $base->modify('+23 hours'));
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=101")->fetchColumn() === 'DISCARD_PENDING', 'before 24 hours the file is not deleted');
asterisk_run_recording_retention($db, $config, $fetch, $delete, $base->modify('+25 hours'));
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=101")->fetchColumn() === 'DISCARDED', 'after grace ARI deletion marks DISCARDED');
$assert(count($deleted) === 1, 'short recording is deleted exactly once');
asterisk_run_recording_retention($db, $config, $fetch, $delete, $base->modify('+26 hours'));
$assert(count($deleted) === 1, 'repeated execution is idempotent');

asterisk_run_recording_retention($db, $config, $fetch, $delete, $base->modify('+89 days'));
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=102")->fetchColumn() === 'READY', 'recording inside 90 days is not deleted');
asterisk_run_recording_retention($db, $config, $fetch, $delete, $base->modify('+91 days'));
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=102")->fetchColumn() === 'DISCARDED', 'recording past 90 days is deleted');
$assert($db->query("SELECT status FROM call_recordings WHERE call_id=201")->fetchColumn() === 'DISCARDED', 'each tenant recording is processed only by its own row identity');

$insert->execute([1, 105, $name(1, 105), 'READY', 4.0, '2026-08-01 00:00:00', '2026-08-01 00:00:00']);
$errorDelete = static fn(string $recordingName): string => throw new RuntimeException("falha\ntemporaria ARI");
asterisk_run_recording_retention($db, $config, $fetch, $errorDelete, $base);
$failedCleanup = $db->query('SELECT * FROM call_recordings WHERE call_id=105')->fetch();
$assert($failedCleanup['status'] === 'DISCARD_PENDING', 'ARI failure keeps recording recoverable');
$assert($failedCleanup['last_cleanup_error'] !== null && !str_contains($failedCleanup['last_cleanup_error'], "\n"), 'cleanup error is sanitized for retry');

$insert->execute([2, 202, $name(2, 202), 'READY', 4.0, '2026-08-01 00:00:00', '2026-08-01 00:00:00']);
$missingDelete = static fn(string $recordingName): string => 'MISSING';
asterisk_run_recording_retention($db, $config, $fetch, $missingDelete, $base);
$missing = $db->query('SELECT * FROM call_recordings WHERE call_id=202')->fetch();
$assert($missing['status'] === 'DISCARDED' && $missing['discarded_at'] !== null, 'remote 404 is reconciled without fatal error');

$pressure = $config;
$pressure['storage_usage_percent'] = 85;
$stats = asterisk_run_recording_retention($db, $pressure, $fetch, $missingDelete, $base);
$assert(!empty($stats['disk_pressure']), 'configured storage usage above 80 percent enables pressure mode');

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$assert(str_contains($source, "['READY', 'DISCARD_PENDING']"), 'DISCARD_PENDING stays playable during grace');
$assert(str_contains($source, "if (empty(\$call['recording_url']))"), 'legacy Nvoip recording fallback remains intact');
$assert(str_contains($source, "can('recordings')") && str_contains($source, "scoped_calls_clause('co', \$user)"), 'recording playback remains permission and tenant scoped');

echo "OK - {$tests} tests\n";
