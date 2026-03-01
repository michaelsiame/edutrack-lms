-- Migration: Add UNIQUE constraints to prevent duplicate data
-- Date: 2026-03-01
-- Purpose: Prevent race conditions and data integrity issues

-- Prevent duplicate enrollments (same user in same course)
ALTER TABLE enrollments 
ADD CONSTRAINT uk_enrollments_user_course 
UNIQUE (user_id, course_id);

-- Prevent duplicate reviews (one review per user per course)
ALTER TABLE course_reviews 
ADD CONSTRAINT uk_reviews_user_course 
UNIQUE (user_id, course_id);

-- Prevent duplicate certificates for same enrollment
ALTER TABLE certificates 
ADD CONSTRAINT uk_certificates_enrollment 
UNIQUE (enrollment_id);

-- Prevent duplicate quiz attempts for same student/quiz (optional - depending on business logic)
-- If multiple attempts are allowed, this should be removed or modified
-- ALTER TABLE quiz_attempts 
-- ADD CONSTRAINT uk_quiz_attempts_student_quiz 
-- UNIQUE (student_id, quiz_id);

-- Prevent duplicate assignment submissions
ALTER TABLE assignment_submissions 
ADD CONSTRAINT uk_submissions_student_assignment 
UNIQUE (student_id, assignment_id);

-- Prevent duplicate payment references
ALTER TABLE payments 
ADD CONSTRAINT uk_payments_transaction_id 
UNIQUE (transaction_id);

-- Add index for faster lookups on commonly queried fields
CREATE INDEX idx_enrollments_status ON enrollments(enrollment_status);
CREATE INDEX idx_lesson_progress_user_lesson ON lesson_progress(user_id, lesson_id);
CREATE INDEX idx_quiz_attempts_student_quiz ON quiz_attempts(student_id, quiz_id);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at);
CREATE INDEX idx_payments_user_status ON payments(user_id, payment_status);
CREATE INDEX idx_rate_limits_identifier ON rate_limits(identifier, expires_at);
CREATE INDEX idx_email_queue_status ON email_queue(status, scheduled_at);
