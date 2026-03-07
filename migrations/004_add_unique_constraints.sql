-- Migration: Add UNIQUE constraints to prevent duplicate data
-- Date: 2026-03-01
-- Purpose: Prevent race conditions and data integrity issues

-- Prevent duplicate enrollments (same user in same course)
-- This is the primary defense against race conditions in Enrollment::create()
ALTER TABLE enrollments 
ADD CONSTRAINT uk_enrollments_user_course 
UNIQUE (user_id, course_id);

-- Prevent duplicate reviews (one review per user per course)
-- Defends against race conditions in Review::create()
ALTER TABLE course_reviews 
ADD CONSTRAINT uk_reviews_user_course 
UNIQUE (user_id, course_id);

-- Prevent duplicate certificates for same user/course
-- Defends against race conditions in Certificate::generate()
ALTER TABLE certificates 
ADD CONSTRAINT uk_certificates_user_course 
UNIQUE (user_id, course_id);

-- Prevent duplicate certificates for same enrollment (if using enrollment_id)
ALTER TABLE certificates 
ADD CONSTRAINT uk_certificates_enrollment 
UNIQUE (enrollment_id);

-- Prevent duplicate assignment submissions
ALTER TABLE assignment_submissions 
ADD CONSTRAINT uk_submissions_student_assignment 
UNIQUE (student_id, assignment_id);

-- Prevent duplicate payment references
ALTER TABLE payments 
ADD CONSTRAINT uk_payments_transaction_id 
UNIQUE (transaction_id);

-- Prevent duplicate certificate numbers
ALTER TABLE certificates 
ADD CONSTRAINT uk_certificates_number 
UNIQUE (certificate_number);

-- Prevent duplicate verification codes
ALTER TABLE certificates 
ADD CONSTRAINT uk_certificates_verify_code 
UNIQUE (verification_code);

-- Add composite indexes for faster lookups on commonly queried fields
CREATE INDEX idx_enrollments_status ON enrollments(enrollment_status);
CREATE INDEX idx_lesson_progress_user_lesson ON lesson_progress(user_id, lesson_id);
CREATE INDEX idx_quiz_attempts_student_quiz ON quiz_attempts(student_id, quiz_id);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at);
CREATE INDEX idx_payments_user_status ON payments(user_id, payment_status);
CREATE INDEX idx_rate_limits_identifier ON rate_limits(identifier, expires_at);
CREATE INDEX idx_email_queue_status ON email_queue(status, scheduled_at);

-- Add foreign key constraints for data integrity
-- Note: These require the referenced tables to have proper indexes

-- Enrollments reference users and courses
ALTER TABLE enrollments 
ADD CONSTRAINT fk_enrollments_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE enrollments 
ADD CONSTRAINT fk_enrollments_course 
FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;

-- Course reviews reference users and courses
ALTER TABLE course_reviews 
ADD CONSTRAINT fk_reviews_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE course_reviews 
ADD CONSTRAINT fk_reviews_course 
FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;

-- Certificates reference users and courses
ALTER TABLE certificates 
ADD CONSTRAINT fk_certificates_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE certificates 
ADD CONSTRAINT fk_certificates_course 
FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;
