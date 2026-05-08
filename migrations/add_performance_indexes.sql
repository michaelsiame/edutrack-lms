-- Performance indexes for Edutrack LMS
-- Run this on production to dramatically improve page load times

-- courses.status is filtered in almost every course listing query
ALTER TABLE courses ADD INDEX idx_courses_status (status);

-- enrollments.enrollment_status is filtered in dashboard, my-courses, stats
ALTER TABLE enrollments ADD INDEX idx_enrollments_status (enrollment_status);

-- Composite index for dashboard active/completed course queries
ALTER TABLE enrollments ADD INDEX idx_enrollments_user_status (user_id, enrollment_status);

-- lesson_progress.status is used for progress calculations and dashboard activity chart
ALTER TABLE lesson_progress ADD INDEX idx_lesson_progress_status (status);

-- lesson_progress.enrollment_id + status for progress lookups
ALTER TABLE lesson_progress ADD INDEX idx_lesson_progress_enrollment_status (enrollment_id, status);

-- lesson_progress.last_accessed for "resume where left off" queries
ALTER TABLE lesson_progress ADD INDEX idx_lesson_progress_last_accessed (last_accessed);

-- assignments.due_date for upcoming deadlines dashboard widget
ALTER TABLE assignments ADD INDEX idx_assignments_due_date (due_date);

-- assignments.course_id for course assignment lookups
ALTER TABLE assignments ADD INDEX idx_assignments_course (course_id);

-- quizzes.is_published for filtering published quizzes
ALTER TABLE quizzes ADD INDEX idx_quizzes_published (is_published);

-- quizzes.course_id for course quiz lookups
ALTER TABLE quizzes ADD INDEX idx_quizzes_course (course_id);

-- notifications.user_id + is_read for unread notification count
ALTER TABLE notifications ADD INDEX idx_notifications_user_read (user_id, is_read);

-- certificates.issued_date for recent achievements ordering
ALTER TABLE certificates ADD INDEX idx_certificates_issued (issued_date);

-- enrollments.course_id + user_id for enrollment lookups
ALTER TABLE enrollments ADD INDEX idx_enrollments_course_user (course_id, user_id);
