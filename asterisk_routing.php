<?php
declare(strict_types=1);

function asterisk_route_trunk(array $config): string
{
    $route = strtoupper(trim((string)($config['active_route'] ?? '')));
    $configKey = match ($route) {
        'NVOIP_TRUNK' => 'nvoip_trunk',
        'DIRECTCALL_TRUNK' => 'directcall_trunk',
        default => throw new RuntimeException('Rota Asterisk ativa invalida.'),
    };

    $trunk = trim((string)($config[$configKey] ?? ''));
    if ($trunk === '' || preg_match('/^[A-Za-z0-9_.-]+$/', $trunk) !== 1) {
        throw new RuntimeException('Endpoint do trunk Asterisk invalido para a rota ' . $route . '.');
    }

    return $trunk;
}

function asterisk_outbound_endpoint(array $config, string $destination): string
{
    $destination = preg_replace('/\D+/', '', $destination) ?? '';
    if ($destination === '') {
        throw new RuntimeException('Numero de destino Asterisk invalido.');
    }

    $trunk = asterisk_route_trunk($config);
    return strtoupper((string)($config['active_route'] ?? '')) === 'NVOIP_TRUNK'
        ? 'PJSIP/' . $destination . '@' . $trunk
        : 'PJSIP/' . $trunk . '/' . $destination;
}

function asterisk_ari_success_diagnostics(
    string $method,
    string $url,
    int $status,
    string|false $body,
    ?array $payload
): array {
    return [
        'method' => strtoupper($method),
        'url' => asterisk_ari_safe_url($url),
        'http_status' => $status,
        'response' => $body === false ? '' : $body,
        'curl_error' => '',
        'telephony_endpoint' => trim((string)($payload['endpoint'] ?? '')),
    ];
}

function asterisk_origin_returned_channel_id(array $response): string
{
    $channelId = trim((string)($response['id'] ?? ''));
    return preg_match('/^[A-Za-z0-9_.:-]+$/', $channelId) === 1 ? $channelId : '';
}

function asterisk_origin_confirmation_state(string $channelId, int $httpStatus, array $response = []): string
{
    if ($channelId === '') return 'rejected';
    if ($httpStatus === 404) return 'destroyed';
    if ($httpStatus < 200 || $httpStatus >= 300) return 'confirmation_failed';
    return asterisk_origin_returned_channel_id($response) === $channelId ? 'active' : 'confirmation_failed';
}

function asterisk_manual_call_view_state(array $call): array
{
    $status = strtolower(trim((string)($call['status'] ?? '')));
    $internal = strtolower(trim((string)($call['internal_status'] ?? '')));
    $terminal = !empty($call['finalized_at']) || in_array($status, ['completed', 'failed', 'cancelled', 'busy', 'no_answer', 'invalid_number'], true);
    if ($terminal) {
        $failed = in_array($status, ['failed', 'busy', 'no_answer', 'invalid_number'], true);
        return ['active' => false, 'phase' => $failed ? 'failed' : 'ended', 'label' => $failed ? 'Falha' : 'Encerrada'];
    }
    if (!empty($call['connected_at']) || in_array($internal, ['conectada'], true)) {
        return ['active' => true, 'phase' => 'connected', 'label' => 'Em ligacao'];
    }
    if (!empty($call['answered_at']) || in_array($status, ['answered'], true) || $internal === 'atendida') {
        return ['active' => true, 'phase' => 'answered', 'label' => 'Atendida'];
    }
    if ($status === 'ringing' || $internal === 'chamando') {
        return ['active' => true, 'phase' => 'ringing', 'label' => 'Chamando'];
    }
    return ['active' => true, 'phase' => 'originating', 'label' => 'Iniciando chamada'];
}

function asterisk_origin_has_timed_out(array $call, int $now, int $timeoutSeconds = 15): bool
{
    $status = strtolower(trim((string)($call['status'] ?? '')));
    if (!empty($call['answered_at']) || !empty($call['finalized_at']) || $status === 'ringing') {
        return false;
    }
    $anchor = strtotime((string)($call['last_event_at'] ?? $call['created_at'] ?? $call['started_at'] ?? ''));
    return $anchor !== false && ($now - $anchor) >= max(1, $timeoutSeconds);
}

final class AsteriskAriRequestException extends RuntimeException
{
    public function __construct(string $message, private array $diagnostics)
    {
        parent::__construct($message);
    }

    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}

function asterisk_ari_safe_url(string $url): string
{
    return preg_replace('#://[^/@]+@#', '://***@', $url) ?? $url;
}

function asterisk_ari_failure(
    string $method,
    string $url,
    int $status,
    string|false $body,
    string $curlError,
    ?array $payload
): AsteriskAriRequestException {
    $response = $body === false ? '' : $body;
    $endpoint = trim((string)($payload['endpoint'] ?? ''));
    $diagnostics = [
        'method' => strtoupper($method),
        'url' => asterisk_ari_safe_url($url),
        'http_status' => $status,
        'response' => $response,
        'curl_error' => $curlError,
        'telephony_endpoint' => $endpoint,
    ];
    $parts = [
        'metodo=' . $diagnostics['method'],
        'url=' . $diagnostics['url'],
        'HTTP=' . ($status > 0 ? (string)$status : 'sem resposta'),
        'resposta=' . ($response !== '' ? $response : '<vazia>'),
        'cURL=' . ($curlError !== '' ? $curlError : '<sem erro>'),
    ];
    if ($endpoint !== '') $parts[] = 'endpoint=' . $endpoint;

    return new AsteriskAriRequestException('ARI: ' . implode('; ', $parts), $diagnostics);
}
