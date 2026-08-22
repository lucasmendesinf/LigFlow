<?php
declare(strict_types=1);

function asterisk_bridge_recording_name(int $companyId, int $callId, ?string $uniqueSuffix = null): string
{
    if ($companyId < 1 || $callId < 1) {
        throw new InvalidArgumentException('Empresa e chamada sao obrigatorias para nomear a gravacao.');
    }
    $suffix = strtolower($uniqueSuffix ?? substr(hash('sha256', 'ligflow-recording|' . $companyId . '|' . $callId), 0, 16));
    if (preg_match('/^[a-f0-9]{16}$/', $suffix) !== 1) {
        throw new InvalidArgumentException('Identificador unico da gravacao invalido.');
    }
    return sprintf('ligflow_%s_company-%d_call-%d_%s', gmdate('Ymd'), $companyId, $callId, $suffix);
}

function asterisk_recording_call_id_from_name(string $recordingName): int
{
    if (preg_match('/^ligflow_[0-9]{8}_company-[1-9][0-9]*_call-([1-9][0-9]*)_[a-f0-9]{16}$/', $recordingName, $matches) !== 1) {
        return 0;
    }
    return (int)$matches[1];
}

function asterisk_recording_claim_key(int $callId, string $recordingName): string
{
    if ($callId < 1 || asterisk_recording_call_id_from_name($recordingName) !== $callId) {
        throw new InvalidArgumentException('Chamada invalida para controle idempotente da gravacao.');
    }
    return hash('sha256', 'asterisk-recording-request|' . $callId . '|' . $recordingName);
}

function asterisk_recording_event_summary(array $event): array
{
    $type = (string)($event['type'] ?? '');
    $statuses = [
        'RecordingStarted' => 'RECORDING',
        'RecordingFinished' => 'READY',
        'RecordingFailed' => 'FAILED',
    ];
    if (!isset($statuses[$type]) || !is_array($event['recording'] ?? null)) {
        return [];
    }
    $recording = $event['recording'];
    $name = trim((string)($recording['name'] ?? ''));
    $callId = asterisk_recording_call_id_from_name($name);
    if ($callId < 1) {
        return [];
    }
    $duration = $recording['duration_seconds'] ?? $recording['duration'] ?? null;
    $size = $recording['size_bytes'] ?? $recording['size'] ?? null;
    return [
        'event_type' => $type,
        'recording_name' => $name,
        'status' => $statuses[$type],
        'timestamp' => trim((string)($event['timestamp'] ?? gmdate(DATE_ATOM))),
        'call_id' => $callId,
        'format' => trim((string)($recording['format'] ?? 'wav')),
        'cause' => trim((string)($event['cause'] ?? $event['cause_txt'] ?? '')),
        'duration_seconds' => is_numeric($duration) ? max(0, (float)$duration) : null,
        'size_bytes' => is_numeric($size) ? max(0, (int)$size) : null,
    ];
}

function asterisk_recording_safe_name(string $recordingName): string
{
    $recordingName = trim($recordingName);
    if (asterisk_recording_call_id_from_name($recordingName) < 1 || preg_match('/^[A-Za-z0-9_-]{1,180}$/', $recordingName) !== 1) {
        throw new InvalidArgumentException('Nome de gravacao Asterisk invalido.');
    }
    return $recordingName;
}

function asterisk_stored_recording_path(string $recordingName, bool $file = false): string
{
    $recordingName = asterisk_recording_safe_name($recordingName);
    return '/recordings/stored/' . rawurlencode($recordingName) . ($file ? '/file' : '');
}

function asterisk_recording_metadata(array $recording, array $fallback = []): array
{
    $duration = $recording['duration_seconds'] ?? $recording['duration'] ?? $fallback['duration_seconds'] ?? null;
    $size = $recording['size_bytes'] ?? $recording['size'] ?? $fallback['size_bytes'] ?? null;
    return [
        'duration_seconds' => is_numeric($duration) ? max(0, (float)$duration) : null,
        'size_bytes' => is_numeric($size) ? max(0, (int)$size) : null,
    ];
}

function asterisk_recording_failure_reason(string $reason): ?string
{
    $reason = trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $reason) ?? '');
    if ($reason === '') return null;
    return substr($reason, 0, 500);
}

