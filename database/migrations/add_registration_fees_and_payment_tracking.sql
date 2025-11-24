-- =====================================================
-- EDUTRACK COMPUTER TRAINING COLLEGE
-- Registration Fee & Partial Payment Tracking System
-- =====================================================
--
-- This migration adds:
-- 1. Finance/Accountant role for handling cash payments
-- 2. Registration fees table (K150 one-time fee per student)
-- 3. Payment tracking for partial payments
-- 4. Certificate blocking until fully paid
-- =====================================================

-- =====================================================
-- 1. ADD FINANCE/ACCOUNTANT ROLE
-- =====================================================

INSERT INTO `roles` (`id`, `role_name`, `description`, `permissions`, `created_at`) VALUES
(6, 'Finance', 'Financial operations and cash payment management',
 '{"payments": ["create", "read", "update"], "students": ["read"], "enrollments": ["read"], "reports": ["read"]}',
 NOW());

-- =====================================================
-- 2. REGISTRATION FEES TABLE
-- One-time K150 fee per student (paid to bank account)
-- =====================================================

CREATE TABLE IF NOT EXISTS `registration_fees` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `student_id` INT(11) DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 150.00,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'ZMW',
    `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `payment_method` ENUM('bank_transfer', 'bank_deposit') NOT NULL DEFAULT 'bank_deposit',
    `bank_reference` VARCHAR(100) DEFAULT NULL COMMENT 'Bank deposit slip or transfer reference',
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `deposit_date` DATE DEFAULT NULL,
    `verified_by` INT(11) DEFAULT NULL COMMENT 'Admin/Finance user who verified the payment',
    `verified_at` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_registration` (`user_id`),
    KEY `idx_student_id` (`student_id`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_verified_by` (`verified_by`),
    CONSTRAINT `fk_regfee_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_regfee_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_regfee_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. ENROLLMENT PAYMENT PLANS TABLE
-- Tracks total fees and balance for each enrollment
-- =====================================================

CREATE TABLE IF NOT EXISTS `enrollment_payment_plans` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `enrollment_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `course_id` INT(11) NOT NULL,
    `total_fee` DECIMAL(10,2) NOT NULL COMMENT 'Full course fee',
    `total_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `balance` DECIMAL(10,2) GENERATED ALWAYS AS (`total_fee` - `total_paid`) STORED,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'ZMW',
    `payment_status` ENUM('pending', 'partial', 'completed', 'overdue') DEFAULT 'pending',
    `due_date` DATE DEFAULT NULL COMMENT 'Final payment due date',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_enrollment_plan` (`enrollment_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_course_id` (`course_id`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_balance` (`balance`),
    CONSTRAINT `fk_plan_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_plan_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. MODIFY PAYMENTS TABLE
-- Add payment type and recording info for cash payments
-- =====================================================

-- Add payment_type column if not exists
ALTER TABLE `payments`
ADD COLUMN IF NOT EXISTS `payment_type` ENUM('registration', 'course_fee', 'partial_payment')
DEFAULT 'course_fee' AFTER `payment_method_id`;

-- Add recorded_by for cash payments (finance staff who recorded it)
ALTER TABLE `payments`
ADD COLUMN IF NOT EXISTS `recorded_by` INT(11) DEFAULT NULL
COMMENT 'User ID of admin/finance who recorded cash payment' AFTER `payment_type`;

-- Add enrollment_payment_plan_id reference
ALTER TABLE `payments`
ADD COLUMN IF NOT EXISTS `payment_plan_id` INT(11) DEFAULT NULL AFTER `enrollment_id`;

-- Add phone number for mobile money reference
ALTER TABLE `payments`
ADD COLUMN IF NOT EXISTS `phone_number` VARCHAR(20) DEFAULT NULL AFTER `transaction_id`;

-- Add notes for additional details
ALTER TABLE `payments`
ADD COLUMN IF NOT EXISTS `notes` TEXT DEFAULT NULL AFTER `phone_number`;

-- Add foreign key for recorded_by
ALTER TABLE `payments`
ADD CONSTRAINT `fk_payment_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add foreign key for payment_plan_id
ALTER TABLE `payments`
ADD CONSTRAINT `fk_payment_plan` FOREIGN KEY (`payment_plan_id`) REFERENCES `enrollment_payment_plans` (`id`) ON DELETE SET NULL;

-- =====================================================
-- 5. ADD certificate_blocked COLUMN TO ENROLLMENTS
-- Block certificate if balance > 0
-- =====================================================

ALTER TABLE `enrollments`
ADD COLUMN IF NOT EXISTS `certificate_blocked` TINYINT(1) DEFAULT 0
COMMENT 'Certificate blocked until fully paid' AFTER `certificate_issued`;

-- =====================================================
-- 6. CREATE VIEW FOR STUDENT BALANCES
-- Easy lookup of student payment status
-- =====================================================

CREATE OR REPLACE VIEW `v_student_balances` AS
SELECT
    u.id as user_id,
    u.username,
    CONCAT(u.first_name, ' ', u.last_name) as full_name,
    u.email,
    s.id as student_id,
    rf.id as registration_fee_id,
    rf.payment_status as registration_status,
    rf.amount as registration_amount,
    COALESCE(epp.total_courses, 0) as total_courses,
    COALESCE(epp.total_fees, 0) as total_course_fees,
    COALESCE(epp.total_paid, 0) as total_paid,
    COALESCE(epp.total_balance, 0) as total_balance,
    CASE
        WHEN rf.payment_status != 'completed' OR rf.payment_status IS NULL THEN 'registration_pending'
        WHEN COALESCE(epp.total_balance, 0) > 0 THEN 'balance_owing'
        ELSE 'cleared'
    END as overall_status
FROM users u
LEFT JOIN students s ON s.user_id = u.id
LEFT JOIN registration_fees rf ON rf.user_id = u.id
LEFT JOIN (
    SELECT
        user_id,
        COUNT(*) as total_courses,
        SUM(total_fee) as total_fees,
        SUM(total_paid) as total_paid,
        SUM(balance) as total_balance
    FROM enrollment_payment_plans
    GROUP BY user_id
) epp ON epp.user_id = u.id
WHERE EXISTS (SELECT 1 FROM user_roles ur WHERE ur.user_id = u.id AND ur.role_id = 4);

-- =====================================================
-- 7. CREATE VIEW FOR PENDING VERIFICATIONS
-- For finance dashboard
-- =====================================================

CREATE OR REPLACE VIEW `v_pending_verifications` AS
SELECT
    'registration' as fee_type,
    rf.id as fee_id,
    rf.user_id,
    u.username,
    CONCAT(u.first_name, ' ', u.last_name) as full_name,
    u.email,
    NULL as course_id,
    NULL as course_title,
    rf.amount,
    rf.currency,
    rf.payment_method,
    rf.bank_reference as reference,
    rf.deposit_date as payment_date,
    rf.created_at
FROM registration_fees rf
JOIN users u ON u.id = rf.user_id
WHERE rf.payment_status = 'pending'

UNION ALL

SELECT
    'course_fee' as fee_type,
    p.payment_id as fee_id,
    p.student_id as user_id,
    u.username,
    CONCAT(u.first_name, ' ', u.last_name) as full_name,
    u.email,
    p.course_id,
    c.title as course_title,
    p.amount,
    p.currency,
    COALESCE(pm.method_name, 'Unknown') as payment_method,
    p.transaction_id as reference,
    p.payment_date,
    p.created_at
FROM payments p
JOIN students s ON s.id = p.student_id
JOIN users u ON u.id = s.user_id
JOIN courses c ON c.id = p.course_id
LEFT JOIN payment_methods pm ON pm.payment_method_id = p.payment_method_id
WHERE p.payment_status = 'Pending';

-- =====================================================
-- 8. UPDATE SYSTEM SETTINGS FOR REGISTRATION FEE
-- =====================================================

-- Create settings table if not exists
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT DEFAULT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert registration fee settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('registration_fee_amount', '150.00', 'number', 'Registration fee amount in ZMW'),
('registration_fee_required', 'true', 'boolean', 'Whether registration fee is required before enrollment'),
('bank_account_name', 'EDUTRACK Computer Training College', 'string', 'Bank account name for deposits'),
('bank_account_number', '', 'string', 'Bank account number for deposits'),
('bank_name', '', 'string', 'Bank name for deposits'),
('bank_branch', '', 'string', 'Bank branch for deposits'),
('currency', 'ZMW', 'string', 'Default currency (Zambian Kwacha)'),
('partial_payments_enabled', 'true', 'boolean', 'Allow partial payments for course fees'),
('certificate_requires_full_payment', 'true', 'boolean', 'Block certificate issuance until fully paid')
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Index for faster balance lookups
CREATE INDEX IF NOT EXISTS `idx_epp_balance` ON `enrollment_payment_plans` (`balance`);
CREATE INDEX IF NOT EXISTS `idx_epp_status` ON `enrollment_payment_plans` (`payment_status`);

-- =====================================================
-- MIGRATION COMPLETE
-- =====================================================
--
-- Next Steps:
-- 1. Update bank account details in system_settings
-- 2. Create Finance user accounts with role_id = 6
-- 3. Test registration fee flow
-- 4. Test partial payment recording
-- =====================================================
