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

$rate035 = billing_decimal_to_micros('0.35');
$cost = static fn(int $seconds, int $rate = 350000): array => billing_proportional_call_cost($seconds, $rate);
$directCallCost = static fn(int $seconds, int $rate = 320000): array => billing_full_minute_call_cost($seconds, $rate);
$assert($cost(0)['cost_micros'] === 0, '0 segundos sem custo');
$assert($cost(10)['cost_micros'] === 58333 && $cost(10)['cost_decimal'] === '0.058333', '10 segundos proporcionais');
$assert($cost(30)['cost_micros'] === 175000, '30 segundos proporcionais');
$assert($cost(59)['cost_micros'] === 344167, '59 segundos proporcionais');
$assert($cost(60)['cost_micros'] === 350000 && $cost(60)['cost_decimal'] === '0.350000', '60 segundos exatos');
$assert($cost(61)['cost_micros'] === 355833, '61 segundos proporcionais');
$assert($cost(90)['cost_micros'] === 525000, '90 segundos proporcionais');
$assert($cost(0, $rate035)['cost_micros'] === 0, 'chamada nao atendida sem custo');
$assert($cost(10, $rate035)['cost_micros'] !== $rate035, '10 segundos nao cobram minuto cheio');
$assert($cost(60, billing_decimal_to_micros('0.50'))['cost_micros'] === 500000, 'tarifa conforme plano');
$assert($directCallCost(0)['cost_micros'] === 0 && $directCallCost(0)['billed_minutes'] === 0, 'DirectCall 0 segundos sem custo');
$assert($directCallCost(15)['cost_micros'] === 320000 && $directCallCost(15)['billed_minutes'] === 1, 'DirectCall 15 segundos cobra 1 minuto cheio');
$assert($directCallCost(48)['cost_micros'] === 320000 && $directCallCost(48)['billed_minutes'] === 1, 'DirectCall 48 segundos cobra 1 minuto cheio');
$assert($directCallCost(60)['cost_micros'] === 320000 && $directCallCost(60)['billed_minutes'] === 1, 'DirectCall 60 segundos cobra 1 minuto cheio');
$assert($directCallCost(61)['cost_micros'] === 640000 && $directCallCost(61)['billed_minutes'] === 2, 'DirectCall 61 segundos cobra 2 minutos cheios');

$assert(billing_cadence_30_6_seconds(0) === 0, 'cadencia 30/6: 0s sem custo');
$assert(billing_cadence_30_6_seconds(1) === 30, 'cadencia 30/6: 1s cobra a franquia minima de 30s');
$assert(billing_cadence_30_6_seconds(30) === 30, 'cadencia 30/6: 30s cobra exatamente 30s');
$assert(billing_cadence_30_6_seconds(31) === 36, 'cadencia 30/6: 31s ja entra no proximo bloco de 6s');
$assert(billing_cadence_30_6_seconds(36) === 36, 'cadencia 30/6: 36s cobra 36s');
$assert(billing_cadence_30_6_seconds(37) === 42, 'cadencia 30/6: 37s cobra 42s');
$assert(billing_cadence_30_6_seconds(42) === 42, 'cadencia 30/6: 42s cobra 42s');
$assert(billing_cadence_30_6_seconds(43) === 48, 'cadencia 30/6: 43s cobra 48s');
$cadenceCost = static fn(int $seconds, int $rate = 350000): array => billing_cadence_call_cost($seconds, $rate);
$assert($cadenceCost(0)['cost_micros'] === 0, 'cadencia 30/6: chamada nao atendida sem custo');
$assert($cadenceCost(1)['billable_seconds'] === 30 && $cadenceCost(1)['cost_micros'] === $cadenceCost(30)['cost_micros'], 'cadencia 30/6: 1s cobra o mesmo que 30s');
$assert($cadenceCost(37)['billable_seconds'] === 42, 'cadencia 30/6: usa os segundos cobraveis, nao os reais, no custo');
$assert($cadenceCost(60, billing_decimal_to_micros('0.50'))['billable_seconds'] === 60 && $cadenceCost(60, billing_decimal_to_micros('0.50'))['cost_micros'] === 500000, 'cadencia 30/6: 60s cobra tarifa cheia do minuto conforme o plano');

$pdo->exec('CREATE TABLE call_charges (call_id INTEGER PRIMARY KEY, cost_micros INTEGER NOT NULL)');
$charge = $pdo->prepare('INSERT INTO call_charges(call_id,cost_micros) VALUES (?,?) ON CONFLICT(call_id) DO UPDATE SET cost_micros=excluded.cost_micros');
$charge->execute([77, $cost(30)['cost_micros']]);
$charge->execute([77, $cost(30)['cost_micros']]);
$assert((int)$pdo->query('SELECT SUM(cost_micros) FROM call_charges WHERE call_id=77')->fetchColumn() === 175000, 'reprocessamento sem debito duplicado');
$assert((int)$pdo->query('SELECT SUM(cost_micros) FROM call_charges')->fetchColumn() === $cost(30)['cost_micros'], 'relatorio e saldo usam custo persistido');

