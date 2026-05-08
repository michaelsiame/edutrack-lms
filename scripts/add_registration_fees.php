<?php
/**
 * Add Registration Fees for Office Students
 * Creates completed registration fee records for all imported students
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/../src/bootstrap.php';

$db = Database::getInstance();

echo "=== Adding Registration Fees ===\n\n";

// All user IDs for Microsoft Office students + test user
$userIds = [78, 83, 77, 79, 85, 86, 84, 87, 80, 81, 82];

// Also check for testuser
$testUser = $db->fetchOne("SELECT id FROM users WHERE username = ?", ['testuser']);
if ($testUser) {
    $userIds[] = $testUser['id'];
}

$created = 0;
$skipped = 0;

foreach ($userIds as $userId) {
    $user = $db->fetchOne("SELECT id, first_name, last_name FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        echo "  - User $userId not found, skipping\n";
        continue;
    }
    
    $name = $user['first_name'] . ' ' . $user['last_name'];
    
    // Check if registration fee already exists
    $existing = $db->fetchOne("SELECT id, payment_status FROM registration_fees WHERE user_id = ?", [$userId]);
    
    if ($existing) {
        if ($existing['payment_status'] === 'completed') {
            echo "  - $name (ID: $userId): Already paid\n";
            $skipped++;
        } else {
            // Update to completed
            $db->query("UPDATE registration_fees SET payment_status = 'completed', verified_by = 1, verified_at = NOW(), updated_at = NOW() WHERE id = ?", [$existing['id']]);
            echo "  - $name (ID: $userId): Updated to completed\n";
            $created++;
        }
    } else {
        // Get student_id if exists
        $student = $db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
        $studentId = $student ? $student['id'] : null;
        
        // Create completed registration fee
        $db->query("INSERT INTO registration_fees (user_id, student_id, amount, currency, payment_status, payment_method, bank_reference, bank_name, verified_by, verified_at, notes, created_at, updated_at) VALUES (?, ?, 150.00, 'ZMW', 'completed', 'bank_deposit', 'IMPORTED', 'Admin Import', 1, NOW(), 'Auto-imported for Microsoft Office graduates', NOW(), NOW())", [
            $userId, $studentId
        ]);
        echo "  - $name (ID: $userId): Created registration fee K150\n";
        $created++;
    }
}

echo "\n=== Done ===\n";
echo "Created/Updated: $created\n";
echo "Already paid: $skipped\n";
