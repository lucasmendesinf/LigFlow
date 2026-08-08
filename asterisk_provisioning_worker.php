<?php
declare(strict_types=1);
define('LIGFLOW_ARI_WORKER', true);
require __DIR__ . '/index.php';
if (PHP_SAPI !== 'cli') exit(1);
$limit = isset($argv[1]) ? (int)$argv[1] : 10;
$processed = asterisk_process_pending_provisioning_jobs($limit);
echo "Processed {$processed} Asterisk provisioning job(s).\n";
