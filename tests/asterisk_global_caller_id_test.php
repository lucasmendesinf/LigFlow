<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$source = file_get_contents(dirname(__DIR__) . '/index.php') ?: '';

$assert(str_contains($source, "\$nvoipTrunkConfig['caller_id']"), 'Nvoip caller ID is stored in the existing global trunk configuration');
$assert(str_contains($source, "\$this->trunk() === 'NVOIP_TRUNK'"), 'global caller ID is restricted to the Nvoip trunk');
$assert(substr_count($source, "\$callerId = \$this->outboundCallerId(\$campaign);") === 2, 'single and parallel Asterisk calls share the same caller ID resolver');
$assert(substr_count($source, "if (\$callerId !== '') \$payload['callerId'] = \$callerId;") === 2, 'blank Nvoip caller ID lets the PJSIP endpoint apply its authorized identity');
$assert(str_contains($source, "if (\$this->trunk() === 'NVOIP_TRUNK')"), 'Nvoip has an isolated caller ID branch');
$assert(str_contains($source, "return '';"), 'Nvoip lets its registered endpoint apply the authorized caller ID');
$assert(str_contains($source, "return nvoip_phone_digits((string)(\$campaign['caller_id'] ?? ''));"), 'DirectCall preserves the campaign caller ID');
$assert(str_contains($source, 'name="nvoip_caller_id"'), 'Asterisk settings expose the global Nvoip caller ID field');
$assert(str_contains($source, "'nvoip_caller_id_configured' => \$nvoipCallerId !== ''"), 'audit records configuration state without exposing the number');

echo "OK - {$tests} tests\n";
