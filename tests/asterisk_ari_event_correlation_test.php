<?php
declare(strict_types=1);

define('LIGFLOW_ARI_WORKER', true);
require_once dirname(__DIR__) . '/index.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$channelId = 'ARI-race-123';
$destroyed = [
    'type' => 'ChannelDestroyed',
    'channel' => ['id' => $channelId, 'linkedid' => 'linked-123', 'cause' => 17, 'cause_txt' => 'User busy'],
];
$dial = [
    'type' => 'Dial',
    'caller' => ['id' => $channelId],
    'peer' => ['id' => 'PJSIP-peer-123'],
    'dialstatus' => 'BUSY',
];

$assert(asterisk_event_references_channel($destroyed, $channelId), 'destroyed event is correlated by nested channel id');
$assert(in_array('linked-123', asterisk_event_identifiers($destroyed), true), 'linked id is available for correlation');
$assert(asterisk_terminal_event_cause($destroyed) === 'ChannelDestroyed: cause=17; cause_txt=User busy', 'hangup cause is preserved');
[$status, $internal, $terminal] = asterisk_event_transition($dial, ['answered_at' => null, 'internal_status' => 'iniciada']);
$assert($status === 'busy' && $internal === 'ocupado' && $terminal, 'terminal Dial result finalizes the call');
$assert(!asterisk_event_references_channel(['type' => 'ApplicationReplaced'], $channelId), 'application lifecycle events are not attached to a call');

echo "OK - {$tests} tests\n";
