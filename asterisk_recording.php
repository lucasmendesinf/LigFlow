<?php
declare(strict_types=1);

function asterisk_bridge_recording_name(int $companyId, int $callId, ?string $uniqueSuffix = null): string
{
    if ($companyId < 1 || $callId < 1) {
        throw new InvalidArgumentException('Empresa e chamada sao obrigatorias para nomear a gravacao.');
    }
    $suffix = strtolower($uniqueSuffix ?? bin2hex(random_bytes(8)));
    if (preg_match('/^[a-f0-9]{16}$/', $suffix) !== 1) {
        throw new InvalidArgumentException('Identificador unico da gravacao invalido.');
    }
    return sprintf('ligflow_%s_company-%d_call-%d_%s', gmdate('Ymd'), $companyId, $callId, $suffix);
}

function asterisk_bridge_recording_request(string $bridgeId, string $recordingName, string $format = 'wav'): array
{
    if (preg_match('/^[A-Za-z0-9_.-]{1,128}$/', $bridgeId) !== 1 || str_contains($bridgeId, '..')) {
        throw new InvalidArgumentException('Bridge Asterisk invalido para gravacao.');
    }
    if (preg_match('/^[A-Za-z0-9_-]{1,180}$/', $recordingName) !== 1 || str_contains($recordingName, '..')) {
        throw new InvalidArgumentException('Nome de gravacao Asterisk invalido.');
    }
    if ($format !== 'wav') {
        throw new InvalidArgumentException('Formato de gravacao Asterisk invalido.');
    }
    $query = http_build_query([
        'name' => $recordingName,
        'format' => $format,
        'ifExists' => 'fail',
        'beep' => 'false',
        'terminateOn' => 'none',
    ], '', '&', PHP_QUERY_RFC3986);
    return [
        'method' => 'POST',
        'path' => '/bridges/' . rawurlencode($bridgeId) . '/record?' . $query,
        'recording_name' => $recordingName,
        'filename' => $recordingName . '.' . $format,
        'format' => $format,
    ];
}

function asterisk_record_bridge_ari(callable $request, array $config, string $bridgeId, string $recordingName, string $format = 'wav'): array
{
    $definition = asterisk_bridge_recording_request($bridgeId, $recordingName, $format);
    $response = $request($config, $definition['method'], $definition['path'], null);
    return $definition + ['response' => is_array($response) ? $response : []];
}

function asterisk_try_winner_bridge_recording(callable $record, array $call, string $bridgeId): array
{
    if ((string)($call['race_outcome'] ?? '') !== 'WINNER') {
        return ['started' => false, 'skipped' => 'not_winner'];
    }
    if ($bridgeId === '') {
        return ['started' => false, 'skipped' => 'invalid_bridge'];
    }
    try {
        $result = $record($bridgeId, asterisk_bridge_recording_name((int)($call['company_id'] ?? 0), (int)($call['id'] ?? 0)), 'wav');
        return ['started' => true, 'result' => is_array($result) ? $result : []];
    } catch (Throwable $e) {
        return ['started' => false, 'error' => $e->getMessage()];
    }
}
