-- Migration: Add phone_number column to registration_fees table
-- Date: 2026-03-07
-- Purpose: Support mobile money payments for registration fee

-- Add phone_number column
ALTER TABLE registration_fees 
ADD COLUMN phone_number VARCHAR(20) DEFAULT NULL COMMENT 'Mobile money phone number' AFTER deposit_date;

-- Update payment_method enum to include mobile_money
ALTER TABLE registration_fees 
MODIFY COLUMN payment_method ENUM('bank_transfer','bank_deposit','mobile_money') NOT NULL DEFAULT 'bank_deposit';

-- Add index for phone number lookups
CREATE INDEX idx_registration_fees_phone ON registration_fees(phone_number);

SELECT 'Migration completed successfully' AS status;
