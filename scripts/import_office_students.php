<?php
/**
 * Import Office Students Script
 * Enrolls 11 students into Microsoft Office Suite (course_id=1) with grades and certificates
 * 
 * Usage: php scripts/import_office_students.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/../src/bootstrap.php';

$db = Database::getInstance();

echo "=== Edutrack LMS - Import Microsoft Office Students ===\n\n";
ob_implicit_flush(true);
ob_end_flush();

// ============================================================
// CONFIGURATION
// ============================================================
$courseId = 1;
$adminUserId = 1; // Graded by admin

// 11 students with their grades
$students = [
    ['name' => 'Luyando Muchimba',      'user_id' => 78, 'student_id' => 72, 'grades' => [100, 100, 98, 100, 89], 'total' => 487],
    ['name' => 'Wane Mary Ng\'ambi',     'user_id' => 83, 'student_id' => 77, 'grades' => [96, 94, 98, 84, 88], 'total' => 460],
    ['name' => 'Taonga Tembo',           'user_id' => 77, 'student_id' => 71, 'grades' => [93, 96, 98, 74, 83], 'total' => 444],
    ['name' => 'Chintu Chiinda',         'user_id' => 79, 'student_id' => 73, 'grades' => [83, 90, 98, 72, 77], 'total' => 420],
    ['name' => 'Fragester Mudenda',      'user_id' => 85, 'student_id' => 79, 'grades' => [82, 96, 94, 74, 72], 'total' => 418],
    ['name' => 'Joyce Lishebela',        'user_id' => 86, 'student_id' => 80, 'grades' => [87, 94, 86, 70, 80], 'total' => 417],
    ['name' => 'Cathrine Namakanda',     'user_id' => 84, 'student_id' => 78, 'grades' => [76, 90, 96, 76, 71], 'total' => 409],
    ['name' => 'Patricia Siamukopa',     'user_id' => null, 'student_id' => null, 'grades' => [69, 92, 90, 80, 68], 'total' => 399],
    ['name' => 'Wankie Trust',           'user_id' => 80, 'student_id' => 74, 'grades' => [89, 94, 88, 58, 68], 'total' => 397],
    ['name' => 'Dabali Luyando',         'user_id' => 81, 'student_id' => 75, 'grades' => [73, 92, 92, 76, 57], 'total' => 390],
    ['name' => 'Abson Simukabe',         'user_id' => 82, 'student_id' => 76, 'grades' => [50, 62, 68, 68, 73], 'total' => 321],
];

$assignmentTitles = ['Test 1', 'Microsoft Word', 'Microsoft Excel', 'Microsoft Publisher & PowerPoint', 'IT & Networks'];

// ============================================================
// HELPER: Check if column exists
// ============================================================
function columnExists($db, $table, $column) {
    try {
        $result = $db->fetchOne("SHOW COLUMNS FROM `$table` LIKE ?", [$column]);
        return !empty($result);
    } catch (Exception $e) {
        return false;
    }
}

// ============================================================
// STEP 1: Create Patricia Siamukopa user if needed
// ============================================================
echo "Step 1: Checking users...\n";

foreach ($students as &$student) {
    if ($student['user_id'] === null) {
        // Create user for Patricia
        $existing = $db->fetchOne("SELECT id FROM users WHERE username = ?", ['patricia.siamukopa']);
        if ($existing) {
            $student['user_id'] = $existing['id'];
            echo "  - Found existing user for {$student['name']}: {$existing['id']}\n";
        } else {
            $db->query("INSERT INTO users (username, email, password_hash, first_name, last_name, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())", [
                'patricia.siamukopa',
                'patricia.siamukopa@student.edutrack.edu',
                '$2y$10$dxWyurt7ibrP4JzRuvqFjOnaNiF/XGmKtkOP5OEf8.fXJWke3bWxW',
                'Patricia',
                'Siamukopa'
            ]);
            $student['user_id'] = $db->lastInsertId();
            echo "  - Created user for {$student['name']}: {$student['user_id']}\n";
        }
        
        // Create student record
        $existingStudent = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$student['user_id']]);
        if ($existingStudent) {
            $student['student_id'] = $existingStudent['id'];
        } else {
            $db->query("INSERT INTO students (user_id, enrollment_date, total_courses_enrolled, total_courses_completed, total_certificates, created_at, updated_at) VALUES (?, CURDATE(), 0, 0, 0, NOW(), NOW())", [$student['user_id']]);
            $student['student_id'] = $db->lastInsertId();
            echo "  - Created student record: {$student['student_id']}\n";
        }
        
        // Assign student role
        $existingRole = $db->fetchOne("SELECT id FROM user_roles WHERE user_id = ? AND role_id = 4", [$student['user_id']]);
        if (!$existingRole) {
            $db->query("INSERT INTO user_roles (user_id, role_id, assigned_at, assigned_by) VALUES (?, 4, NOW(), 1)", [$student['user_id']]);
            echo "  - Assigned student role\n";
        }
        
        // Create user profile if needed
        $existingProfile = $db->fetchOne("SELECT id FROM user_profiles WHERE user_id = ?", [$student['user_id']]);
        if (!$existingProfile) {
            $db->query("INSERT INTO user_profiles (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())", [$student['user_id']]);
            echo "  - Created user profile\n";
        }
    } else {
        echo "  - User exists for {$student['name']}: {$student['user_id']}\n";
    }
}
unset($student);

// ============================================================
// STEP 2: Create assignments for course
// ============================================================
echo "\nStep 2: Creating assignments...\n";
$assignmentIds = [];
foreach ($assignmentTitles as $index => $title) {
    $existing = $db->fetchOne("SELECT id FROM assignments WHERE course_id = ? AND title = ?", [$courseId, $title]);
    if ($existing) {
        $assignmentIds[$index] = $existing['id'];
        echo "  - Found assignment '$title': {$existing['id']}\n";
    } else {
        $db->query("INSERT INTO assignments (course_id, title, description, max_points, passing_points, due_date, allow_late_submission, late_penalty_percent, created_at, updated_at) VALUES (?, ?, ?, 100, 60, NOW(), 0, 0.00, NOW(), NOW())", [
            $courseId, $title, "$title assessment for Microsoft Office Suite"
        ]);
        $assignmentIds[$index] = $db->lastInsertId();
        echo "  - Created assignment '$title': {$assignmentIds[$index]}\n";
    }
}

// ============================================================
// STEP 3: Get lessons for course
// ============================================================
echo "\nStep 3: Getting course lessons...\n";
$lessons = $db->fetchAll("
    SELECT l.id 
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    WHERE m.course_id = ?
    ORDER BY m.display_order, l.display_order
", [$courseId]);

if (empty($lessons)) {
    echo "  WARNING: No lessons found for course $courseId\n";
} else {
    echo "  - Found " . count($lessons) . " lessons\n";
}

// ============================================================
// STEP 4: Process each student
// ============================================================
echo "\nStep 4: Enrolling students and recording grades...\n";

foreach ($students as $student) {
    $userId = $student['user_id'];
    $studentId = $student['student_id'];
    $name = $student['name'];
    
    echo "\n  Processing: $name (User: $userId, Student: $studentId)\n";
    
    try {
        // Check/create enrollment
        $enrollment = $db->fetchOne("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
        if ($enrollment) {
            $enrollmentId = $enrollment['id'];
            echo "    - Found enrollment: $enrollmentId\n";
        } else {
            $db->query("INSERT INTO enrollments (user_id, student_id, course_id, enrolled_at, start_date, progress, final_grade, enrollment_status, payment_status, amount_paid, completion_date, certificate_issued, certificate_blocked, created_at, updated_at) VALUES (?, ?, ?, CURDATE(), CURDATE(), 100.00, ?, 'Completed', 'completed', 2500.00, CURDATE(), 1, 0, NOW(), NOW())", [
                $userId, $studentId, $courseId, round($student['total'] / 500 * 100, 2)
            ]);
            $enrollmentId = $db->lastInsertId();
            echo "    - Created enrollment: $enrollmentId\n";
        }
        
        // Create/update payment plan
        $paymentPlan = $db->fetchOne("SELECT id FROM enrollment_payment_plans WHERE enrollment_id = ?", [$enrollmentId]);
        if (!$paymentPlan) {
            $db->query("INSERT INTO enrollment_payment_plans (enrollment_id, user_id, course_id, total_fee, total_paid, currency, payment_status, created_at, updated_at) VALUES (?, ?, ?, 2500.00, 2500.00, 'ZMW', 'completed', NOW(), NOW())", [
                $enrollmentId, $userId, $courseId
            ]);
            echo "    - Created payment plan\n";
        }
        
        // Mark all lessons complete
        if (!empty($lessons)) {
            $hasUserId = columnExists($db, 'lesson_progress', 'user_id');
            $hasCompleted = columnExists($db, 'lesson_progress', 'completed');
            $hasEnrollmentId = columnExists($db, 'lesson_progress', 'enrollment_id');
            
            echo "    - lesson_progress columns: enrollment_id=" . ($hasEnrollmentId ? 'yes' : 'no') . ", user_id=" . ($hasUserId ? 'yes' : 'no') . ", completed=" . ($hasCompleted ? 'yes' : 'no') . "\n";
            
            $lessonCount = 0;
            foreach ($lessons as $lesson) {
                $lessonId = $lesson['id'];
                
                // Check if progress exists
                $whereClause = $hasUserId ? "user_id = ? AND lesson_id = ?" : "enrollment_id = ? AND lesson_id = ?";
                $whereParams = $hasUserId ? [$userId, $lessonId] : [$enrollmentId, $lessonId];
                
                $existing = $db->fetchOne("SELECT id FROM lesson_progress WHERE $whereClause", $whereParams);
                
                if (!$existing) {
                    $columns = [];
                    $values = [];
                    $params = [];
                    
                    if ($hasEnrollmentId) {
                        $columns[] = 'enrollment_id';
                        $values[] = '?';
                        $params[] = $enrollmentId;
                    }
                    if ($hasUserId) {
                        $columns[] = 'user_id';
                        $values[] = '?';
                        $params[] = $userId;
                    }
                    $columns[] = 'lesson_id';
                    $values[] = '?';
                    $params[] = $lessonId;
                    $columns[] = 'status';
                    $values[] = '?';
                    $params[] = 'Completed';
                    $columns[] = 'progress_percentage';
                    $values[] = '?';
                    $params[] = 100.00;
                    $columns[] = 'time_spent_minutes';
                    $values[] = '?';
                    $params[] = 15;
                    if ($hasCompleted) {
                        $columns[] = 'completed';
                        $values[] = '?';
                        $params[] = 1;
                    }
                    $columns[] = 'started_at';
                    $values[] = 'NOW()';
                    $columns[] = 'completed_at';
                    $values[] = 'NOW()';
                    $columns[] = 'last_accessed';
                    $values[] = 'NOW()';
                    $columns[] = 'created_at';
                    $values[] = 'NOW()';
                    $columns[] = 'updated_at';
                    $values[] = 'NOW()';
                    
                    $colStr = implode(', ', $columns);
                    $valStr = implode(', ', $values);
                    $sql = "INSERT INTO lesson_progress ($colStr) VALUES ($valStr)";
                    $db->query($sql, $params);
                    $lessonCount++;
                }
                
                // Small delay to reduce server load
                usleep(10000); // 10ms
            }
            echo "    - Marked $lessonCount lessons complete\n";
        }
        
        // Create assignment submissions
        $gradeCount = 0;
        foreach ($student['grades'] as $idx => $grade) {
            $assignmentId = $assignmentIds[$idx];
            $existing = $db->fetchOne("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?", [$assignmentId, $studentId]);
            if (!$existing) {
                $db->query("INSERT INTO assignment_submissions (assignment_id, student_id, submitted_at, status, points_earned, graded_by, graded_at, attempt_number, is_late) VALUES (?, ?, NOW(), 'Graded', ?, ?, NOW(), 1, 0)", [
                    $assignmentId, $studentId, $grade, $adminUserId
                ]);
                $gradeCount++;
            }
        }
        echo "    - Recorded $gradeCount assignment grades\n";
        
        // Update enrollment to ensure it's marked complete
        $db->query("UPDATE enrollments SET progress = 100.00, final_grade = ?, enrollment_status = 'Completed', completion_date = CURDATE(), certificate_issued = 1, certificate_blocked = 0, updated_at = NOW() WHERE id = ?", [
            round($student['total'] / 500 * 100, 2), $enrollmentId
        ]);
        
        // Create certificate
        $existingCert = $db->fetchOne("SELECT id FROM certificates WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
        if (!$existingCert) {
            $finalScore = round($student['total'] / 500 * 100, 2);
            $certNumber = 'EDTRK-2026-' . str_pad($enrollmentId + 100000, 6, '0', STR_PAD_LEFT);
            $verifyCode = 'VRF-' . str_pad($enrollmentId + 100, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(md5($certNumber), 0, 8));
            
            $db->query("INSERT INTO certificates (user_id, course_id, enrollment_id, certificate_number, issued_date, verification_code, final_score, issued_at, is_verified, created_at) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, NOW(), 1, NOW())", [
                $userId, $courseId, $enrollmentId, $certNumber, $verifyCode, $finalScore
            ]);
            echo "    - Created certificate: $certNumber\n";
        } else {
            echo "    - Certificate already exists\n";
        }
        
    } catch (Exception $e) {
        echo "    *** ERROR for $name: " . $e->getMessage() . "\n";
        echo "    *** Stack trace: " . $e->getTraceAsString() . "\n";
    }
}

// ============================================================
// STEP 5: Update course enrollment count
// ============================================================
echo "\nStep 5: Updating course stats...\n";
$enrollmentCount = $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE course_id = ?", [$courseId]);
$db->query("UPDATE courses SET enrollment_count = ? WHERE id = ?", [$enrollmentCount, $courseId]);
echo "  - Updated enrollment count to $enrollmentCount\n";

echo "\n=== Import Complete ===\n";
echo "Enrolled " . count($students) . " students into Microsoft Office Suite.\n";
