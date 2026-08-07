-- SQLite forward migration for Asterisk campaign parallel dialing.
ALTER TABLE campaigns ADD COLUMN simultaneous_calls INTEGER NOT NULL DEFAULT 1;
ALTER TABLE calls ADD COLUMN dial_batch_id INTEGER;
ALTER TABLE calls ADD COLUMN race_outcome TEXT;
CREATE TABLE IF NOT EXISTS dial_batches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    campaign_id INTEGER NOT NULL,
    agent_id INTEGER NOT NULL,
    requested_parallelism INTEGER NOT NULL DEFAULT 1,
    effective_parallelism INTEGER NOT NULL DEFAULT 1,
    telephony_mode TEXT NOT NULL,
    telephony_trunk TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'ORIGINATING',
    winner_call_id INTEGER,
    idempotency_key TEXT NOT NULL UNIQUE,
    next_started_at TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_dial_batches_active_agent ON dial_batches(company_id, agent_id, status, id DESC);
CREATE INDEX IF NOT EXISTS idx_dial_batches_campaign_created ON dial_batches(company_id, campaign_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_calls_dial_batch ON calls(dial_batch_id, status, id);

-- Rollback (SQLite requires table rebuild to remove columns):
-- DROP TABLE IF EXISTS dial_batches;
-- DROP INDEX IF EXISTS idx_dial_batches_active_agent;
-- DROP INDEX IF EXISTS idx_dial_batches_campaign_created;
-- DROP INDEX IF EXISTS idx_calls_dial_batch;
-- Rebuild campaigns/calls without simultaneous_calls/dial_batch_id/race_outcome only after a verified backup.