function asterisk_recording_retention_config(array $overrides = []): array
{
    $envInt = static function (string $name, int $default, int $minimum, int $maximum): int {
        $value = getenv($name);
        $number = $value !== false && trim((string)$value) !== '' ? (int)$value : $default;
        return max($minimum, min($maximum, $number));
    };
    $usage = getenv('ASTERISK_RECORDING_STORAGE_USAGE_PERCENT');
    $config = [
        'short_threshold_seconds' => $envInt('ASTERISK_RECORDING_SHORT_THRESHOLD_SECONDS', 5, 1, 60),
        'discard_grace_hours' => $envInt('ASTERISK_RECORDING_DISCARD_GRACE_HOURS', 24, 1, 720),
        'retention_days' => $envInt('ASTERISK_RECORDING_RETENTION_DAYS', 90, 1, 3650),
        'disk_threshold_percent' => $envInt('ASTERISK_RECORDING_DISK_THRESHOLD_PERCENT', 80, 1, 99),
        'batch_size' => $envInt('ASTERISK_RECORDING_RETENTION_BATCH_SIZE', 25, 1, 500),
        'storage_usage_percent' => $usage !== false && trim((string)$usage) !== '' ? max(0, min(100, (float)$usage)) : null,
    ];
    return array_replace($config, $overrides);
}

function asterisk_recording_retention_policy(float $durationSeconds, ?string $finishedAt, array $config, ?DateTimeImmutable $now = null): array
{
    $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
    try {
        $base = $finishedAt ? new DateTimeImmutable($finishedAt, new DateTimeZone('UTC')) : $now;
        $base = $base->setTimezone(new DateTimeZone('UTC'));
    } catch (Throwable) {
        $base = $now;
    }
    if ($durationSeconds < (float)$config['short_threshold_seconds']) {
        return [
            'status' => 'DISCARD_PENDING',
            'discard_eligible_at' => $base->modify('+' . (int)$config['discard_grace_hours'] . ' hours')->format('Y-m-d H:i:s'),
            'retention_until' => null,
        ];
    }
    return [
        'status' => 'READY',
        'discard_eligible_at' => null,
        'retention_until' => $base->modify('+' . (int)$config['retention_days'] . ' days')->format('Y-m-d H:i:s'),
    ];
}

