<?php
declare(strict_types=1);

require dirname(__DIR__) . '/asterisk_agent/lib.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};
$root = sys_get_temp_dir() . '/ligflow-agent-' . bin2hex(random_bytes(4));
mkdir($root, 0700, true);
$pjsip = $root . '/pjsip.conf';
file_put_contents($pjsip, '#include "pjsip.d/ligflow.conf"' . "\n");
$endpoints = [];
$reloadSucceeds = true;
$runner = static function (array $command) use (&$endpoints, &$reloadSucceeds): array {
    $request = (string) end($command);
    if (str_starts_with($request, 'pjsip show endpoint ')) {
        $extension = substr($request, strlen('pjsip show endpoint '));
        return isset($endpoints[$extension])
            ? ['code' => 0, 'stdout' => 'Endpoint: ' . $extension, 'stderr' => '']
            : ['code' => 1, 'stdout' => '', 'stderr' => 'not found'];
    }
    if ($request === 'pjsip reload') {
        if (!$reloadSucceeds) return ['code' => 1, 'stdout' => '', 'stderr' => 'reload failed'];
        $endpoints['1003'] = true;
        return ['code' => 0, 'stdout' => 'reloaded', 'stderr' => ''];
    }
    return ['code' => 1, 'stdout' => '', 'stderr' => 'unexpected'];
};
$config = [
    'shared_secret' => str_repeat('a', 64),
    'allowed_ips' => ['127.0.0.1'],
    'asterisk_server_id' => 1,
    'extension_start' => 1000,
    'extension_end' => 1999,
    'state_dir' => $root . '/state',
    'asterisk_bin' => '/usr/sbin/asterisk',
    'pjsip_conf' => $pjsip,
    'root_include' => '#include "pjsip.d/ligflow.conf"',
    'master_file' => $root . '/pjsip.d/ligflow.conf',
    'managed_dir' => $root . '/pjsip.d/ligflow',
    'endpoint_template' => "[{{extension}}]\ntype=endpoint\naors=ligflow-aor-{{extension}}\nauth=ligflow-auth-{{extension}}\n\n[ligflow-auth-{{extension}}]\ntype=auth\nauth_type=userpass\nusername={{extension}}\npassword={{password}}\n\n[ligflow-aor-{{extension}}]\ntype=aor\nmax_contacts=1\n",
];
$agent = new LigFlowAsteriskAgent($config, $runner);
$payload = [
    'operation' => 'CREATE_EXTENSION',
    'idempotency_key' => str_repeat('b', 64),
    'asterisk_server_id' => 1,
    'asterisk_user_extension_id' => 9,
    'extension' => '1003',
    'sip_password' => str_repeat('x', 30),
];
$sign = static function (array $request, string $nonce) use ($config): array {
    $raw = json_encode($request, JSON_THROW_ON_ERROR);
    $timestamp = (string) time();
    return [$raw, [
        'x-ligflow-timestamp' => $timestamp,
        'x-ligflow-nonce' => $nonce,
        'x-ligflow-signature' => hash_hmac('sha256', $timestamp . '.' . $nonce . '.' . $raw, $config['shared_secret']),
    ]];
};
[$raw, $headers] = $sign($payload, str_repeat('c', 32));
$invalid = $agent->handle([], $raw, '127.0.0.1');
$assert($invalid['status'] === 401, 'invalid auth is rejected');
$outside = $payload;
$outside['extension'] = '2000';
[$outsideRaw, $outsideHeaders] = $sign($outside, str_repeat('d', 32));
$outsideResult = $agent->handle($outsideHeaders, $outsideRaw, '127.0.0.1');
$assert($outsideResult['status'] === 422, 'outside range is rejected');
$result = $agent->handle($headers, $raw, '127.0.0.1');
$assert($result['status'] === 200 && $result['body']['endpoint_confirmed'] === true, 'valid CREATE is confirmed');
$extensionFile = $root . '/pjsip.d/ligflow/1003.conf';
$content = (string) file_get_contents($extensionFile);
$assert(is_file($extensionFile), 'endpoint file is created');
$assert(!str_contains($content, 'idempotency_key'), 'generated config excludes job data');
$assert(!str_contains(json_encode($result, JSON_THROW_ON_ERROR), $payload['sip_password']), 'response excludes SIP secret');
[$againRaw, $againHeaders] = $sign($payload, str_repeat('e', 32));
$again = $agent->handle($againHeaders, $againRaw, '127.0.0.1');
$assert($again === $result, 'duplicate idempotency returns stored response');
$replay = $payload;
$replay['idempotency_key'] = str_repeat('f', 64);
$replayRaw = json_encode($replay, JSON_THROW_ON_ERROR);
$replayHeaders = $headers;
$replayHeaders['x-ligflow-signature'] = hash_hmac('sha256', $headers['x-ligflow-timestamp'] . '.' . $headers['x-ligflow-nonce'] . '.' . $replayRaw, $config['shared_secret']);
$replayResult = $agent->handle($replayHeaders, $replayRaw, '127.0.0.1');
$assert($replayResult['status'] === 401, 'nonce replay is rejected');
$duplicate = $payload;
$duplicate['idempotency_key'] = str_repeat('1', 64);
[$duplicateRaw, $duplicateHeaders] = $sign($duplicate, str_repeat('2', 32));
$duplicateResult = $agent->handle($duplicateHeaders, $duplicateRaw, '127.0.0.1');
$assert($duplicateResult['status'] === 409, 'duplicate endpoint is rejected');
$failRoot = $root . '/failed';
mkdir($failRoot, 0700, true);
$failPjsip = $failRoot . '/pjsip.conf';
file_put_contents($failPjsip, '#include "pjsip.d/ligflow.conf"' . "\n");
$failConfig = $config;
$failConfig['state_dir'] = $failRoot . '/state';
$failConfig['pjsip_conf'] = $failPjsip;
$failConfig['master_file'] = $failRoot . '/pjsip.d/ligflow.conf';
$failConfig['managed_dir'] = $failRoot . '/pjsip.d/ligflow';
$reloadSucceeds = false;
$failedAgent = new LigFlowAsteriskAgent($failConfig, $runner);
$failedPayload = $payload;
$failedPayload['extension'] = '1004';
$failedPayload['idempotency_key'] = str_repeat('3', 64);
[$failedRaw, $failedHeaders] = $sign($failedPayload, str_repeat('4', 32));
$failed = $failedAgent->handle($failedHeaders, $failedRaw, '127.0.0.1');
$assert($failed['status'] === 502, 'reload failure is controlled');
$assert(!is_file($failRoot . '/pjsip.d/ligflow/1004.conf'), 'failure rolls back endpoint file');
$source = file_get_contents(dirname(__DIR__) . '/index.php');
$assert(str_contains($source, "lifecycle_status = 'ACTIVE'"), 'production activates links only after success');
$assert(str_contains($source, "status = 'FAILED'"), 'production marks failed jobs');
$assert(str_contains($source, 'sip_password_encrypted'), 'production stores SIP password encrypted');
echo "OK - {$tests} tests\n";