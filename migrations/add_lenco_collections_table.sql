-- Migration: Add lenco_collections table for V2 API mobile money collections
-- Date: 2026-03-07
-- Purpose: Store Lenco v2 mobile money collection requests

CREATE TABLE IF NOT EXISTS `lenco_collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lenco_collection_id` varchar(100) DEFAULT NULL COMMENT 'Lenco collection ID',
  `reference` varchar(100) NOT NULL COMMENT 'Our reference',
  `lenco_reference` varchar(100) DEFAULT NULL COMMENT 'Lenco reference',
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'ZMW',
  `phone` varchar(20) NOT NULL COMMENT 'Customer phone number',
  `country` varchar(2) NOT NULL DEFAULT 'ZM',
  `status` enum('pending','pay-offline','successful','failed') DEFAULT 'pending',
  `operator_transaction_id` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'registration_fee' COMMENT 'registration_fee or course_payment',
  `fee` decimal(10,2) DEFAULT NULL,
  `settlement_status` varchar(20) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lenco_collection_id` (`lenco_collection_id`),
  UNIQUE KEY `uk_reference` (`reference`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'lenco_collections table created successfully' AS status;
