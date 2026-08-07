<?php
declare(strict_types=1);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) throw new RuntimeException($message);
};

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE asterisk_user_extensions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    asterisk_server_id INTEGER NOT NULL DEFAULT 1,
    extension TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Ativo',
    provisioning_status TEXT NOT NULL DEFAULT 'Pendente',
    deactivated_at TEXT
);");
$db->exec("CREATE UNIQUE INDEX active_extension ON asterisk_user_extensions(company_id, asterisk_server_id, extension) WHERE status = 'Ativo';");
$db->exec("CREATE UNIQUE INDEX active_user ON asterisk_user_extensions(company_id, user_id, asterisk_server_id) WHERE status = 'Ativo';");

$assign = static function (PDO $db, int $companyId, int $userId, string $extension, bool $active = true): void {
    $db->beginTransaction();
    try {
        if (!$active || $extension === '') {
            $db->prepare("UPDATE asterisk_user_extensions SET status='Inativo', deactivated_at='now' WHERE user_id=? AND asterisk_server_id=1 AND status='Ativo'")
                ->execute([$userId]);
        } else {
            if (preg_match('/^[0-9]{1,32}$/', $extension) !== 1) throw new InvalidArgumentException('invalid extension');
            $duplicate = $db->prepare("SELECT user_id FROM asterisk_user_extensions WHERE company_id=? AND asterisk_server_id=1 AND extension=? AND status='Ativo' AND user_id<>?");
            $duplicate->execute([$companyId, $extension, $userId]);
            if ($duplicate->fetchColumn()) throw new InvalidArgumentException('duplicate extension');
            $db->prepare("UPDATE asterisk_user_extensions SET status='Inativo', deactivated_at='now' WHERE user_id=? AND asterisk_server_id=1 AND status='Ativo' AND (company_id<>? OR extension<>?)")
                ->execute([$userId, $companyId, $extension]);
            $current = $db->prepare("SELECT id FROM asterisk_user_extensions WHERE company_id=? AND user_id=? AND asterisk_server_id=1 AND extension=? AND status='Ativo'");
            $current->execute([$companyId, $userId, $extension]);
            if (!$current->fetchColumn()) {
                $db->prepare("INSERT INTO asterisk_user_extensions(company_id,user_id,asterisk_server_id,extension,status,provisioning_status) VALUES (?,?,1,?,'Ativo','Pendente')")
                    ->execute([$companyId, $userId, $extension]);
            }
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
};

$assign($db, 1, 10, '1003');
$row = $db->query("SELECT company_id,user_id,extension,status,provisioning_status FROM asterisk_user_extensions WHERE status='Ativo'")->fetch(PDO::FETCH_ASSOC);
$assert((int)$row['company_id'] === 1 && (int)$row['user_id'] === 10 && $row['extension'] === '1003', 'creates user extension link');
$assert($row['status'] === 'Ativo' && $row['provisioning_status'] === 'Pendente', 'new link is pending provisioning');

try { $assign($db, 1, 11, '1003'); $assert(false, 'duplicate extension must fail'); } catch (InvalidArgumentException) { $assert(true, 'duplicate active extension is rejected'); }
$assign($db, 1, 10, '1004');
$assert((string)$db->query("SELECT extension FROM asterisk_user_extensions WHERE user_id=10 AND status='Ativo'")->fetchColumn() === '1004', 'extension can change');
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_user_extensions WHERE user_id=10 AND status='Inativo'")->fetchColumn() === 1, 'previous link is preserved as history');

$assign($db, 1, 10, '', false);
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_user_extensions WHERE user_id=10 AND status='Ativo'")->fetchColumn() === 0, 'deactivation releases extension');
$assign($db, 1, 11, '1004');
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_user_extensions WHERE user_id=11 AND extension='1004' AND status='Ativo'")->fetchColumn() === 1, 'released extension can be reassigned');
$assign($db, 2, 12, '1004');
$assert((int)$db->query("SELECT COUNT(*) FROM asterisk_user_extensions WHERE extension='1004' AND status='Ativo'")->fetchColumn() === 2, 'same extension remains isolated by tenant');

echo "OK - {$tests} tests\n";