<?php
declare(strict_types=1);

define('LIGFLOW_ARI_WORKER', true);
require __DIR__ . '/index.php';

if (PHP_SAPI !== 'cli') {
    exit(1);
}

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
