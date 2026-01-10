<?php
/**
 * Financials Handler - Processes form submissions before HTML output
 */

$action = $_POST['action'] ?? '';

// Add payment
if ($action === 'add') {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentType = $_POST['payment_type'] ?? 'tuition';
    $paymentMethodId = (int)($_POST['payment_method_id'] ?? 0);
    $paymentStatus = $_POST['payment_status'] ?? 'Pending';
    $transactionId = trim($_POST['transaction_id'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($studentId && $amount > 0) {
        $db->insert('payments', [
            'student_id' => $studentId,
            'course_id' => $courseId ?: null,
            'amount' => $amount,
            'currency' => 'ZMW',
            'payment_type' => $paymentType,
            'payment_method_id' => $paymentMethodId ?: null,
            'payment_status' => $paymentStatus,
            'transaction_id' => $transactionId ?: null,
            'notes' => $notes ?: null,
            'payment_date' => $paymentStatus === 'Completed' ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update enrollment payment if course selected
        if ($courseId && $paymentStatus === 'Completed') {
            $enrollment = $db->fetchOne("SELECT id, amount_paid FROM enrollments WHERE user_id = ? AND course_id = ?", [$studentId, $courseId]);
            if ($enrollment) {
                $db->update('enrollments', [
                    'amount_paid' => $enrollment['amount_paid'] + $amount,
                    'payment_status' => 'completed'
                ], 'id = ?', [$enrollment['id']]);
            }
        }

        header('Location: ?page=financials&msg=added');
        exit;
    }
}

// Edit payment
if ($action === 'edit' && isset($_POST['payment_id'])) {
    $paymentId = (int)$_POST['payment_id'];
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentType = $_POST['payment_type'] ?? 'tuition';
    $paymentMethodId = (int)($_POST['payment_method_id'] ?? 0);
    $transactionId = trim($_POST['transaction_id'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $db->update('payments', [
        'amount' => $amount,
        'payment_type' => $paymentType,
        'payment_method_id' => $paymentMethodId ?: null,
        'transaction_id' => $transactionId ?: null,
        'notes' => $notes ?: null
    ], 'payment_id = ?', [$paymentId]);

    header('Location: ?page=financials&msg=updated');
    exit;
}

// Verify payment
if ($action === 'verify' && isset($_POST['payment_id'])) {
    $paymentId = (int)$_POST['payment_id'];
    $db->update('payments', [
        'payment_status' => 'Completed',
        'payment_date' => date('Y-m-d H:i:s')
    ], 'payment_id = ?', [$paymentId]);

    // Update enrollment
    $payment = $db->fetchOne("SELECT student_id, course_id, amount FROM payments WHERE payment_id = ?", [$paymentId]);
    if ($payment && $payment['course_id']) {
        $enrollment = $db->fetchOne("SELECT id, amount_paid FROM enrollments WHERE user_id = ? AND course_id = ?", [$payment['student_id'], $payment['course_id']]);
        if ($enrollment) {
            $db->update('enrollments', [
                'amount_paid' => $enrollment['amount_paid'] + $payment['amount'],
                'payment_status' => 'completed'
            ], 'id = ?', [$enrollment['id']]);
        }
    }

    header('Location: ?page=financials&msg=verified');
    exit;
}

// Reject payment
if ($action === 'reject' && isset($_POST['payment_id'])) {
    $paymentId = (int)$_POST['payment_id'];
    $db->update('payments', ['payment_status' => 'Failed'], 'payment_id = ?', [$paymentId]);
    header('Location: ?page=financials&msg=rejected');
    exit;
}

// Refund payment
if ($action === 'refund' && isset($_POST['payment_id'])) {
    $paymentId = (int)$_POST['payment_id'];
    $refundReason = trim($_POST['refund_reason'] ?? '');

    $payment = $db->fetchOne("SELECT * FROM payments WHERE payment_id = ?", [$paymentId]);
    if ($payment && $payment['payment_status'] === 'Completed') {
        $db->update('payments', [
            'payment_status' => 'Refunded',
            'notes' => ($payment['notes'] ? $payment['notes'] . "\n" : '') . "Refund reason: " . $refundReason
        ], 'payment_id = ?', [$paymentId]);

        // Update enrollment if applicable
        if ($payment['course_id']) {
            $enrollment = $db->fetchOne("SELECT id, amount_paid FROM enrollments WHERE user_id = ? AND course_id = ?", [$payment['student_id'], $payment['course_id']]);
            if ($enrollment) {
                $newAmount = max(0, $enrollment['amount_paid'] - $payment['amount']);
                $db->update('enrollments', [
                    'amount_paid' => $newAmount,
                    'payment_status' => $newAmount > 0 ? 'completed' : 'pending'
                ], 'id = ?', [$enrollment['id']]);
            }
        }
    }

    header('Location: ?page=financials&msg=refunded');
    exit;
}

// Delete payment
if ($action === 'delete' && isset($_POST['payment_id'])) {
    $paymentId = (int)$_POST['payment_id'];
    $db->delete('payments', 'payment_id = ?', [$paymentId]);
    header('Location: ?page=financials&msg=deleted');
    exit;
}
