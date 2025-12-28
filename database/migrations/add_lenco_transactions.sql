-- ==============================================
-- Lenco Payment Gateway Integration
-- Migration: Add lenco_transactions table
-- Date: 2024-12-28
-- ==============================================

-- Create lenco_transactions table for tracking Lenco payments
CREATE TABLE IF NOT EXISTS `lenco_transactions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Unique payment reference (LENCO-XXXX-TIMESTAMP)',
    `user_id` INT(11) UNSIGNED NOT NULL COMMENT 'FK to users table',
    `enrollment_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'FK to enrollments table',
    `course_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'FK to courses table',
    `amount` DECIMAL(15,2) NOT NULL COMMENT 'Expected payment amount',
    `currency` VARCHAR(3) NOT NULL DEFAULT 'ZMW' COMMENT 'Currency code (ZMW, NGN, USD)',
    `virtual_account_number` VARCHAR(50) DEFAULT NULL COMMENT 'Lenco virtual account number',
    `virtual_account_bank` VARCHAR(100) DEFAULT NULL COMMENT 'Bank name for virtual account',
    `virtual_account_name` VARCHAR(255) DEFAULT NULL COMMENT 'Account name displayed to payer',
    `lenco_account_id` VARCHAR(100) DEFAULT NULL COMMENT 'Lenco internal account ID',
    `lenco_transaction_id` VARCHAR(100) DEFAULT NULL COMMENT 'Lenco transaction ID (set on completion)',
    `status` ENUM('pending', 'successful', 'failed', 'expired', 'reversed') NOT NULL DEFAULT 'pending',
    `paid_at` DATETIME DEFAULT NULL COMMENT 'When payment was confirmed',
    `expires_at` DATETIME DEFAULT NULL COMMENT 'When this payment request expires',
    `metadata` JSON DEFAULT NULL COMMENT 'Additional transaction metadata',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_reference` (`reference`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_enrollment_id` (`enrollment_id`),
    KEY `idx_course_id` (`course_id`),
    KEY `idx_status` (`status`),
    KEY `idx_virtual_account` (`virtual_account_number`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_lenco_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lenco_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_lenco_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks Lenco payment gateway transactions';

-- Create lenco_webhook_logs table for debugging
CREATE TABLE IF NOT EXISTS `lenco_webhook_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type` VARCHAR(100) NOT NULL COMMENT 'Webhook event type',
    `payload` JSON NOT NULL COMMENT 'Raw webhook payload',
    `signature` VARCHAR(255) DEFAULT NULL COMMENT 'Webhook signature',
    `signature_valid` TINYINT(1) DEFAULT NULL COMMENT 'Was signature valid?',
    `processed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Has this been processed?',
    `error_message` TEXT DEFAULT NULL COMMENT 'Error if processing failed',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Source IP address',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_processed` (`processed`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs incoming Lenco webhooks for debugging';

-- Add Lenco as a payment method if not exists
INSERT INTO `payment_methods` (`method_name`, `description`, `is_active`, `processing_fee`, `min_amount`, `max_amount`)
SELECT 'Lenco Bank Transfer', 'Pay via bank transfer using Lenco virtual accounts', 1, 0.00, 10.00, 1000000.00
WHERE NOT EXISTS (
    SELECT 1 FROM `payment_methods` WHERE `method_name` LIKE '%Lenco%'
);

-- Add index on payments table for better Lenco transaction lookups
-- Only add if not exists
SET @index_exists = (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = DATABASE()
    AND table_name = 'payments'
    AND index_name = 'idx_transaction_id'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE `payments` ADD INDEX `idx_transaction_id` (`transaction_id`)',
    'SELECT "Index already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ==============================================
-- Scheduled Event: Clean up expired transactions
-- ==============================================
-- Run every hour to mark expired pending transactions
-- Note: Ensure event_scheduler is ON in MySQL config

DELIMITER //

DROP EVENT IF EXISTS `cleanup_expired_lenco_transactions`//

CREATE EVENT IF NOT EXISTS `cleanup_expired_lenco_transactions`
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE `lenco_transactions`
    SET `status` = 'expired', `updated_at` = NOW()
    WHERE `status` = 'pending'
    AND `expires_at` < NOW();
END//

DELIMITER ;

-- ==============================================
-- Sample data for testing (comment out in production)
-- ==============================================
-- INSERT INTO lenco_transactions (reference, user_id, amount, currency, status)
-- VALUES ('LENCO-TEST-123456789', 1, 500.00, 'ZMW', 'pending');
