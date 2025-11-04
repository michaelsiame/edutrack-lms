-- Migration: Add status column to enrollments table
-- This adds a 'status' column that mirrors 'enrollment_status' for backward compatibility
-- Date: 2025-11-04

USE edutrack_lms;

-- Add the status column (same as enrollment_status)
ALTER TABLE `enrollments`
ADD COLUMN `status` enum('active','completed','dropped','suspended') DEFAULT 'active'
AFTER `enrollment_status`;

-- Copy existing data from enrollment_status to status
UPDATE `enrollments` SET `status` = `enrollment_status`;

-- Create a trigger to keep status in sync with enrollment_status on INSERT
DELIMITER $$
CREATE TRIGGER `enrollments_status_insert`
BEFORE INSERT ON `enrollments`
FOR EACH ROW
BEGIN
    IF NEW.status IS NULL THEN
        SET NEW.status = NEW.enrollment_status;
    END IF;
    SET NEW.enrollment_status = NEW.status;
END$$
DELIMITER ;

-- Create a trigger to keep status in sync with enrollment_status on UPDATE
DELIMITER $$
CREATE TRIGGER `enrollments_status_update`
BEFORE UPDATE ON `enrollments`
FOR EACH ROW
BEGIN
    -- If enrollment_status changed, update status
    IF NEW.enrollment_status != OLD.enrollment_status THEN
        SET NEW.status = NEW.enrollment_status;
    END IF;
    -- If status changed, update enrollment_status
    IF NEW.status != OLD.status THEN
        SET NEW.enrollment_status = NEW.status;
    END IF;
END$$
DELIMITER ;
