-- Migration: Add google_id column to users table for Google OAuth
-- Run this migration before deploying Google Sign-In

ALTER TABLE `users`
ADD COLUMN `google_id` VARCHAR(255) NULL DEFAULT NULL AFTER `email`,
ADD UNIQUE INDEX `idx_users_google_id` (`google_id`);
