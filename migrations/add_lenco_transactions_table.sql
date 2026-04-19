-- Migration: Add lenco_transactions table for virtual account payments
-- Date: 2026-04-09
-- Purpose: Store Lenco virtual account payment transactions

CREATE TABLE IF NOT EXISTS `lenco_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) NOT NULL COMMENT 'Unique payment reference',
  `user_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW',
  `virtual_account_number` varchar(50) DEFAULT NULL,
  `virtual_account_bank` varchar(100) DEFAULT NULL,
  `virtual_account_name` varchar(100) DEFAULT NULL,
  `status` enum('pending','successful','failed','expired') DEFAULT 'pending',
  `lenco_transaction_id` varchar(100) DEFAULT NULL COMMENT 'Lenco transaction ID from webhook',
  `payer_account_number` varchar(50) DEFAULT NULL COMMENT 'Actual payer bank account',
  `payer_account_name` varchar(100) DEFAULT NULL COMMENT 'Payer name from bank',
  `payer_bank` varchar(100) DEFAULT NULL COMMENT 'Payer bank name',
  `paid_amount` decimal(10,2) DEFAULT NULL COMMENT 'Actual amount paid',
  `paid_at` datetime DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reference` (`reference`),
  UNIQUE KEY `uk_lenco_tx_id` (`lenco_transaction_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_enrollment_id` (`enrollment_id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'lenco_transactions table created successfully' AS status;
