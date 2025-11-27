-- Migration: Add live sessions support
-- Date: 2025-11-27
-- Description: Enables live lessons using Jitsi Meet integration

-- Live sessions table
CREATE TABLE IF NOT EXISTS `live_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `meeting_room_id` varchar(255) NOT NULL UNIQUE,
  `scheduled_start_time` datetime NOT NULL,
  `scheduled_end_time` datetime NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `status` enum('scheduled', 'live', 'ended', 'cancelled') NOT NULL DEFAULT 'scheduled',
  `max_participants` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recording_url` varchar(500) DEFAULT NULL,
  `moderator_password` varchar(100) DEFAULT NULL,
  `participant_password` varchar(100) DEFAULT NULL,
  `allow_recording` tinyint(1) NOT NULL DEFAULT 1,
  `auto_start_recording` tinyint(1) NOT NULL DEFAULT 0,
  `enable_chat` tinyint(1) NOT NULL DEFAULT 1,
  `enable_screen_share` tinyint(1) NOT NULL DEFAULT 1,
  `buffer_minutes_before` int(11) NOT NULL DEFAULT 15,
  `buffer_minutes_after` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `scheduled_start_time` (`scheduled_start_time`),
  KEY `status` (`status`),
  CONSTRAINT `fk_live_sessions_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_live_sessions_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Live session attendance tracking
CREATE TABLE IF NOT EXISTS `live_session_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `live_session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime NOT NULL,
  `left_at` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `is_moderator` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `live_session_id` (`live_session_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_attendance_live_session` FOREIGN KEY (`live_session_id`) REFERENCES `live_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for performance
CREATE INDEX idx_live_session_status_time ON live_sessions(status, scheduled_start_time);
CREATE INDEX idx_attendance_user_session ON live_session_attendance(user_id, live_session_id);
