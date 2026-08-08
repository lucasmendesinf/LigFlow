<?php
declare(strict_types=1);

final class LigFlowAsteriskAgent
{
    private array $config;
    private $runner;

    public function __construct(array $config, ?callable $runner = null)
    {
        $this->config = $config;
        $this->runner = $runner ?? static function (array $command): array {
            $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($process)) return ['code' => 1, 'stdout' => '', 'stderr' => 'Falha ao iniciar o comando controlado.'];
            $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
            return ['code' => proc_close($process), 'stdout' => (string)$stdout, 'stderr' => (string)$stderr];
        };
    }

    public function handle(array $headers, string $rawBody, string $remoteIp): array
    {
        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) return $this->fail(400, 'invalid_json');
        if (!$this->validIp($remoteIp) || !$this->authenticate($headers, $rawBody)) return $this->fail(401, 'unauthorized');
        if (($payload['operation'] ?? '') !== 'CREATE_EXTENSION') return $this->fail(422, 'unsupported_operation');
        $extension = (string)($payload['extension'] ?? '');
        $serverId = (int)($payload['asterisk_server_id'] ?? 0);
        $linkId = (int)($payload['asterisk_user_extension_id'] ?? 0);
        $key = (string)($payload['idempotency_key'] ?? '');
        $password = (string)($payload['sip_password'] ?? '');
        if (!$this->validRequest($extension, $serverId, $linkId, $key, $password)) return $this->fail(422, 'invalid_request');

        $stored = $this->idempotentResponse($key);
        if ($stored !== null) return $stored;
        if ($this->endpointExists($extension) || is_file($this->extensionFile($extension))) return $this->finish($key, 409, ['ok' => false, 'error' => 'endpoint_conflict']);
        if (!$this->masterIncludeIsConfigured()) return $this->finish($key, 409, ['ok' => false, 'error' => 'ligflow_include_not_configured']);

        $extensionFile = $this->extensionFile($extension);
        $masterFile = $this->masterFile();
        $backup = $this->backup($extensionFile, $masterFile);
        try {
            $this->atomicWrite($extensionFile, $this->renderExtension($extension, $password));
            $this->atomicWrite($masterFile, $this->renderMaster($extension));
            $reload = $this->runAsterisk(['-rx', 'pjsip reload']);
            if ($reload['code'] !== 0) throw new RuntimeException('pjsip_reload_failed');
            if (!$this->endpointExists($extension)) throw new RuntimeException('endpoint_not_confirmed');
            return $this->finish($key, 200, ['ok' => true, 'extension' => $extension, 'endpoint_confirmed' => true]);
        } catch (Throwable $error) {
            $this->restore($backup);
            $this->runAsterisk(['-rx', 'pjsip reload']);
            return $this->finish($key, 502, ['ok' => false, 'error' => $this->safeError($error)]);
        }
    }

    private function authenticate(array $headers, string $rawBody): bool
    {
        $timestamp = (string)($headers['x-ligflow-timestamp'] ?? '');
        $nonce = (string)($headers['x-ligflow-nonce'] ?? '');
        $signature = (string)($headers['x-ligflow-signature'] ?? '');
        $secret = (string)($this->config['shared_secret'] ?? '');
        if ($secret === '' || !ctype_digit($timestamp) || preg_match('/^[A-Fa-f0-9]{32,128}$/', $nonce) !== 1 || abs(time() - (int)$timestamp) > 60) return false;
        if ($this->nonceSeen($nonce)) return false;
        $expected = hash_hmac('sha256', $timestamp . '.' . $nonce . '.' . $rawBody, $secret);
        if (!hash_equals($expected, $signature)) return false;
        $this->rememberNonce($nonce);
        return true;
    }

    private function validRequest(string $extension, int $serverId, int $linkId, string $key, string $password): bool
    {
        return $serverId === (int)($this->config['asterisk_server_id'] ?? 1)
            && $linkId > 0
            && preg_match('/^[0-9]{1,18}$/', $extension) === 1
            && (int)$extension >= (int)$this->config['extension_start']
            && (int)$extension <= (int)$this->config['extension_end']
            && preg_match('/^[A-Fa-f0-9]{32,128}$/', $key) === 1
            && strlen($password) >= 24;
    }

    private function validIp(string $ip): bool { return in_array($ip, (array)($this->config['allowed_ips'] ?? []), true); }
    private function stateDir(): string { return rtrim((string)$this->config['state_dir'], DIRECTORY_SEPARATOR); }
    private function nonceFile(string $nonce): string { return $this->stateDir() . '/nonces/' . hash('sha256', $nonce); }
    private function idempotencyFile(string $key): string { return $this->stateDir() . '/jobs/' . hash('sha256', $key) . '.json'; }
    private function nonceSeen(string $nonce): bool { return is_file($this->nonceFile($nonce)); }
    private function rememberNonce(string $nonce): void { $file=$this->nonceFile($nonce); if(!is_dir(dirname($file))) mkdir(dirname($file),0700,true); if(@file_put_contents($file,(string)time(),LOCK_EX)===false) throw new RuntimeException('nonce_storage_failed'); chmod($file,0600); }
    private function idempotentResponse(string $key): ?array { $file=$this->idempotencyFile($key); if(!is_file($file)) return null; $saved=json_decode((string)file_get_contents($file),true); return is_array($saved)?$saved:null; }
    private function finish(string $key, int $status, array $body): array { $response=['status'=>$status,'body'=>$body]; $file=$this->idempotencyFile($key); if(!is_dir(dirname($file))) mkdir(dirname($file),0700,true); file_put_contents($file,json_encode($response,JSON_UNESCAPED_SLASHES),LOCK_EX); chmod($file,0600); return $response; }
    private function fail(int $status, string $error): array { return ['status'=>$status,'body'=>['ok'=>false,'error'=>$error]]; }
    private function extensionFile(string $extension): string { return rtrim((string)$this->config['managed_dir'],DIRECTORY_SEPARATOR).'/'.$extension.'.conf'; }
    private function masterFile(): string { return (string)$this->config['master_file']; }
    private function masterIncludeIsConfigured(): bool { $root=(string)$this->config['pjsip_conf']; $include=(string)$this->config['root_include']; return is_file($root) && str_contains((string)file_get_contents($root),$include); }
    private function renderExtension(string $extension, string $password): string { $template=(string)($this->config['endpoint_template'] ?? ''); if($template==='') throw new RuntimeException('endpoint_template_not_configured'); return str_replace(['{{extension}}','{{password}}'],[$extension,$password],$template); }
    private function renderMaster(string $extension): string { $content=is_file($this->masterFile())?(string)file_get_contents($this->masterFile()):"; Managed by LigFlow. Do not edit manually.\n"; $include='#include "pjsip.d/ligflow/'.$extension.'.conf"'; return str_contains($content,$include)?$content:$content.$include."\n"; }
    private function atomicWrite(string $file, string $content): void { if(!is_dir(dirname($file))) mkdir(dirname($file),0750,true); $tmp=$file.'.tmp.'.bin2hex(random_bytes(4)); file_put_contents($tmp,$content,LOCK_EX); chmod($tmp,0640); if(!rename($tmp,$file)) { @unlink($tmp); throw new RuntimeException('atomic_write_failed'); } }
    private function backup(string ...$files): array { $items=[]; foreach($files as $file){$items[$file]=is_file($file)?file_get_contents($file):null;} return $items; }
    private function restore(array $backup): void { foreach($backup as $file=>$content){ if($content===null){@unlink($file);}else{$this->atomicWrite($file,(string)$content);} } }
    private function runAsterisk(array $arguments): array { return ($this->runner)(array_merge([(string)$this->config['asterisk_bin']],$arguments)); }
    private function endpointExists(string $extension): bool { $result=$this->runAsterisk(['-rx','pjsip show endpoint '.$extension]); return $result['code']===0 && stripos($result['stdout'],'Endpoint:')!==false; }
    private function safeError(Throwable $error): string { return preg_replace('/[^A-Za-z0-9_.-]/','_',substr($error->getMessage(),0,120)) ?: 'provisioning_failed'; }
}
