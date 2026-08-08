<?php
declare(strict_types=1);
if ((['REQUEST_METHOD'] ?? 'GET') !== 'POST') { http_response_code(405); header('Allow: POST'); echo json_encode(['ok'=>false,'error'=>'method_not_allowed']); exit; }
$configPath = getenv('LIGFLOW_ASTERISK_AGENT_CONFIG') ?: __DIR__ . '/config.php';
if (!is_file($configPath)) { http_response_code(503); echo json_encode(['ok'=>false,'error'=>'agent_not_configured']); exit; }
$config = require $configPath;
require __DIR__ . '/lib.php';
$headers=[]; foreach ($_SERVER as $key=>$value) { if (str_starts_with($key,'HTTP_')) $headers[strtolower(str_replace('_','-',substr($key,5)))] = (string)$value; }
$agent = new LigFlowAsteriskAgent($config);
$result = $agent->handle($headers, (string)file_get_contents('php://input'), (string)($_SERVER['REMOTE_ADDR'] ?? ''));
http_response_code($result['status']); header('Content-Type: application/json'); echo json_encode($result['body'], JSON_UNESCAPED_SLASHES);
