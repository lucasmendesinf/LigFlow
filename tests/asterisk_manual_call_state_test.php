<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/asterisk_routing.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$originating = ['status' => 'calling_origin', 'internal_status' => 'iniciada', 'created_at' => '2026-08-14 12:00:00'];
$ringing = $originating + ['ringing_at' => '2026-08-14 12:00:04'];
$ringing['status'] = 'ringing';
$ringing['internal_status'] = 'chamando';
$answered = $ringing;
$answered['status'] = 'answered';
$answered['internal_status'] = 'atendida';
$answered['answered_at'] = '2026-08-14 12:00:08';
$failed = $originating;
$failed['status'] = 'failed';
$failed['internal_status'] = 'falha';
$failed['finalized_at'] = '2026-08-14 12:00:15';

$assert(asterisk_manual_call_view_state($originating)['phase'] === 'originating', 'accepted origination is not shown as an active conversation');
$assert(asterisk_manual_call_view_state($ringing)['phase'] === 'ringing', 'ringing is shown only after a confirmed transition');
$assert(asterisk_manual_call_view_state($answered)['phase'] === 'answered', 'answered state enables the in-call UI');
$assert(asterisk_manual_call_view_state($failed)['active'] === false, 'failed calls do not keep active controls');
$assert(!asterisk_origin_has_timed_out($originating, strtotime('2026-08-14 12:00:14'), 15), 'origin stays pending before 15 seconds');
$assert(asterisk_origin_has_timed_out($originating, strtotime('2026-08-14 12:00:15'), 15), 'origin expires after 15 seconds without progress');
$assert(!asterisk_origin_has_timed_out($ringing, strtotime('2026-08-14 12:01:00'), 15), 'a real ringing state is not treated as missing-channel timeout');

$diagnostics = asterisk_ari_success_diagnostics(
    'post',
    'http://user:password@127.0.0.1:8088/ari/channels',
    200,
    '{"id":"ARI-real","state":"Down"}',
    ['endpoint' => 'PJSIP/5541999999999@nvoip']
);
$assert($diagnostics['http_status'] === 200 && $diagnostics['response'] !== '', 'successful ARI response status and full body are retained');
$assert(!str_contains($diagnostics['url'], 'password'), 'successful diagnostics hide credentials');

$index = file_get_contents(dirname(__DIR__) . '/index.php');
$javascript = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js');
$assert(str_contains($index, "'status' => 'calling_origin'"), 'manual origin starts in the originating state');
$assert(str_contains($index, "datetime('now'), NULL, ?"), 'manual origin does not fabricate ringing_at');
$assert(str_contains($index, "'/channels/' . rawurlencode(\$channelId)"), 'originated channel is confirmed through ARI');
$assert(str_contains($index, 'asterisk_replay_orphan_events_for_call($callId'), 'events received before the call insert are replayed by real channel ID');
$assert(str_contains($javascript, 'page=asterisk_call_state'), 'managed Asterisk UI polls the backend channel state');
$assert(str_contains($javascript, "if (state.call || !usesManagedAsterisk())"), 'REGISTERED is not reused as managed call state');
$assert(str_contains($index, 'function get_active_manual_call(')
    && str_contains($index, "ca.dialer_type='manual'"), 'manual calls have an isolated active-call query');
$assert(str_contains($index, 'function get_active_campaign_call(')
    && str_contains($index, "COALESCE(ca.dialer_type,'')<>'manual'"), 'campaign panels exclude manual calls');
$assert(str_contains($index, '$campaign = get_or_create_manual_campaign($companyId, $agentId);'), 'manual calls never reuse the selected operation campaign');
$assert(str_contains($index, "VALUES (?, ?, 'Ligacao manual', ?, ?, 'Manual', 'novo')"), 'manual call technical contact is not reserved as a campaign lead');
$assert(str_contains($index, 'connectManualCall($call, $agent)'), 'answered manual calls connect the registered consultant endpoint to the ARI bridge');
$assert(str_contains($index, "ASTERISK_MANUAL_CONSULTANT_ORIGINATED")
    && str_contains($index, "['connected', 'conectada', false]"), 'manual UI becomes connected only after the consultant channel reaches Up');
$assert(str_contains($javascript, "?page=asterisk_manual_hangup"), 'the floating manual UI controls the real Asterisk call');
$assert(str_contains($javascript, "data-managed-call-id")
    && str_contains($javascript, "managedCallPhase === 'answered' || managedCallPhase === 'connected'"), 'manual timer starts only after a confirmed answer');
$assert(str_contains($javascript, 'HTMLFormElement.prototype.submit.call(form)'), 'managed manual call stays backend-originated after webphone registration');

echo "OK - {$tests} tests\n";