function asterisk_recording_apply_retention_policy(PDO $pdo, array $recording, array $config, ?DateTimeImmutable $now = null): array
{
    if (!in_array((string)($recording['status'] ?? ''), ['READY', 'DISCARD_PENDING'], true) || !is_numeric($recording['duration_seconds'] ?? null)) {
        return $recording;
    }
    $policy = asterisk_recording_retention_policy(
        (float)$recording['duration_seconds'],
        (string)($recording['finished_at'] ?? $recording['created_at'] ?? ''),
        $config,
        $now
    );
    $pdo->prepare("UPDATE call_recordings
        SET status=?, discard_eligible_at=?, retention_until=?, last_cleanup_error=NULL, updated_at=datetime('now')
        WHERE id=? AND company_id=? AND status IN ('READY','DISCARD_PENDING')")
        ->execute([$policy['status'], $policy['discard_eligible_at'], $policy['retention_until'], (int)$recording['id'], (int)$recording['company_id']]);
    return array_replace($recording, $policy);
}

function asterisk_recording_cleanup_error(string $reason): string
{
    return asterisk_recording_failure_reason($reason) ?: 'Falha temporaria na limpeza da gravacao Asterisk.';
}

function asterisk_run_recording_retention(
    PDO $pdo,
    array $config,
    callable $fetchRecording,
    callable $deleteRecording,
    ?DateTimeImmutable $now = null
): array {
    $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $nowSql = $now->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $baseLimit = max(1, (int)$config['batch_size']);
    $underPressure = is_numeric($config['storage_usage_percent'] ?? null)
        && (float)$config['storage_usage_percent'] >= (float)$config['disk_threshold_percent'];
    $deleteLimit = $underPressure ? min(1000, $baseLimit * 2) : $baseLimit;
    $stats = ['prepared' => 0, 'discarded' => 0, 'missing' => 0, 'failed' => 0, 'disk_pressure' => $underPressure];

    $prepareLimit = min(1000, $baseLimit * 3);
    $rows = $pdo->query("SELECT * FROM call_recordings
        WHERE status IN ('READY','DISCARD_PENDING')
        ORDER BY COALESCE(finished_at, created_at) ASC, id ASC
        LIMIT {$prepareLimit}")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $recording) {
        if (!is_numeric($recording['duration_seconds'] ?? null) && (string)$recording['status'] === 'READY') {
            try {
                $file = $fetchRecording((string)$recording['recording_name']);
                $stream = $file['stream'] ?? null;
                $duration = is_resource($stream) ? asterisk_wav_duration_from_stream($stream) : null;
                if (is_resource($stream)) fclose($stream);
                if ($duration === null) throw new RuntimeException('Duracao WAV indisponivel para aplicar retencao.');
                $size = max(0, (int)($file['size_bytes'] ?? 0));
                $pdo->prepare("UPDATE call_recordings SET duration_seconds=?, size_bytes=CASE WHEN ?>0 THEN ? ELSE size_bytes END, last_cleanup_error=NULL, updated_at=datetime('now') WHERE id=? AND company_id=? AND status='READY'")
                    ->execute([$duration, $size, $size, (int)$recording['id'], (int)$recording['company_id']]);
                $recording['duration_seconds'] = $duration;
                if ($size > 0) $recording['size_bytes'] = $size;
            } catch (Throwable $error) {
                $pdo->prepare("UPDATE call_recordings SET last_cleanup_error=?, updated_at=datetime('now') WHERE id=? AND company_id=? AND status='READY'")
                    ->execute([asterisk_recording_cleanup_error($error->getMessage()), (int)$recording['id'], (int)$recording['company_id']]);
                $stats['failed']++;
                continue;
            }
        }
        $before = [
            'status' => (string)$recording['status'],
            'discard_eligible_at' => $recording['discard_eligible_at'] ?? null,
            'retention_until' => $recording['retention_until'] ?? null,
        ];
        $recording = asterisk_recording_apply_retention_policy($pdo, $recording, $config, $now);
        if ((string)$recording['status'] !== $before['status']
            || ($recording['discard_eligible_at'] ?? null) !== $before['discard_eligible_at']
            || ($recording['retention_until'] ?? null) !== $before['retention_until']) {
            $stats['prepared']++;
        }
    }

    $eligible = $pdo->prepare("SELECT * FROM call_recordings
        WHERE (status='DISCARD_PENDING' AND discard_eligible_at IS NOT NULL AND discard_eligible_at<=?)
           OR (status='READY' AND retention_until IS NOT NULL AND retention_until<=?)
        ORDER BY CASE WHEN status='DISCARD_PENDING' THEN discard_eligible_at ELSE retention_until END ASC, id ASC
        LIMIT {$deleteLimit}");
    $eligible->execute([$nowSql, $nowSql]);
    foreach ($eligible->fetchAll(PDO::FETCH_ASSOC) as $recording) {
        try {
            $result = strtoupper((string)$deleteRecording((string)$recording['recording_name']));
            if (!in_array($result, ['DELETED', 'MISSING'], true)) throw new RuntimeException('Resposta invalida ao excluir gravacao armazenada.');
            $pdo->prepare("UPDATE call_recordings
                SET status='DISCARDED', discarded_at=?, last_cleanup_error=NULL, updated_at=datetime('now')
                WHERE id=? AND company_id=? AND status IN ('READY','DISCARD_PENDING')")
                ->execute([$nowSql, (int)$recording['id'], (int)$recording['company_id']]);
            $stats[$result === 'MISSING' ? 'missing' : 'discarded']++;
        } catch (Throwable $error) {
            $pdo->prepare("UPDATE call_recordings SET last_cleanup_error=?, updated_at=datetime('now') WHERE id=? AND company_id=? AND status IN ('READY','DISCARD_PENDING')")
                ->execute([asterisk_recording_cleanup_error($error->getMessage()), (int)$recording['id'], (int)$recording['company_id']]);
            $stats['failed']++;
        }
    }
    return $stats;
}

function asterisk_wav_duration_from_stream($stream): ?float
{
    if (!is_resource($stream)) return null;
    $position = ftell($stream);
    rewind($stream);
    $header = fread($stream, 44);
    if ($position !== false) fseek($stream, $position);
    if (!is_string($header) || strlen($header) < 44 || substr($header, 0, 4) !== 'RIFF' || substr($header, 8, 4) !== 'WAVE') return null;
    $byteRate = unpack('V', substr($header, 28, 4))[1] ?? 0;
    $dataSize = unpack('V', substr($header, 40, 4))[1] ?? 0;
    if ($byteRate < 1 || $dataSize < 1) return null;
    return round($dataSize / $byteRate, 3);
}

function asterisk_persist_recording_event(PDO $pdo, array $summary, array $call, array $metadata = []): bool
{
    $callId = (int)($call['id'] ?? 0);
    $companyId = (int)($call['company_id'] ?? 0);
    $recordingName = asterisk_recording_safe_name((string)($summary['recording_name'] ?? ''));
    if ($callId < 1 || $companyId < 1 || asterisk_recording_call_id_from_name($recordingName) !== $callId) return false;

    $raceOutcome = strtoupper(trim((string)($call['race_outcome'] ?? '')));
    if ((!empty($call['dial_batch_id']) && $raceOutcome !== 'WINNER') || in_array($raceOutcome, ['LOSER', 'LATE_ANSWERED', 'ORIGINATE_FAILED'], true)) {
        return false;
    }

    $status = strtoupper(trim((string)($summary['status'] ?? '')));
    if (!in_array($status, ['RECORDING', 'READY', 'FAILED'], true)) return false;
    $format = strtolower(trim((string)($summary['format'] ?? 'wav')));
    if ($format !== 'wav') $format = 'wav';
    $timestamp = trim((string)($summary['timestamp'] ?? '')) ?: gmdate('Y-m-d\TH:i:s\Z');
    $metrics = asterisk_recording_metadata($metadata, $summary);
    $startedAt = $status === 'RECORDING' ? $timestamp : null;
    $finishedAt = in_array($status, ['READY', 'FAILED'], true) ? $timestamp : null;
    $failure = $status === 'FAILED' ? asterisk_recording_failure_reason((string)($summary['cause'] ?? 'Falha na gravacao ARI.')) : null;

    $stmt = $pdo->prepare("INSERT INTO call_recordings
        (company_id, call_id, recording_name, format, status, duration_seconds, size_bytes, started_at, finished_at, failure_reason, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
        ON CONFLICT(call_id) DO UPDATE SET
            recording_name = excluded.recording_name,
            format = excluded.format,
            status = CASE
                WHEN call_recordings.status IN ('READY', 'DISCARD_PENDING', 'DISCARDED') THEN call_recordings.status
                WHEN call_recordings.status = 'FAILED' AND excluded.status = 'RECORDING' THEN call_recordings.status
                ELSE excluded.status
            END,
            duration_seconds = COALESCE(excluded.duration_seconds, call_recordings.duration_seconds),
            size_bytes = COALESCE(excluded.size_bytes, call_recordings.size_bytes),
            started_at = COALESCE(call_recordings.started_at, excluded.started_at),
            finished_at = COALESCE(excluded.finished_at, call_recordings.finished_at),
            failure_reason = CASE WHEN call_recordings.status IN ('READY', 'DISCARD_PENDING', 'DISCARDED') THEN call_recordings.failure_reason WHEN excluded.status = 'FAILED' THEN excluded.failure_reason WHEN excluded.status = 'READY' THEN NULL ELSE call_recordings.failure_reason END,
            updated_at = datetime('now')");
    $stmt->execute([$companyId, $callId, $recordingName, $format, $status, $metrics['duration_seconds'], $metrics['size_bytes'], $startedAt, $finishedAt, $failure]);
    if ($status === 'READY' && $metrics['duration_seconds'] !== null) {
        $persisted = $pdo->prepare('SELECT * FROM call_recordings WHERE call_id=? AND company_id=? LIMIT 1');
        $persisted->execute([$callId, $companyId]);
        $row = $persisted->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) asterisk_recording_apply_retention_policy($pdo, $row, asterisk_recording_retention_config());
    }
    return true;
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

function asterisk_try_connected_bridge_recording(callable $record, array $call, string $bridgeId): array
{
    if ((string)($call['status'] ?? '') !== 'connected' || empty($call['connected_at'])) {
        return ['started' => false, 'skipped' => 'not_connected'];
    }
    if (!empty($call['finalized_at'])) {
        return ['started' => false, 'skipped' => 'finalized'];
    }
    $raceOutcome = strtoupper(trim((string)($call['race_outcome'] ?? '')));
    if (!empty($call['dial_batch_id']) && $raceOutcome !== 'WINNER') {
        return ['started' => false, 'skipped' => 'not_winner'];
    }
    if (in_array($raceOutcome, ['LOSER', 'LATE_ANSWERED', 'ORIGINATE_FAILED'], true)) {
        return ['started' => false, 'skipped' => 'not_winner'];
    }
    if ($bridgeId === '' || $bridgeId !== trim((string)($call['provider_bridge_id'] ?? ''))) {
        return ['started' => false, 'skipped' => 'invalid_bridge'];
    }
    try {
        $recordingName = asterisk_bridge_recording_name((int)($call['company_id'] ?? 0), (int)($call['id'] ?? 0));
        $result = $record($bridgeId, $recordingName, 'wav');
        return ['started' => true, 'recording_name' => $recordingName, 'result' => is_array($result) ? $result : []];
    } catch (Throwable $e) {
        return ['started' => false, 'error' => $e->getMessage()];
    }
}
