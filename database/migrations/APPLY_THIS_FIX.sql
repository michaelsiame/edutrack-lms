-- ============================================================================
-- FIX FOR: Unknown column 'status' in 'where clause' error
-- ============================================================================
-- This adds a 'status' column to the enrollments table that mirrors
-- 'enrollment_status' to maintain compatibility with code that uses 'status'
--
-- HOW TO APPLY:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Select the 'edutrack_lms' database
-- 3. Click on 'SQL' tab
-- 4. Copy and paste this ENTIRE file
-- 5. Click 'Go' to execute
-- ============================================================================

-- Add the status column (same enum values as enrollment_status)
ALTER TABLE `enrollments`
ADD COLUMN `status` enum('active','completed','dropped','suspended') DEFAULT 'active'
AFTER `enrollment_status`;

-- Copy existing data from enrollment_status to status
UPDATE `enrollments` SET `status` = `enrollment_status`;

-- Create trigger to sync status with enrollment_status on INSERT
DROP TRIGGER IF EXISTS `enrollments_status_insert`;
DELIMITER $$
CREATE TRIGGER `enrollments_status_insert`
BEFORE INSERT ON `enrollments`
FOR EACH ROW
BEGIN
    -- Keep both columns in sync
    IF NEW.status IS NULL THEN
        SET NEW.status = NEW.enrollment_status;
    END IF;
    IF NEW.enrollment_status IS NULL THEN
        SET NEW.enrollment_status = NEW.status;
    END IF;
END$$
DELIMITER ;

-- Create trigger to sync status with enrollment_status on UPDATE
DROP TRIGGER IF EXISTS `enrollments_status_update`;
DELIMITER $$
CREATE TRIGGER `enrollments_status_update`
BEFORE UPDATE ON `enrollments`
FOR EACH ROW
BEGIN
    -- If enrollment_status changed, sync status
    IF NEW.enrollment_status != OLD.enrollment_status THEN
        SET NEW.status = NEW.enrollment_status;
    END IF;
    -- If status changed, sync enrollment_status
    IF NEW.status != OLD.status THEN
        SET NEW.enrollment_status = NEW.status;
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- DONE! The 'status' column is now available and will stay in sync with
-- 'enrollment_status' automatically via triggers.
-- ============================================================================
