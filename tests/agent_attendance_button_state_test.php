<?php
declare(strict_types=1);

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';
$webphone = file_get_contents(dirname(__DIR__) . '/assets/nvoip-webphone.js') ?: '';

if (!str_contains($source, "finalized_at IS NULL AND status IN ('in_progress','calling_origin','ringing','answered','connected')")) {
    throw new RuntimeException('O botao deve considerar todos os estados de chamada em andamento.');
}
if (!str_contains($source, '$attendanceButtonBusy = $isAutoDialing || $hasOngoingCall;')) {
    throw new RuntimeException('O botao deve considerar operacao automatica ou chamada ativa.');
}
if (!str_contains($source, 'data-start-attendance-button')) {
    throw new RuntimeException('O botao precisa estar identificado para atualizacao em tempo real.');
}
if (!str_contains($webphone, "classList.toggle('danger', attendanceBusy)")) {
    throw new RuntimeException('O webphone deve deixar o botao vermelho durante a chamada.');
}
if (!str_contains($webphone, "classList.toggle('success', !attendanceBusy)")) {
    throw new RuntimeException('O webphone deve restaurar o verde ao encerrar a chamada.');
}

echo "OK - estado visual do botao de atendimento\n";
