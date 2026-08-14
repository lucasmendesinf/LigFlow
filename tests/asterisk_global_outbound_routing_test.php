<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$javascript = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js') ?: '';

$assert(str_contains($source, 'function campaign_uses_asterisk_outbound'), 'Asterisk outbound routing has one central campaign decision');
$assert(str_contains($source, 'if (campaign_uses_asterisk_outbound($campaign, $companyId))'), 'serial and parallel campaigns enter the Asterisk backend flow');
$assert(str_contains($source, 'return start_asterisk_parallel_batch($campaign, $agentId, $companyId);'), 'campaign Asterisk calls reuse the existing batch lifecycle');
$assert(str_contains($source, 'function campaign_uses_asterisk_parallelism'), 'parallel UI threshold remains separate from outbound routing');
$assert(str_contains($source, "campaign_requested_parallelism(\$campaign) > 1"), 'aggregate batch behavior remains restricted to simultaneous calls');

$assert(substr_count($source, 'name="action" value="manual_call"') >= 2, 'both floating webphones can submit manual calls to the backend');
$assert(substr_count($source, 'data-outbound-via-ari=') >= 3, 'agent, floating and SIP diagnostic surfaces receive the global route marker');
$assert(substr_count($javascript, "json.provider === 'ASTERISK' ? '1' : '0'") === 2, 'open agent and diagnostic screens refresh the global route marker');
$assert(str_contains($javascript, "['action', 'manual_call']"), 'SIP test submits through the same backend manual call action');
$assert(str_contains($javascript, 'form?.requestSubmit();'), 'manual webphone calls submit through the backend in Asterisk mode');
$assert(substr_count($javascript, "loaded.provider === 'ASTERISK'") >= 1, 'floating manual calls resolve the active provider at click time');
$assert(str_contains($javascript, "config.provider === 'ASTERISK'"), 'SIP diagnostics resolve the active provider at click time');
$assert(str_contains($javascript, 'HTMLFormElement.prototype.submit.call(form);'), 'Asterisk manual calls bypass direct browser SIP origination');
$assert(str_contains($source, "hash_file('sha256', __DIR__ . '/assets/nvoip-webphone.js')"), 'webphone asset uses content-based cache busting');

$assert(str_contains($source, 'public function connectSingleCall'), 'answered manual Asterisk calls connect the consultant to their bridge');
$assert(str_contains($source, 'else asterisk_single_call_answered((int)$call[\'id\']);'), 'ARI answer events connect non-batch calls');
$assert(str_contains($source, 'provider_consultant_channel_id'), 'consultant channel is persisted for controlled cleanup');
$assert(str_contains($source, "'endpoint' => 'PJSIP/' . \$endpoint"), 'consultant connection remains on the existing PJSIP/WebRTC endpoint');

$assert(str_contains($source, "? 'PJSIP/' . \$destination . '@' . \$trunk"), 'Nvoip originates through its selected endpoint');
$assert(str_contains($source, ": 'PJSIP/' . \$trunk . '/' . \$destination"), 'DirectCall keeps its existing endpoint syntax');
$assert(str_contains($source, "'LIGFLOW_TRUNK' => \$this->trunk()"), 'each originated call snapshots the selected global trunk');

echo "OK - {$tests} tests\n";
