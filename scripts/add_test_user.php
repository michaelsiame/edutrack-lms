<?php
/**
 * Add Test User Graduate
 * Creates a test user enrolled in Microsoft Office Suite with certificate
 * 
 * Login: testuser / TestPass123!
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/../src/bootstrap.php';

$db = Database::getInstance();

echo "=== Adding Test User Graduate ===\n\n";

$courseId = 1;
$adminUserId = 1;
$testPassword = 'TestPass123!';
$passwordHash = password_hash($testPassword, PASSWORD_BCRYPT);

// ============================================================
// STEP 1: Create test user
// ============================================================
echo "Step 1: Creating test user...\n";

$existing = $db->fetchOne("SELECT id FROM users WHERE username = ?", ['testuser']);
if ($existing) {
    $userId = $existing['id'];
    echo "  - Found existing test user: $userId\n";
    // Update password to known value
    $db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$passwordHash, $userId]);
    echo "  - Updated password to: $testPassword\n";
} else {
    $db->query("INSERT INTO users (username, email, password_hash, first_name, last_name, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())", [
        'testuser',
        'testuser@edutrack.edu',
        $passwordHash,
        'Test',
        'User'
    ]);
    $userId = $db->lastInsertId();
    echo "  - Created test user: $userId\n";
    echo "  - Username: testuser\n";
    echo "  - Password: $testPassword\n";
}

// ============================================================
// STEP 2: Create student record
// ============================================================
echo "\nStep 2: Creating student record...\n";

$existingStudent = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
if ($existingStudent) {
    $studentId = $existingStudent['id'];
    echo "  - Found existing student record: $studentId\n";
} else {
    $db->query("INSERT INTO students (user_id, enrollment_date, total_courses_enrolled, total_courses_completed, total_certificates, created_at, updated_at) VALUES (?, CURDATE(), 0, 0, 0, NOW(), NOW())", [$userId]);
    $studentId = $db->lastInsertId();
    echo "  - Created student record: $studentId\n";
}

// ============================================================
// STEP 3: Assign student role
// ============================================================
echo "\nStep 3: Assigning student role...\n";

$existingRole = $db->fetchOne("SELECT id FROM user_roles WHERE user_id = ? AND role_id = 4", [$userId]);
if ($existingRole) {
    echo "  - Student role already assigned\n";
} else {
    $db->query("INSERT INTO user_roles (user_id, role_id, assigned_at, assigned_by) VALUES (?, 4, NOW(), 1)", [$userId]);
    echo "  - Assigned student role\n";
}

// ============================================================
// STEP 4: Create user profile
// ============================================================
echo "\nStep 4: Creating user profile...\n";

$existingProfile = $db->fetchOne("SELECT id FROM user_profiles WHERE user_id = ?", [$userId]);
if ($existingProfile) {
    echo "  - User profile already exists\n";
} else {
    $db->query("INSERT INTO user_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())", [$userId]);
    echo "  - Created user profile\n";
}

// ============================================================
// STEP 5: Create enrollment
// ============================================================
echo "\nStep 5: Creating enrollment...\n";

$grades = [80, 85, 90, 75, 88];
$total = array_sum($grades);
$finalGrade = round($total / 500 * 100, 2);

$enrollment = $db->fetchOne("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
if ($enrollment) {
    $enrollmentId = $enrollment['id'];
    echo "  - Found existing enrollment: $enrollmentId\n";
} else {
    $db->query("INSERT INTO enrollments (user_id, student_id, course_id, enrolled_at, start_date, progress, final_grade, enrollment_status, payment_status, amount_paid, completion_date, certificate_issued, certificate_blocked, created_at, updated_at) VALUES (?, ?, ?, CURDATE(), CURDATE(), 100.00, ?, 'Completed', 'completed', 2500.00, CURDATE(), 1, 0, NOW(), NOW())", [
        $userId, $studentId, $courseId, $finalGrade
    ]);
    $enrollmentId = $db->lastInsertId();
    echo "  - Created enrollment: $enrollmentId\n";
}

// ============================================================
// STEP 6: Create payment plan
// ============================================================
echo "\nStep 6: Creating payment plan...\n";

$paymentPlan = $db->fetchOne("SELECT id FROM enrollment_payment_plans WHERE enrollment_id = ?", [$enrollmentId]);
if ($paymentPlan) {
    echo "  - Payment plan already exists\n";
} else {
    $db->query("INSERT INTO enrollment_payment_plans (enrollment_id, user_id, course_id, total_fee, total_paid, currency, payment_status, created_at, updated_at) VALUES (?, ?, ?, 2500.00, 2500.00, 'ZMW', 'completed', NOW(), NOW())", [
        $enrollmentId, $userId, $courseId
    ]);
    echo "  - Created payment plan\n";
}

// ============================================================
// STEP 7: Mark all lessons complete
// ============================================================
echo "\nStep 7: Marking lessons complete...\n";

$lessons = $db->fetchAll("
    SELECT l.id 
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    WHERE m.course_id = ?
    ORDER BY m.display_order, l.display_order
", [$courseId]);

$lessonCount = 0;
foreach ($lessons as $lesson) {
    $lessonId = $lesson['id'];
    $existing = $db->fetchOne("SELECT id FROM lesson_progress WHERE enrollment_id = ? AND lesson_id = ?", [$enrollmentId, $lessonId]);
    
    if (!$existing) {
        $db->query("INSERT INTO lesson_progress (enrollment_id, lesson_id, status, progress_percentage, time_spent_minutes, started_at, completed_at, last_accessed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW(), NOW(), NOW())", [
            $enrollmentId, $lessonId, 'Completed', 100.00, 15
        ]);
        $lessonCount++;
    }
}
echo "  - Marked $lessonCount lessons complete (" . count($lessons) . " total)\n";

// ============================================================
// STEP 8: Create assignment submissions
// ============================================================
echo "\nStep 8: Recording assignment grades...\n";

$assignmentTitles = ['Test 1', 'Microsoft Word', 'Microsoft Excel', 'Microsoft Publisher & PowerPoint', 'IT & Networks'];
$gradeCount = 0;

foreach ($assignmentTitles as $index => $title) {
    $assignment = $db->fetchOne("SELECT id FROM assignments WHERE course_id = ? AND title = ?", [$courseId, $title]);
    if ($assignment) {
        $assignmentId = $assignment['id'];
        $existing = $db->fetchOne("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?", [$assignmentId, $studentId]);
        if (!$existing) {
            $db->query("INSERT INTO assignment_submissions (assignment_id, student_id, submitted_at, status, points_earned, graded_by, graded_at, attempt_number, is_late) VALUES (?, ?, NOW(), 'Graded', ?, ?, NOW(), 1, 0)", [
                $assignmentId, $studentId, $grades[$index], $adminUserId
            ]);
            $gradeCount++;
        }
    }
}
echo "  - Recorded $gradeCount assignment grades\n";

// ============================================================
// STEP 9: Create certificate
// ============================================================
echo "\nStep 9: Creating certificate...\n";

$existingCert = $db->fetchOne("SELECT certificate_id FROM certificates WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
if ($existingCert) {
    echo "  - Certificate already exists\n";
} else {
    $certNumber = 'EDTRK-2026-' . str_pad($enrollmentId + 100000, 6, '0', STR_PAD_LEFT);
    $verifyCode = 'VRF-' . str_pad($enrollmentId + 100, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5($certNumber), 0, 8));
    
    $db->query("INSERT INTO certificates (user_id, course_id, enrollment_id, certificate_number, issued_date, verification_code, final_score, issued_at, is_verified, created_at) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, NOW(), 1, NOW())", [
        $userId, $courseId, $enrollmentId, $certNumber, $verifyCode, $finalGrade
    ]);
    echo "  - Created certificate: $certNumber\n";
}

// ============================================================
// STEP 10: Update enrollment to ensure complete
// ============================================================
echo "\nStep 10: Finalizing enrollment...\n";

$db->query("UPDATE enrollments SET progress = 100.00, final_grade = ?, enrollment_status = 'Completed', completion_date = CURDATE(), certificate_issued = 1, certificate_blocked = 0, updated_at = NOW() WHERE id = ?", [
    $finalGrade, $enrollmentId
]);
echo "  - Enrollment finalized\n";

// ============================================================
// SUMMARY
// ============================================================
echo "\n========================================\n";
echo "TEST USER CREATED SUCCESSFULLY\n";
echo "========================================\n";
echo "Username: testuser\n";
echo "Password: $testPassword\n";
echo "Email:    testuser@edutrack.edu\n";
echo "Name:     Test User\n";
echo "Course:   Microsoft Office Suite\n";
echo "Status:   Completed\n";
echo "Grade:    $finalGrade%\n";
echo "Total:    $total / 500\n";
echo "========================================\n";
