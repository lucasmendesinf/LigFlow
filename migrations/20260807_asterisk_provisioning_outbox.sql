-- SQLite forward migration for the LigFlow Asterisk provisioning outbox.
-- The running application applies these additions versioned through migrate().
ALTER TABLE asterisk_user_extensions ADD COLUMN lifecycle_status TEXT NOT NULL DEFAULT 'ACTIVE';
ALTER TABLE asterisk_user_extensions ADD COLUMN provisioned_at TEXT;
ALTER TABLE asterisk_user_extensions ADD COLUMN last_provision_error TEXT;
ALTER TABLE asterisk_user_extensions ADD COLUMN provisioning_version INTEGER NOT NULL DEFAULT 1;
ALTER TABLE asterisk_user_extensions ADD COLUMN released_at TEXT;

CREATE TABLE IF NOT EXISTS asterisk_provisioning_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    asterisk_user_extension_id INTEGER NOT NULL,
    asterisk_server_id INTEGER NOT NULL DEFAULT 1,
    operation TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'PENDING',
    idempotency_key TEXT NOT NULL UNIQUE,
    attempts INTEGER NOT NULL DEFAULT 0,
    last_error TEXT,
    payload_json TEXT NOT NULL DEFAULT '{}',
    processing_started_at TEXT,
    completed_at TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_asterisk_user_extensions_lifecycle
    ON asterisk_user_extensions(company_id, asterisk_server_id, lifecycle_status, extension);
CREATE INDEX IF NOT EXISTS idx_asterisk_provisioning_jobs_extension_status
    ON asterisk_provisioning_jobs(asterisk_user_extension_id, status, id DESC);
CREATE INDEX IF NOT EXISTS idx_asterisk_provisioning_jobs_company_user
    ON asterisk_provisioning_jobs(company_id, user_id, id DESC);

-- Rollback (after no queued jobs reference the rows):
-- DROP INDEX IF EXISTS idx_asterisk_provisioning_jobs_company_user;
-- DROP INDEX IF EXISTS idx_asterisk_provisioning_jobs_extension_status;
-- DROP INDEX IF EXISTS idx_asterisk_user_extensions_lifecycle;
-- DROP TABLE IF EXISTS asterisk_provisioning_jobs;
-- SQLite 3.35+ supports DROP COLUMN for the five added lifecycle columns.