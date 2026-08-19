<?php
declare(strict_types=1);

define('LIGFLOW_ARI_WORKER', true);
require __DIR__ . '/index.php';

if (PHP_SAPI !== 'cli') {
    exit(1);
}

ini_set('error_log', DATA_DIR . '/asterisk_ari_worker.log');

$lockHandle = fopen(DATA_DIR . '/asterisk_ari_worker.lock', 'c+');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    error_log('LigFlow Asterisk ARI worker: another consumer is already running.');
    exit(0);
}
ftruncate($lockHandle, 0);
fwrite($lockHandle, (string)getmypid());
fflush($lockHandle);

$backoff = 2;
while (true) {
    $config = asterisk_config();
    if (empty($config['enabled']) || empty($config['ari_ws_url']) || empty($config['ari_username']) || empty($config['ari_password'])) {
        sleep(5);
        continue;
    }

    $socket = null;
    try {
        $socket = new AsteriskAriWebSocket($config);
        $socket->connect();
        $backoff = max(1, (int)$config['reconnect_initial_seconds']);
        while (true) {
            $event = $socket->readEvent(10);
            if ($event === null) {
                if ($socket->timedOut()) {
                    continue;
                }
                throw new RuntimeException('Conexao ARI encerrada.');
            }
            asterisk_handle_event($event);
        }
    } catch (Throwable $e) {
        error_log('LigFlow Asterisk ARI worker: reconnecting after connection failure.');
    } finally {
        if ($socket instanceof AsteriskAriWebSocket) {
            $socket->close();
        }
    }
    sleep($backoff);
    $backoff = min(max(2, (int)$config['reconnect_max_seconds']), max(1, $backoff * 2));
}
