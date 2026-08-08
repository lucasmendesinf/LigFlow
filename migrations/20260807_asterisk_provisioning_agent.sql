-- Etapa 3.2: faixa configurável, agente de provisionamento e segredos por ramal.
ALTER TABLE asterisk_settings ADD COLUMN extension_start INTEGER NOT NULL DEFAULT 1000;
ALTER TABLE asterisk_settings ADD COLUMN extension_end INTEGER NOT NULL DEFAULT 9999;
ALTER TABLE asterisk_settings ADD COLUMN provisioning_agent_url TEXT;
ALTER TABLE asterisk_settings ADD COLUMN provisioning_agent_secret_encrypted TEXT;
ALTER TABLE asterisk_settings ADD COLUMN provisioning_agent_timeout_seconds INTEGER NOT NULL DEFAULT 10;
ALTER TABLE asterisk_user_extensions ADD COLUMN sip_password_encrypted TEXT;
ALTER TABLE asterisk_provisioning_jobs ADD COLUMN response_json TEXT;
