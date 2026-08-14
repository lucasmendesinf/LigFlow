<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_routing.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$nvoip = ['active_route' => 'NVOIP_TRUNK', 'nvoip_trunk' => 'nvoip', 'directcall_trunk' => 'directcall'];
$directcall = ['active_route' => 'DIRECTCALL_TRUNK', 'nvoip_trunk' => 'nvoip', 'directcall_trunk' => 'directcall'];

$assert(asterisk_route_trunk($nvoip) === 'nvoip', 'NVOIP_TRUNK resolves to the nvoip PJSIP endpoint');
$assert(asterisk_outbound_endpoint($nvoip, '+55 (41) 99999-9999') === 'PJSIP/5541999999999@nvoip', 'NVOIP_TRUNK uses the registered endpoint syntax and digits only');
$assert(asterisk_outbound_endpoint($directcall, '+55 (41) 99999-9999') === 'PJSIP/directcall/5541999999999', 'DIRECTCALL_TRUNK resolves to the directcall PJSIP endpoint');

$snapshot = $nvoip;
$current = $directcall;
$assert(asterisk_route_trunk($snapshot) === 'nvoip', 'an initiated call keeps its Nvoip route snapshot');
$assert(asterisk_route_trunk($current) === 'directcall', 'a route change applies to a new call');

try {
    asterisk_route_trunk(['active_route' => 'UNKNOWN', 'nvoip_trunk' => 'nvoip', 'directcall_trunk' => 'directcall']);
    $assert(false, 'unknown routes must not silently fall back');
} catch (RuntimeException) {
    $assert(true, 'unknown routes do not silently fall back to DirectCall');
}

$index = file_get_contents(dirname(__DIR__) . '/index.php');
$javascript = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js');
$assert(str_contains($index, 'return asterisk_outbound_endpoint($this->config, $destination);'), 'provider reuses the central outbound endpoint resolver');
$assert(str_contains($index, '$dialString = $this->outboundEndpoint($destination)')
    && str_contains($index, "'endpoint' => \$this->outboundEndpoint(\$destination)"), 'single and parallel Asterisk originations use the same endpoint resolver');
$assert(str_contains($index, 'data-managed-asterisk=') && str_contains($index, 'name="action" value="manual_call"'), 'manual webphone posts through the managed Asterisk endpoint');
$assert(str_contains($javascript, 'usesManagedAsterisk() && !isAutoDialing()'), 'manual JavaScript bypasses direct browser SIP when Asterisk is active');

echo "OK - {$tests} tests\n";
