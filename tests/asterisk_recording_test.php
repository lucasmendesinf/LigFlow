<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_recording.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$name = asterisk_bridge_recording_name(12, 845, '0123456789abcdef');
$assert(preg_match('/^ligflow_[0-9]{8}_company-12_call-845_0123456789abcdef$/', $name) === 1, 'recording name is safe and contains no personal data');
$assert(asterisk_bridge_recording_name(12, 845) === asterisk_bridge_recording_name(12, 845), 'recording name is deterministic for idempotency');
$assert(asterisk_recording_call_id_from_name($name) === 845, 'call id is correlated from the safe recording name');
$assert(asterisk_recording_call_id_from_name('../invalid') === 0, 'invalid recording name is not correlated');
$assert(asterisk_recording_claim_key(845, $name) === asterisk_recording_claim_key(845, $name), 'recording request claim is deterministic');

$captured = [];
$mockRequest = static function (array $config, string $method, string $path, ?array $payload) use (&$captured): array {
    $captured = compact('config', 'method', 'path', 'payload');
    return ['name' => 'mock-recording', 'state' => 'recording'];
};
$request = asterisk_record_bridge_ari($mockRequest, ['ari_url' => 'http://127.0.0.1:8088/ari'], 'ligflow-bridge-1', $name);
$assert($captured['method'] === 'POST', 'bridge recording uses POST');
$assert(str_starts_with($captured['path'], '/bridges/ligflow-bridge-1/record?'), 'bridge recording uses ARI bridge record endpoint');
parse_str((string)parse_url($captured['path'], PHP_URL_QUERY), $query);
$assert(($query['name'] ?? '') === $name && ($query['format'] ?? '') === 'wav', 'ARI request contains safe name and wav format');
$assert($captured['payload'] === null, 'ARI bridge recording uses query parameters');
$assert(($request['filename'] ?? '') === $name . '.wav', 'expected wav filename is returned');

foreach (['', '../bridge', 'bridge/other'] as $invalidBridge) {
    try {
        asterisk_bridge_recording_request($invalidBridge, $name);
        $assert(false, 'invalid bridge must fail');
    } catch (InvalidArgumentException) {
        $assert(true, 'invalid bridge is rejected');
    }
}
foreach (['../recording', 'lead phone 55419999', 'recording.wav'] as $invalidName) {
    try {
        asterisk_bridge_recording_request('ligflow-bridge-1', $invalidName);
        $assert(false, 'invalid recording name must fail');
    } catch (InvalidArgumentException) {
        $assert(true, 'invalid recording name is rejected');
    }
}

$recordCalls = 0;
$record = static function () use (&$recordCalls): array { $recordCalls++; return ['ok' => true]; };
$loser = asterisk_try_winner_bridge_recording($record, ['id' => 10, 'company_id' => 2, 'race_outcome' => 'LOSER'], 'ligflow-bridge-1');
$assert($recordCalls === 0 && ($loser['skipped'] ?? '') === 'not_winner', 'loser never starts recording');
$invalid = asterisk_try_winner_bridge_recording($record, ['id' => 10, 'company_id' => 2, 'race_outcome' => 'WINNER'], '');
$assert($recordCalls === 0 && ($invalid['skipped'] ?? '') === 'invalid_bridge', 'winner without bridge never starts recording');
$winner = asterisk_try_winner_bridge_recording($record, ['id' => 10, 'company_id' => 2, 'race_outcome' => 'WINNER'], 'ligflow-bridge-1');
$assert($recordCalls === 1 && !empty($winner['started']), 'winner with valid bridge starts one recording');
$staleCall = ['id' => 11, 'company_id' => 2, 'race_outcome' => 'PENDING'];
$winnerFromAtomicUpdate = asterisk_try_winner_bridge_recording($record, array_replace($staleCall, ['race_outcome' => 'WINNER']), 'ligflow-bridge-2');
$assert($recordCalls === 2 && !empty($winnerFromAtomicUpdate['started']), 'atomic winner result overrides stale call snapshot');

$failure = asterisk_try_winner_bridge_recording(
    static function (): never { throw new RuntimeException('mock ARI failure'); },
    ['id' => 10, 'company_id' => 2, 'race_outcome' => 'WINNER'],
    'ligflow-bridge-1'
);
$assert(empty($failure['started']) && ($failure['error'] ?? '') === 'mock ARI failure', 'ARI failure is contained and does not escape into call flow');

$connectedManual = ['id' => 20, 'company_id' => 2, 'status' => 'connected', 'connected_at' => '2026-08-21 12:00:00', 'provider_bridge_id' => 'bridge-manual'];
$manual = asterisk_try_connected_bridge_recording($record, $connectedManual, 'bridge-manual');
$assert(!empty($manual['started']), 'connected manual call starts recording');
$parallelLoser = asterisk_try_connected_bridge_recording($record, $connectedManual + ['dial_batch_id' => 9, 'race_outcome' => 'LOSER'], 'bridge-manual');
$assert(($parallelLoser['skipped'] ?? '') === 'not_winner', 'connected parallel loser never starts recording');
$parallelWinner = asterisk_try_connected_bridge_recording($record, array_replace($connectedManual, ['dial_batch_id' => 9, 'race_outcome' => 'WINNER']), 'bridge-manual');
$assert(!empty($parallelWinner['started']), 'connected parallel winner starts recording');
$notConnected = asterisk_try_connected_bridge_recording($record, array_replace($connectedManual, ['status' => 'answered', 'connected_at' => null]), 'bridge-manual');
$assert(($notConnected['skipped'] ?? '') === 'not_connected', 'answered call without consultant bridge is not recorded');

