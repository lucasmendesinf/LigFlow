<?php
declare(strict_types=1);
require_once __DIR__ . '/../billing_domain.php';

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};
$tz = new DateTimeZone('America/Sao_Paulo');
$now = new DateTimeImmutable('2026-08-10 12:00:00', $tz);
$state = static fn(string $due) => billing_status_at($due, 'America/Sao_Paulo', $now);

$assert($state('2026-08-15 23:59:59')['state'] === 'warning', '5 dias antes');
$assert($state('2026-08-10 23:59:59')['state'] === 'warning', 'dia do vencimento');
$assert($state('2026-08-09 23:59:59')['state'] === 'overdue' && $state('2026-08-09 23:59:59')['days'] === 1, 'primeiro dia de atraso');
$assert($state('2026-08-03 23:59:59')['state'] === 'overdue' && !$state('2026-08-03 23:59:59')['blocked'], 'setimo dia de atraso');
$assert($state('2026-08-02 23:59:59')['blocked'], 'oitavo dia bloqueado');
$assert(!billing_operational_route_allowed(true, 'agent') && billing_operational_route_allowed(true, 'costs'), 'bloqueio de API operacional');
$assert(billing_normalize_payment_status('pending') === 'PENDING', 'pendente sem liberacao');
$assert(billing_normalize_payment_status('approved') === 'APPROVED', 'aprovado libera');
$assert(billing_authoritative_amount(['monthly_price'=>99.90, 'frontend_amount'=>0.01]) === 99.90, 'valor do frontend ignorado');
$assert(billing_public_webhook_url('http://localhost/voipCalutec') === null, 'webhook local nao enviado ao Mercado Pago');
$assert(billing_public_webhook_url('https://ligflow.example.com/app') === 'https://ligflow.example.com/app/?page=mercado_pago_webhook', 'webhook publico valido');

$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE periods (id INTEGER PRIMARY KEY, company_id INTEGER, payment_id INTEGER UNIQUE); CREATE TABLE payments (id INTEGER PRIMARY KEY, company_id INTEGER, status TEXT)');
$pdo->exec("INSERT INTO payments VALUES (1,10,'PENDING'),(2,20,'APPROVED')");
$insert = $pdo->prepare('INSERT OR IGNORE INTO periods(company_id,payment_id) VALUES (?,?)');
$insert->execute([20,2]); $insert->execute([20,2]);
$assert((int)$pdo->query('SELECT COUNT(*) FROM periods WHERE payment_id=2')->fetchColumn() === 1, 'webhook repetido sem duplicar periodo');
$q = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE company_id=?'); $q->execute([10]);
$assert((int)$q->fetchColumn() === 1, 'isolamento entre tenants');
$assert((int)$pdo->query("SELECT COUNT(*) FROM periods p JOIN payments x ON x.id=p.payment_id WHERE x.status='PENDING'")->fetchColumn() === 0, 'pagamento pendente nao cria periodo');

echo "OK - {$tests} testes\n";
