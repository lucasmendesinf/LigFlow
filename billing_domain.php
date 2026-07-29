<?php
declare(strict_types=1);

const BILLING_PAYMENT_STATUSES = ['CREATED', 'PENDING', 'IN_PROCESS', 'APPROVED', 'REJECTED', 'CANCELLED', 'EXPIRED', 'REFUNDED', 'CHARGED_BACK', 'ERROR'];

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
