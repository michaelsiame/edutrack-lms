-- Migration: Add session reminder tracking columns
-- Date: 2025-12-29
-- Description: Adds columns to track which reminders have been sent for live sessions

-- Add reminder tracking columns to live_sessions table
ALTER TABLE `live_sessions`
    ADD COLUMN `reminder_30_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '30-minute reminder sent',
    ADD COLUMN `reminder_5_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '5-minute reminder sent',
    ADD COLUMN `start_notification_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Starting now notification sent',
    ADD COLUMN `actual_start_time` DATETIME DEFAULT NULL COMMENT 'When the session actually started',
    ADD COLUMN `actual_end_time` DATETIME DEFAULT NULL COMMENT 'When the session actually ended';

-- Update status enum to include 'in_progress'
ALTER TABLE `live_sessions`
    MODIFY COLUMN `status` ENUM('scheduled', 'in_progress', 'live', 'ended', 'cancelled', 'completed') NOT NULL DEFAULT 'scheduled';

-- Add index for reminder queries
CREATE INDEX idx_live_session_reminders ON live_sessions(status, scheduled_start_time, reminder_30_sent, reminder_5_sent);