$startedEvent = asterisk_recording_event_summary([
    'type' => 'RecordingStarted',
    'timestamp' => '2026-08-21T12:00:00.000Z',
    'recording' => ['name' => $name, 'format' => 'wav', 'state' => 'recording'],
]);
$assert(($startedEvent['call_id'] ?? 0) === 845 && ($startedEvent['status'] ?? '') === 'RECORDING', 'RecordingStarted is correlated without event.channel');
$finishedEvent = asterisk_recording_event_summary(['type' => 'RecordingFinished', 'recording' => ['name' => $name, 'format' => 'wav']]);
$assert(($finishedEvent['status'] ?? '') === 'READY', 'RecordingFinished is normalized');
$failedEvent = asterisk_recording_event_summary(['type' => 'RecordingFailed', 'cause_txt' => 'mock', 'recording' => ['name' => $name, 'format' => 'wav']]);
$assert(($failedEvent['status'] ?? '') === 'FAILED' && ($failedEvent['cause'] ?? '') === 'mock', 'RecordingFailed is normalized');

$assert(asterisk_stored_recording_path($name) === '/recordings/stored/' . $name, 'stored recording metadata path is safe');
$assert(asterisk_stored_recording_path($name, true) === '/recordings/stored/' . $name . '/file', 'stored recording file path is safe');

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE call_recordings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, company_id INTEGER NOT NULL, call_id INTEGER NOT NULL UNIQUE,
    recording_name TEXT NOT NULL UNIQUE, format TEXT NOT NULL, status TEXT NOT NULL,
    duration_seconds REAL, size_bytes INTEGER, started_at TEXT, finished_at TEXT,
    failure_reason TEXT, discard_eligible_at TEXT, discarded_at TEXT,
    retention_until TEXT, last_cleanup_error TEXT, created_at TEXT, updated_at TEXT
)");
$call = ['id' => 845, 'company_id' => 12, 'dial_batch_id' => null, 'race_outcome' => null];
$assert(asterisk_persist_recording_event($db, $startedEvent, $call), 'RecordingStarted creates the definitive relation');
$row = $db->query('SELECT * FROM call_recordings')->fetch(PDO::FETCH_ASSOC);
$assert(($row['status'] ?? '') === 'RECORDING' && (int)$row['company_id'] === 12, 'recording is linked to the call tenant');
$tenantDenied = (int)$db->query('SELECT COUNT(*) FROM call_recordings WHERE company_id = 99 AND call_id = 845')->fetchColumn();
$assert($tenantDenied === 0, 'another tenant cannot resolve the recording relation');

$finishedEvent['duration_seconds'] = 19.2;
$finishedEvent['size_bytes'] = 307244;
$assert(asterisk_persist_recording_event($db, $finishedEvent, $call), 'RecordingFinished updates the relation');
$assert(asterisk_persist_recording_event($db, $finishedEvent, $call), 'duplicate RecordingFinished remains idempotent');
$row = $db->query('SELECT * FROM call_recordings')->fetch(PDO::FETCH_ASSOC);
$assert(($row['status'] ?? '') === 'READY' && (int)$row['size_bytes'] === 307244, 'finished recording is READY with metadata');
$assert((int)$db->query('SELECT COUNT(*) FROM call_recordings')->fetchColumn() === 1, 'duplicate event does not duplicate the recording');

$loserName = asterisk_bridge_recording_name(12, 846, 'fedcba9876543210');
$loserSummary = asterisk_recording_event_summary(['type' => 'RecordingStarted', 'recording' => ['name' => $loserName, 'format' => 'wav']]);
$loserCall = ['id' => 846, 'company_id' => 12, 'dial_batch_id' => 9, 'race_outcome' => 'LOSER'];
$assert(!asterisk_persist_recording_event($db, $loserSummary, $loserCall), 'parallel loser never creates a recording row');
$assert((int)$db->query('SELECT COUNT(*) FROM call_recordings')->fetchColumn() === 1, 'loser did not change persisted recordings');

$failedName = asterisk_bridge_recording_name(13, 847, '1111111111111111');
$failedSummary = asterisk_recording_event_summary(['type' => 'RecordingFailed', 'cause_txt' => "falha\ncontrolada", 'recording' => ['name' => $failedName, 'format' => 'wav']]);
$assert(asterisk_persist_recording_event($db, $failedSummary, ['id' => 847, 'company_id' => 13]), 'RecordingFailed creates a failed relation');
$failedRow = $db->query('SELECT * FROM call_recordings WHERE call_id = 847')->fetch(PDO::FETCH_ASSOC);
$assert(($failedRow['status'] ?? '') === 'FAILED' && !str_contains((string)$failedRow['failure_reason'], "\n"), 'failure is persisted and sanitized');

$wav = fopen('php://temp', 'w+b');
$header = 'RIFF' . pack('V', 16036) . 'WAVEfmt ' . pack('VvvVVvv', 16, 1, 1, 8000, 16000, 2, 16) . 'data' . pack('V', 16000);
fwrite($wav, $header . str_repeat("\0", 16000));
$assert(asterisk_wav_duration_from_stream($wav) === 1.0, 'WAV duration is derived without exposing the file path');
fclose($wav);

echo "OK - {$tests} tests\n";
