<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$migrations = [
    '2019_12_14_000001_create_personal_access_tokens_table',
    '2024_01_01_000001_create_users_table',
    '2024_01_01_000002_create_course_categories_table',
    '2024_01_01_000003_create_instructors_table',
    '2024_01_01_000004_create_courses_table',
    '2024_01_01_000005_create_modules_table',
    '2024_01_01_000006_create_lessons_table',
    '2024_01_01_000007_create_enrollments_table',
    '2024_01_01_000008_create_certificates_table',
    '2024_01_01_000009_create_payments_table',
    '2024_01_01_000010_create_quizzes_table',
    '2024_01_01_000011_create_assignments_table',
    '2024_01_01_000012_create_user_roles_table',
    '2024_01_01_000013_create_user_profiles_table',
    '2024_01_01_000014_create_questions_table',
    '2024_01_01_000015_create_quiz_questions_table',
    '2024_01_01_000016_create_question_options_table',
    '2024_01_01_000017_create_quiz_attempts_table',
    '2024_01_01_000018_create_quiz_answers_table',
    '2024_01_01_000019_create_assignment_submissions_table',
    '2024_01_01_000020_create_announcements_table',
    '2024_01_01_000021_create_activity_logs_table',
    '2024_01_01_000022_create_email_queue_table',
    '2024_01_01_000023_create_payment_methods_table',
    '2024_01_01_000024_create_live_sessions_table',
    '2024_01_01_000025_create_live_session_attendance_table',
    '2024_01_01_000026_create_lesson_progress_table',
    '2024_01_01_000027_create_course_reviews_table',
    '2024_01_01_000028_create_enrollment_payment_plans_table',
    '2024_01_01_000029_create_notifications_table',
    '2024_01_01_000030_create_lenco_transactions_table',
    '2024_01_01_000031_create_lenco_webhook_logs_table',
    '2024_01_01_000032_create_system_settings_table',
    '2024_01_01_000033_create_students_table',
    '2024_01_01_000034_create_roles_table',
    '2024_01_01_000035_create_remember_tokens_table',
    '2024_01_01_000036_create_registration_fees_table',
    '2024_01_01_000037_create_badges_table',
    '2024_01_01_000038_create_contacts_table',
    '2024_01_01_000039_create_course_instructors_table',
    '2024_01_01_000040_create_discussions_table',
    '2024_01_01_000041_create_discussion_replies_table',
    '2024_01_01_000042_create_email_templates_table',
    '2024_01_01_000043_create_lesson_resources_table',
    '2024_01_01_000044_create_messages_table',
    '2024_01_01_000045_create_quiz_question_options_table',
    '2024_01_01_000046_create_student_achievements_table',
    '2024_01_01_000047_create_team_members_table',
    '2024_01_01_000048_create_transactions_table',
    '2024_01_01_000049_create_user_sessions_table',
    '2024_01_02_000001_create_testimonials_table',
    '2024_01_02_000002_create_events_table',
    '2024_01_02_000003_create_hero_slides_table',
    '2024_01_02_000004_create_institution_photos_table', // Wait, the file is 2026_05_09
    '2026_05_09_234622_create_institution_photos_table',
    '2026_05_11_121305_create_lesson_notes_table',
];

foreach ($migrations as $migration) {
    DB::table('migrations')->updateOrInsert(
        ['migration' => $migration],
        ['batch' => 1]
    );
}

echo "Migrations marked.\n";
