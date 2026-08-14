<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$javascript = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js') ?: '';

$assert(str_contains($source, "SELECT * FROM asterisk_settings WHERE id = 1"), 'Asterisk configuration remains global');
$assert(str_contains($source, "'config_version' => substr(hash('sha256'"), 'global configuration exposes a revision');
$assert(substr_count($source, "'configVersion' => \$asterisk['config_version']") === 2, 'both telephony modes expose the global revision');
$assert(str_contains($javascript, "cache: 'no-store'"), 'webphone never caches global telephony configuration');
$assert(str_contains($javascript, 'service.session || currentSipCallId || connecting'), 'active calls are never reconfigured');
$assert(str_contains($javascript, 'isAutoDialing()'), 'active automatic batches are never reconfigured');
$assert(str_contains($javascript, 'previousVersion !== loaded.configVersion'), 'open user sessions detect global changes');
$assert(str_contains($javascript, 'window.setInterval(syncGlobalTelephonyConfig, 15000)'), 'open user sessions periodically synchronize');

echo "OK - {$tests} tests\n";
