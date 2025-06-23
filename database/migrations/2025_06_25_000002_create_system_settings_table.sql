-- AlingAi Pro Project
-- Migration for creating the system_settings table
-- This table stores dynamic configuration settings for the application.
--
-- Migration File: 2025_06_25_000002_create_system_settings_table.sql
-- Timestamp: 2025-06-25 10:00:00

-- Create the system_settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(50) NOT NULL DEFAULT 'string', -- e.g., string, integer, boolean, json
    description TEXT,
    is_dynamic BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create a trigger to automatically update the updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_system_settings_updated_at
AFTER UPDATE ON system_settings
FOR EACH ROW
BEGIN
    UPDATE system_settings SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id;
END;

-- Pre-populate the table with settings from config/app.php
INSERT OR IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
    ('app.name', 'AlingAi Pro', 'string', 'The name of the application.'),
    ('app.version', '5.0.0', 'string', 'Current version of the application.'),
    ('app.environment', 'production', 'string', 'Application environment (e.g., development, production).'),
    ('app.debug', 'false', 'boolean', 'Enable or disable debug mode.'),
    ('app.url', 'https://alingai.com', 'string', 'The base URL of the application.'),
    ('app.timezone', 'Asia/Shanghai', 'string', 'The default timezone for the application.'),
    ('app.locale', 'zh_CN', 'string', 'The default language locale.'),
    ('app.fallback_locale', 'en_US', 'string', 'The fallback language locale.'),
    ('app.key', 'base64:gqkDP+rhNcmcDZoTfJijKtRBgSXYBsrz+CSDhRZ5qNM=', 'string', 'Application encryption key. Should not be changed lightly.'),
    ('app.cipher', 'AES-256-CBC', 'string', 'The encryption cipher to use.'),
    ('features.ai_integration', 'true', 'boolean', 'Enable or disable AI integration features.'),
    ('features.quantum_security', 'true', 'boolean', 'Enable or disable Quantum Security Module.'),
    ('features.zero_trust', 'true', 'boolean', 'Enable or disable Zero Trust Authentication.'),
    ('features.real_time_monitoring', 'true', 'boolean', 'Enable or disable real-time monitoring.'),
    ('features.multi_language', 'true', 'boolean', 'Enable or disable multi-language support.'),
    ('features.api_versioning', 'true', 'boolean', 'Enable or disable API versioning.'),
    ('limits.max_upload_size', '100M', 'string', 'Maximum file upload size.'),
    ('limits.max_file_uploads', '20', 'integer', 'Maximum number of simultaneous file uploads.'),
    ('limits.request_timeout', '300', 'integer', 'Request timeout in seconds.'),
    ('limits.memory_limit', '512M', 'string', 'PHP memory limit.'); 