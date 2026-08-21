<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_routing.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$javascript = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js') ?: '';

$contactUpdate = strpos($source, "UPDATE contacts SET status = 'em_ligacao', attempts = attempts + 1");
$orphanReplay = strpos($source, 'asterisk_replay_orphan_events_for_call($callId', $contactUpdate === false ? 0 : $contactUpdate);
$assert($contactUpdate !== false && $orphanReplay !== false && $contactUpdate < $orphanReplay, 'orphan events are replayed only after the contact enters the active-call state');
$transactionStart = strpos($source, '$callPdo->beginTransaction();', $contactUpdate === false ? 0 : max(0, $contactUpdate - 3000));
$transactionCommit = strpos($source, '$callPdo->commit();', $transactionStart === false ? 0 : $transactionStart);
$assert($transactionStart !== false && $transactionCommit !== false && $transactionCommit < $orphanReplay, 'call and contact state become visible atomically before orphan events are replayed');
$assert(str_contains($source, "status='concluido', reserved_by=NULL")
    && str_contains($source, "status IN ('reservado','em_ligacao')"), 'an unanswered sequential Asterisk attempt releases its contact');
$assert(str_contains($source, 'asterisk_release_terminal_sequential_contact($persistedCall)'), 'a terminal event racing the call insert is reconciled before returning');
$assert(str_contains($source, '$staleSequentialCalls = rows('), 'previous terminal contacts are repaired when the dialer is rendered');
$assert(!str_contains($source, "\$originatePayload['callerId']")
    && !str_contains($source, "\$originateParallelPayload['callerId']"), 'Asterisk trunks keep their authorized endpoint caller ID');
$assert(str_contains($source, "strtolower(\$eventType) === 'channeldestroyed'")
    && str_contains($source, 'asterisk_terminal_event_cause($event)'), 'a late ChannelDestroyed event enriches the terminal failure cause');
$assert(str_contains($source, "SELECT c.*, ca.dialer_type FROM calls c JOIN campaigns ca"), 'the managed state endpoint accepts manual and campaign Asterisk calls');
$assert(str_contains($source, "'continue_auto' => \$continueAuto"), 'the backend explicitly authorizes automatic continuation after an unanswered terminal attempt');
$assert(str_contains($source, '$recentManagedAutoTerminalCall'), 'an immediate terminal attempt remains observable after the redirect');
$assert(str_contains($javascript, 'const shouldAdvanceAuto = isAutoDialing() && Boolean(state.continue_auto);'), 'automatic continuation depends on confirmed backend state');
$assert(str_contains($javascript, 'setTimeout(triggerManagedAutoAdvance, 700);'), 'the next lead starts after the terminal state is shown');
$assert(!str_contains($javascript, 'if (!usesManagedAsterisk() || !currentSipCallId || isAutoDialing()) return;'), 'managed Asterisk polling is not disabled during automatic dialing');
$assert(str_contains($javascript, 'if (managedCallId && usesManagedAsterisk()) {'), 'automatic and manual managed calls start the same ARI state polling');

$terminal = [
    'status' => 'no_answer',
    'internal_status' => 'nao_atendida',
    'finalized_at' => '2026-08-21 20:12:06',
];
$view = asterisk_manual_call_view_state($terminal);
$assert($view['active'] === false && $view['phase'] === 'failed', 'NOANSWER is terminal and cannot leave active call controls visible');

echo "OK - {$tests} tests\n";
