<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_routing.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$created = ['id' => 'ARI-returned-123', 'state' => 'Down'];
$returnedId = asterisk_origin_returned_channel_id($created);
$assert($returnedId === 'ARI-returned-123', 'the Asterisk response ID is used verbatim');
$assert(asterisk_origin_confirmation_state($returnedId, 200, $created) === 'active', 'a created and confirmed channel remains active');
$assert(asterisk_origin_confirmation_state($returnedId, 404) === 'destroyed', 'a channel destroyed before the first GET is terminal');
$assert(asterisk_origin_confirmation_state('', 500) === 'rejected', 'a rejected origin without a returned channel ID is not queried');
$assert(asterisk_origin_confirmation_state($returnedId, 200, ['id' => 'ARI-other']) === 'confirmation_failed', 'a mismatched confirmation cannot replace the returned channel ID');
$assert(asterisk_origin_returned_channel_id(['id' => '../invalid']) === '', 'unsafe channel IDs are rejected');

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$javascript = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js') ?: '';
$postId = strpos($source, '$channelId = asterisk_origin_returned_channel_id($channel)');
$emptyGuard = strpos($source, "if (\$channelId === '')", $postId === false ? 0 : $postId);
$confirmationGet = strpos($source, "'/channels/' . rawurlencode(\$channelId)", $postId === false ? 0 : $postId);
$assert($postId !== false && $emptyGuard !== false && $confirmationGet !== false && $emptyGuard < $confirmationGet, 'GET confirmation only runs after POST returns a valid channel ID');
$assert(str_contains($source, "'requested_channel_id' => \$externalId") && str_contains($source, "'returned_channel_id' => \$channelId"), 'requested and returned IDs are diagnosed separately');
$assert(str_contains($source, 'asterisk_replay_orphan_events_for_call((int)$call[\'id\'], $channelId)'), 'orphan events are replayed before a missing channel is finalized');
$assert(str_contains($source, 'asterisk_terminal_event_for_channel((int)$call[\'id\'], $channelId)'), 'a terminal event is consulted when the channel disappeared');
$assert(str_contains($source, 'ARI_EVENT_CONSUMER_REPLACED') && str_contains($source, 'ARI_TERMINAL_CAUSE_UNAVAILABLE'), 'missing terminal events produce a technical consumer diagnosis');
$assert(str_contains($javascript, "if (!state.active) {") && str_contains($javascript, 'toggleStopCallButtons(false)'), 'terminal backend state disables call controls');
$assert(str_contains($javascript, "document.querySelectorAll('[data-live-call-timer]').forEach((timer) => timer.remove())"), 'terminal backend state removes the call timer');
$assert(str_contains($javascript, "root.querySelector('[data-webphone]')?.classList.remove('is-hidden')"), 'manual webphone remains visible after a terminal failure');

$worker = file_get_contents(dirname(__DIR__) . '/asterisk_ari_worker.php') ?: '';
$assert(str_contains($worker, 'LOCK_EX | LOCK_NB'), 'ARI worker enforces a single persistent consumer');
$assert(str_contains($worker, '$socket->timedOut()'), 'idle WebSocket reads do not reconnect and replace the application subscription');

echo "OK - {$tests} tests\n";
