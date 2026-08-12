<?php
declare(strict_types=1);

const BILLING_PAYMENT_STATUSES = ['CREATED', 'PENDING', 'IN_PROCESS', 'APPROVED', 'REJECTED', 'CANCELLED', 'EXPIRED', 'REFUNDED', 'CHARGED_BACK', 'ERROR'];

function billing_decimal_to_micros(string|int $value): int
{
    $value = trim(str_replace(',', '.', (string)$value));
    if (!preg_match('/^(\d+)(?:\.(\d+))?$/', $value, $matches)) {
        return 0;
    }
    $whole = (int)$matches[1];
    $fraction = (string)($matches[2] ?? '');
    $micros = (int)str_pad(substr($fraction, 0, 6), 6, '0');
    if (isset($fraction[6]) && (int)$fraction[6] >= 5) {
        $micros++;
    }
    return max(0, ($whole * 1000000) + $micros);
}

function billing_micros_to_decimal(int $micros): string
{
    $micros = max(0, $micros);
    return intdiv($micros, 1000000) . '.' . str_pad((string)($micros % 1000000), 6, '0', STR_PAD_LEFT);
}

function billing_input_to_micros(string $value): ?int
{
    $value = trim(str_replace(',', '.', $value));
    if ($value === '' || !preg_match('/^\d+(?:\.\d{1,6})?$/', $value)) {
        return null;
    }
    return billing_decimal_to_micros($value);
}

function billing_micros_to_brl(int $micros): string
{
    $negative = $micros < 0;
    $micros = abs($micros);
    $centavos = intdiv($micros + 5000, 10000);
    return ($negative ? '-R$ ' : 'R$ ')
        . number_format(intdiv($centavos, 100), 0, ',', '.')
        . ',' . str_pad((string)($centavos % 100), 2, '0', STR_PAD_LEFT);
}

function billing_proportional_call_cost(int $billableSeconds, int $rateMicros): array
{
    $billableSeconds = max(0, $billableSeconds);
    $rateMicros = max(0, $rateMicros);
    $costMicros = $billableSeconds > 0 && $rateMicros > 0
        ? intdiv(($billableSeconds * $rateMicros) + 30, 60)
        : 0;
    return [
        'billable_seconds' => $billableSeconds,
        'rate_micros' => $rateMicros,
        'cost_micros' => $costMicros,
        'cost_decimal' => billing_micros_to_decimal($costMicros),
    ];
}

function billing_full_minute_call_cost(int $billableSeconds, int $rateMicros): array
{
    $billableSeconds = max(0, $billableSeconds);
    $rateMicros = max(0, $rateMicros);
    $billedMinutes = $billableSeconds > 0 ? intdiv($billableSeconds + 59, 60) : 0;
    $costMicros = $rateMicros > 0 ? $billedMinutes * $rateMicros : 0;
    return [
        'billable_seconds' => $billableSeconds,
        'billed_minutes' => $billedMinutes,
        'rate_micros' => $rateMicros,
        'cost_micros' => $costMicros,
        'cost_decimal' => billing_micros_to_decimal($costMicros),
    ];
}

function billing_telephony_balance_after(int $balanceMicros, int $debitMicros): int
{
    return $balanceMicros - max(0, $debitMicros);
}

function billing_mvp_test_call_allowed(bool $platformAdmin, string $planName): bool
{
    return $platformAdmin && strtoupper(trim($planName)) === 'MVP';
}

function billing_telephony_call_allowed(bool $configured, int $balanceMicros, int $rateMicros): bool
{
    return $configured && $balanceMicros > 0 && $rateMicros > 0;
}

function billing_status_at(?string $renewsAt, string $timezone, ?DateTimeImmutable $now = null): array
{
    $tz = new DateTimeZone($timezone ?: 'America/Sao_Paulo');
    $now = ($now ?: new DateTimeImmutable('now', $tz))->setTimezone($tz);
    if (!$renewsAt) {
        return ['state' => 'blocked', 'days' => 8, 'blocked' => true, 'message' => 'Plano sem vencimento valido.'];
    }
    $due = (new DateTimeImmutable($renewsAt, $tz))->setTimezone($tz);
    $today = $now->setTime(0, 0);
    $dueDay = $due->setTime(0, 0);
    $delta = (int)$today->diff($dueDay)->format('%r%a');
    if ($delta >= 0 && $delta <= 5) {
        return ['state' => 'warning', 'days' => $delta, 'blocked' => false, 'message' => $delta === 0 ? 'Seu plano vence hoje.' : "Seu plano vence em {$delta} dia(s)."];
    }
    if ($delta < 0) {
        $late = abs($delta);
        if ($late <= 7) {
            return ['state' => 'overdue', 'days' => $late, 'blocked' => false, 'message' => "Plano em atraso ha {$late} dia(s). O bloqueio ocorre no oitavo dia."];
        }
        return ['state' => 'blocked', 'days' => $late, 'blocked' => true, 'message' => "Plano bloqueado por atraso de {$late} dia(s)."];
    }
    return ['state' => 'active', 'days' => $delta, 'blocked' => false, 'message' => 'Plano ativo.'];
}

function billing_normalize_payment_status(string $status, ?string $detail = null): string
{
    $status = strtolower(trim($status));
    $detail = strtolower(trim((string)$detail));
    if (str_contains($detail, 'expired')) return 'EXPIRED';
    return match ($status) {
        'approved' => 'APPROVED',
        'pending' => 'PENDING',
        'in_process', 'in_mediation', 'authorized' => 'IN_PROCESS',
        'rejected' => 'REJECTED',
        'cancelled' => 'CANCELLED',
        'refunded' => 'REFUNDED',
        'charged_back' => 'CHARGED_BACK',
        default => 'ERROR',
    };
}

function billing_period_end(DateTimeImmutable $start, string $period): DateTimeImmutable
{
    $period = strtolower(trim($period));
    return match ($period) {
        'anual', 'annual', 'yearly' => $start->modify('+1 year'),
        'trimestral', 'quarterly' => $start->modify('+3 months'),
        'semestral', 'semiannual' => $start->modify('+6 months'),
        default => $start->modify('+1 month'),
    };
}

function billing_operational_route_allowed(bool $blocked, string $page): bool
{
    return !$blocked || in_array($page, ['login', 'costs', 'payment_status', 'mercado_pago_webhook'], true);
}

function billing_authoritative_amount(array $plan): float
{
    return round((float)($plan['monthly_price'] ?? 0), 2);
}

function billing_public_webhook_url(string $appUrl): ?string
{
    $appUrl = rtrim(trim($appUrl), '/');
    if ($appUrl === '' || filter_var($appUrl, FILTER_VALIDATE_URL) === false) return null;
    $parts = parse_url($appUrl);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower((string)($parts['host'] ?? ''));
    if ($scheme !== 'https' || $host === '' || $host === 'localhost' || str_ends_with($host, '.local')) return null;
    if (filter_var($host, FILTER_VALIDATE_IP) !== false
        && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) return null;
    return $appUrl . '/?page=mercado_pago_webhook';
}
