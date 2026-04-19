-- Migration: Add lenco_webhook_logs table
-- Date: 2026-04-09
-- Purpose: Store incoming Lenco webhook requests for debugging and audit

CREATE TABLE IF NOT EXISTS `lenco_webhook_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL COMMENT 'Event type (e.g., transaction.successful)',
  `payload` longtext NOT NULL COMMENT 'Full webhook payload JSON',
  `signature` varchar(255) DEFAULT NULL COMMENT 'X-Lenco-Signature header',
  `signature_valid` tinyint(1) DEFAULT NULL COMMENT 'Whether signature was verified',
  `processed` tinyint(1) DEFAULT 0 COMMENT 'Whether webhook was processed successfully',
  `error_message` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Remote IP address',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_processed` (`processed`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'lenco_webhook_logs table created successfully' AS status;
