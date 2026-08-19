<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_routing.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$failure = asterisk_ari_failure(
    'post',
    'http://secret-user:secret-pass@127.0.0.1:8088/ari/channels',
    500,
    '{"message":"Allocation failed"}',
    '',
    [
        'endpoint' => 'PJSIP/5541999999999@nvoip',
        'app' => 'ligflow',
        'channelId' => 'ARI-test-channel',
    ]
);
$diagnostics = $failure->diagnostics();

$assert($failure instanceof AsteriskAriRequestException, 'ARI failures use a structured exception');
$assert(($diagnostics['method'] ?? '') === 'POST', 'HTTP method is retained');
$assert(($diagnostics['http_status'] ?? 0) === 500, 'HTTP status is retained');
$assert(($diagnostics['response'] ?? '') === '{"message":"Allocation failed"}', 'complete Asterisk response is retained');
$assert(($diagnostics['curl_error'] ?? 'x') === '', 'empty cURL error does not replace the response');
$assert(($diagnostics['telephony_endpoint'] ?? '') === 'PJSIP/5541999999999@nvoip', 'telephony endpoint is retained');
$assert(($diagnostics['stasis_app'] ?? '') === 'ligflow', 'Stasis application is retained');
$assert(($diagnostics['provider_channel_id'] ?? '') === 'ARI-test-channel', 'requested provider channel ID is retained');
$assert(!str_contains((string)$diagnostics['url'], 'secret-user') && !str_contains((string)$diagnostics['url'], 'secret-pass'), 'ARI credentials are hidden from the URL');
$assert(str_contains($failure->getMessage(), 'HTTP=500') && str_contains($failure->getMessage(), 'Allocation failed'), 'visible error is no longer generic');

$index = file_get_contents(dirname(__DIR__) . '/index.php');
$assert(str_contains($index, 'throw asterisk_ari_failure($method, $url, $status, $body, $error, $payload);'), 'runtime ARI requests preserve sanitized failure diagnostics');

echo "OK - {$tests} tests\n";
