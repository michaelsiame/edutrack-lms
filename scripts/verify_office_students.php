<?php
/**
 * Verify Office Students Import
 * Quick check that all 11 students have complete data
 */

require_once __DIR__ . '/../src/bootstrap.php';

$db = Database::getInstance();
$courseId = 1;

echo "=== Verification Report ===\n\n";

$students = [
    ['name' => 'Luyando Muchimba', 'user_id' => 78],
    ['name' => 'Wane Mary Ng\'ambi', 'user_id' => 83],
    ['name' => 'Taonga Tembo', 'user_id' => 77],
    ['name' => 'Chintu Chiinda', 'user_id' => 79],
    ['name' => 'Fragester Mudenda', 'user_id' => 85],
    ['name' => 'Joyce Lishebela', 'user_id' => 86],
    ['name' => 'Cathrine Namakanda', 'user_id' => 84],
    ['name' => 'Patricia Siamukopa', 'user_id' => 87],
    ['name' => 'Wankie Trust', 'user_id' => 80],
    ['name' => 'Dabali Luyando', 'user_id' => 81],
    ['name' => 'Abson Simukabe', 'user_id' => 82],
];

$totalLessons = $db->fetchColumn("
    SELECT COUNT(l.id) FROM lessons l
    JOIN modules m ON l.module_id = m.id
    WHERE m.course_id = ?
", [$courseId]);

$totalAssignments = $db->fetchColumn("SELECT COUNT(*) FROM assignments WHERE course_id = ?", [$courseId]);

foreach ($students as $student) {
    $userId = $student['user_id'];
    $name = $student['name'];
    
    $enrollment = $db->fetchOne("SELECT id, enrollment_status, final_grade FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
    $lessonCount = $db->fetchColumn("SELECT COUNT(*) FROM lesson_progress WHERE enrollment_id = ?", [$enrollment['id']]);
    $gradeCount = $db->fetchColumn("SELECT COUNT(*) FROM assignment_submissions s JOIN students st ON s.student_id = st.id WHERE st.user_id = ? AND s.assignment_id IN (SELECT id FROM assignments WHERE course_id = ?)", [$userId, $courseId]);
    $cert = $db->fetchOne("SELECT certificate_number, final_score FROM certificates WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
    $payment = $db->fetchOne("SELECT total_paid FROM enrollment_payment_plans WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
    
    $status = $enrollment['enrollment_status'] ?? 'MISSING';
    $grade = $enrollment['final_grade'] ?? 0;
    $certNum = $cert['certificate_number'] ?? 'MISSING';
    $certScore = $cert['final_score'] ?? 0;
    $paid = $payment['total_paid'] ?? 0;
    
    $lessonsOk = ($lessonCount == $totalLessons) ? 'OK' : "$lessonCount/$totalLessons";
    $gradesOk = ($gradeCount == $totalAssignments) ? 'OK' : "$gradeCount/$totalAssignments";
    
    echo sprintf("%-22s | Status: %-9s | Grade: %5.1f%% | Lessons: %s | Grades: %s | Cert: %s | Paid: %s\n",
        $name, $status, $grade, $lessonsOk, $gradesOk, $certNum, $paid ? 'K' . number_format($paid) : 'MISSING');
}

echo "\n=== Summary ===\n";
$totalEnrolled = $db->fetchColumn("SELECT COUNT(*) FROM enrollments WHERE course_id = ?", [$courseId]);
$totalCerts = $db->fetchColumn("SELECT COUNT(*) FROM certificates WHERE course_id = ?", [$courseId]);
echo "Total enrollments: $totalEnrolled\n";
echo "Total certificates: $totalCerts\n";
echo "Lessons per student: $totalLessons\n";
echo "Assignments per student: $totalAssignments\n";
