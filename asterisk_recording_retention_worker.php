<?php
declare(strict_types=1);

define('LIGFLOW_ARI_WORKER', true);
require __DIR__ . '/index.php';

if (PHP_SAPI !== 'cli') exit(1);

$lock = fopen(DATA_DIR . '/asterisk_recording_retention_worker.lock', 'c+');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Retencao de gravacoes Asterisk ja esta em execucao.\n");
    exit(0);
}

$config = asterisk_recording_retention_config();
if (isset($argv[1]) && (int)$argv[1] > 0) {
    $config['batch_size'] = max(1, min(500, (int)$argv[1]));
}
$provider = new AsteriskProvider(asterisk_config());

$fetch = static fn(string $recordingName): array => $provider->storedRecordingFile($recordingName);
$delete = static function (string $recordingName) use ($provider): string {
    try {
        $provider->deleteStoredRecording($recordingName);
        return 'DELETED';
    } catch (AsteriskAriRequestException $error) {
        if ((int)($error->diagnostics()['http_status'] ?? 0) === 404) return 'MISSING';
        throw $error;
    }
};

try {
    $stats = asterisk_run_recording_retention(db(), $config, $fetch, $delete);
    echo sprintf(
        "Retencao Asterisk concluida: preparados=%d, excluidos=%d, ausentes=%d, falhas=%d, pressao_disco=%s.\n",
        (int)$stats['prepared'],
        (int)$stats['discarded'],
        (int)$stats['missing'],
        (int)$stats['failed'],
        !empty($stats['disk_pressure']) ? 'sim' : 'nao'
    );
} catch (Throwable $error) {
    fwrite(STDERR, 'Falha na retencao Asterisk: ' . asterisk_recording_cleanup_error($error->getMessage()) . "\n");
    exit(1);
}
