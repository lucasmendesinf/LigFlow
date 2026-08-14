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

echo "OK - {$tests} tests\n";