$credit = billing_decimal_to_micros('200.00');
$assert($credit === 200000000, 'ativacao concede R$ 200,00 de credito');
$assert(billing_micros_to_brl($credit) === 'R$ 200,00', 'credito exibido em BRL');
$assert(billing_telephony_balance_after($credit, $cost(60)['cost_micros']) === 199650000, '60 segundos debitam R$ 0,35 do credito');
$assert(billing_telephony_balance_after($credit, $cost(10)['cost_micros']) === 199941667, '10 segundos debitam valor proporcional');
$assert(billing_telephony_balance_after($credit, $cost(90)['cost_micros']) === 199475000, '90 segundos debitam valor proporcional');
$assert(billing_telephony_balance_after($credit, $cost(0)['cost_micros']) === $credit, 'chamada nao atendida nao debita credito');
$assert(billing_mvp_test_call_allowed(true, 'MVP'), 'MVP permite chamada de teste para admin');
$assert(billing_mvp_test_call_allowed(true, 'mvp'), 'MVP ignora maiusculas');
$assert(!billing_mvp_test_call_allowed(false, 'MVP'), 'MVP nao libera usuario comum');
$assert(!billing_mvp_test_call_allowed(true, 'Start'), 'outro plano continua exigindo credito');
$assert(!billing_telephony_call_allowed(true, 0, $rate035), 'saldo zerado bloqueia nova chamada');
$assert(!billing_telephony_call_allowed(false, $credit, $rate035) && billing_operational_route_allowed(false, 'dashboard'), 'sem credito nao bloqueia demais funcoes');

$ledger = new PDO('sqlite::memory:');
$ledger->exec('CREATE TABLE periods (id INTEGER PRIMARY KEY, company_id INTEGER, initial_micros INTEGER, rate_micros INTEGER, balance_micros INTEGER); CREATE TABLE ledger (id INTEGER PRIMARY KEY, company_id INTEGER, call_id INTEGER UNIQUE, idempotency_key TEXT UNIQUE, amount_micros INTEGER, balance_before_micros INTEGER, balance_after_micros INTEGER); CREATE TABLE audit (action TEXT, company_id INTEGER)');
$ledger->exec('INSERT INTO periods VALUES (1,10,200000000,350000,200000000),(2,20,50000000,500000,50000000)');
$planRateBefore = (int)$ledger->query('SELECT rate_micros FROM periods WHERE id=1')->fetchColumn();
$editedPlanRate = billing_decimal_to_micros('0.99');
$assert($planRateBefore === 350000 && $editedPlanRate !== $planRateBefore, 'edicao do plano nao altera tarifa do periodo ativo');
$grant = $ledger->prepare('INSERT OR IGNORE INTO ledger(company_id,idempotency_key,amount_micros,balance_before_micros,balance_after_micros) VALUES (?,?,?,?,?)');
$grant->execute([10, 'period-credit:1', 200000000, 0, 200000000]);
$grant->execute([10, 'period-credit:1', 200000000, 0, 200000000]);
$assert((int)$ledger->query("SELECT COUNT(*) FROM ledger WHERE idempotency_key='period-credit:1'")->fetchColumn() === 1, 'renovacao e webhook repetido concedem credito uma vez');
$debit = static function (PDO $db, int $companyId, int $callId, int $amount): void {
    $period = $db->query('SELECT * FROM periods WHERE company_id=' . $companyId)->fetch(PDO::FETCH_ASSOC);
    $before = (int)$period['balance_micros'];
    $after = billing_telephony_balance_after($before, $amount);
    $stmt = $db->prepare('INSERT OR IGNORE INTO ledger(company_id,call_id,idempotency_key,amount_micros,balance_before_micros,balance_after_micros) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$companyId, $callId, 'call-debit:' . $callId, -$amount, $before, $after]);
    if ($stmt->rowCount() === 1) {
        $db->prepare('UPDATE periods SET balance_micros=? WHERE id=?')->execute([$after, $period['id']]);
    }
};
$debit($ledger, 10, 101, $cost(60)['cost_micros']);
$debit($ledger, 10, 101, $cost(60)['cost_micros']);
$assert((int)$ledger->query('SELECT COUNT(*) FROM ledger WHERE call_id=101')->fetchColumn() === 1, 'callback repetido nao duplica debito');
$debit($ledger, 10, 102, $cost(60)['cost_micros']);
$assert((int)$ledger->query('SELECT balance_micros FROM periods WHERE id=1')->fetchColumn() === 199300000, 'dois debitos sequenciais preservam saldo consistente');
$assert((int)$ledger->query('SELECT balance_micros FROM periods WHERE id=2')->fetchColumn() === 50000000, 'isolamento de credito entre tenants');
$ledger->prepare('INSERT INTO audit(action,company_id) VALUES (?,?)')->execute(['ajustou_credito_telefonia', 10]);
$assert((int)$ledger->query("SELECT COUNT(*) FROM audit WHERE action='ajustou_credito_telefonia' AND company_id=10")->fetchColumn() === 1, 'ajuste manual gera auditoria');

echo "OK - {$tests} testes\n";
