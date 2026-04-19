-- Add consent tracking to testimonials table
ALTER TABLE testimonials 
ADD COLUMN consent_given_at TIMESTAMP NULL DEFAULT NULL COMMENT 'When the student gave permission to use their testimonial',
ADD COLUMN consent_type ENUM('written', 'verbal', 'email') DEFAULT 'written' COMMENT 'Type of consent obtained',
ADD COLUMN show_company BOOLEAN DEFAULT TRUE COMMENT 'Whether to display company name publicly';

-- Update existing testimonials to mark as consented (backfill)
UPDATE testimonials SET consent_given_at = created_at WHERE consent_given_at IS NULL;
