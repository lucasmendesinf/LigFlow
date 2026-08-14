<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$routing = file_get_contents(dirname(__DIR__) . '/asterisk_routing.php') ?: '';
$definition = strpos($source, 'function asterisk_replay_orphan_events_for_call');
$startCall = strpos($source, 'function start_call');
$postDispatch = strpos($source, "if (!defined('LIGFLOW_ARI_WORKER'))", $startCall === false ? 0 : $startCall);

$assert($definition !== false, 'orphan replay function is defined');
$assert($startCall !== false && $definition < $startCall, 'orphan replay is globally available before start_call');
$assert($postDispatch !== false && $definition < $postDispatch, 'POST dispatch cannot run before orphan replay is declared');
$assert(substr_count($source, 'function asterisk_replay_orphan_events_for_call') === 1, 'orphan replay has one definition');
$assert(str_contains($source, 'asterisk_replay_orphan_events_for_call($callId'), 'start_call invokes orphan replay after inserting the call');
$assert(str_contains($source, "'asterisk.orphan_replay_failed'"), 'replay failures are recorded without aborting the call');
$assert(str_contains($source, 'asterisk_handle_event($event, (int)$saved[\'id\'])'), 'replay processes the already persisted event atomically');
$assert(!str_contains(substr($source, $definition, $startCall - $definition), "DELETE FROM asterisk_ari_events"), 'replay never deletes an orphan before associating it');
$assert(str_contains($source, 'catch (Throwable $error)'), 'replay failures cannot escape as a fatal error');
$assert(str_contains($source, 'start_call($campaignId, $contactId, $agentId, $companyId);'), 'manual call delegates to the central start_call flow');

$startCallEnd = strpos($source, 'function get_or_create_manual_campaign', $startCall);
$startCallBody = $startCall !== false && $startCallEnd !== false ? substr($source, $startCall, $startCallEnd - $startCall) : '';
preg_match_all('/\b(asterisk_[a-zA-Z0-9_]+)\s*\(/', $startCallBody, $calls);
foreach (array_unique($calls[1] ?? []) as $function) {
    $defined = str_contains($source, 'function ' . $function . '(') || str_contains($routing, 'function ' . $function . '(');
    $assert($defined, $function . ' used by start_call must be defined or loaded');
}

echo "OK - {$tests} tests\n";
