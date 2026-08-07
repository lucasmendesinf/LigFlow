-- SQLite forward migration for Asterisk extensions assigned to LigFlow users.
CREATE TABLE IF NOT EXISTS asterisk_user_extensions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    asterisk_server_id INTEGER NOT NULL DEFAULT 1,
    extension TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Ativo',
    provisioning_status TEXT NOT NULL DEFAULT 'Pendente',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
    deactivated_at TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS idx_asterisk_user_extensions_active_extension
    ON asterisk_user_extensions(company_id, asterisk_server_id, extension)
    WHERE status = 'Ativo';
CREATE UNIQUE INDEX IF NOT EXISTS idx_asterisk_user_extensions_active_user
    ON asterisk_user_extensions(company_id, user_id, asterisk_server_id)
    WHERE status = 'Ativo';

-- Rollback:
-- DROP INDEX IF EXISTS idx_asterisk_user_extensions_active_user;
-- DROP INDEX IF EXISTS idx_asterisk_user_extensions_active_extension;
-- DROP TABLE IF EXISTS asterisk_user_extensions;