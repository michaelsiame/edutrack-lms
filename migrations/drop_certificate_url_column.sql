-- Migration: Remove unused certificate_url column from certificates table
-- The certificate PDF is generated on demand via TCPDF and never stored as a file.
-- The certificate_url column was always NULL and served no purpose.

ALTER TABLE `certificates` DROP COLUMN `certificate_url`;
