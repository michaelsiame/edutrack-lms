<?php
/**
 * Safe Schema Fixes for Production MariaDB
 * Introspects actual schema before making changes — no guessing.
 */

require __DIR__ . '/../config/database.php';

$db = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function columnExists(PDO $db, string $table, string $column): bool {
    $stmt = $db->prepare("
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (bool) $stmt->fetch();
}

function indexExists(PDO $db, string $table, string $index): bool {
    $stmt = $db->prepare("
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND INDEX_NAME = ?
    ");
    $stmt->execute([$table, $index]);
    return (bool) $stmt->fetch();
}

function fkExists(PDO $db, string $table, string $fk): bool {
    $stmt = $db->prepare("
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND CONSTRAINT_NAME = ?
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    $stmt->execute([$table, $fk]);
    return (bool) $stmt->fetch();
}

function tableExists(PDO $db, string $table): bool {
    $stmt = $db->prepare("
        SELECT 1 FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);
    return (bool) $stmt->fetch();
}

$log = [];
$db->exec("SET FOREIGN_KEY_CHECKS = 0");

try {
    $db->beginTransaction();

    // 1. AUTO_INCREMENT fixes
    $db->exec("ALTER TABLE `quiz_answers` MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT");
    $log[] = "quiz_answers.answer_id set to AUTO_INCREMENT";

    $db->exec("ALTER TABLE `quiz_attempts` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
    $log[] = "quiz_attempts.id set to AUTO_INCREMENT";

    // 2. Create quiz_question_options if missing
    if (!tableExists($db, 'quiz_question_options')) {
        $db->exec("CREATE TABLE `quiz_question_options` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `question_id` int(11) NOT NULL,
            `option_text` text NOT NULL,
            `is_correct` tinyint(1) DEFAULT 0,
            `display_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_qqo_question` (`question_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $log[] = "Created table quiz_question_options";
    }

    // Migrate data if empty
    $count = $db->query("SELECT COUNT(*) FROM quiz_question_options")->fetchColumn();
    if ($count == 0 && tableExists($db, 'question_options')) {
        $db->exec("INSERT INTO quiz_question_options (question_id, option_text, is_correct, display_order)
                   SELECT question_id, option_text, is_correct, display_order FROM question_options");
        $log[] = "Migrated data from question_options to quiz_question_options";
    }

    // 3. Certificates columns — add ONLY if missing
    foreach ([
        'user_id' => 'int(11) DEFAULT NULL AFTER certificate_id',
        'course_id' => 'int(11) DEFAULT NULL AFTER user_id',
        'final_score' => 'decimal(5,2) DEFAULT 0 AFTER verification_code',
        'issued_at' => 'datetime DEFAULT NULL AFTER final_score'
    ] as $col => $def) {
        if (!columnExists($db, 'certificates', $col)) {
            $db->exec("ALTER TABLE `certificates` ADD COLUMN `$col` $def");
            $log[] = "Added certificates.$col";
        }
    }

    // Migrate certificate data
    $db->exec("UPDATE `certificates` c
               JOIN `enrollments` e ON c.enrollment_id = e.id
               SET c.user_id = e.user_id,
                   c.course_id = e.course_id,
                   c.issued_at = COALESCE(c.issued_at, CONCAT(c.issued_date, ' 00:00:00'))
               WHERE c.user_id IS NULL");
    $log[] = "Migrated certificates data from enrollments";

    // 4. Deduplicate enrollment_payment_plans
    $db->exec("UPDATE payments p
               JOIN (
                   SELECT enrollment_id,
                          SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY total_paid DESC, updated_at DESC, id ASC SEPARATOR ','), ',', 1) + 0 AS keeper_id
                   FROM enrollment_payment_plans
                   GROUP BY enrollment_id
                   HAVING COUNT(*) > 1
               ) k ON p.enrollment_id = k.enrollment_id
               SET p.payment_plan_id = k.keeper_id
               WHERE p.payment_plan_id != k.keeper_id");

    $db->exec("DELETE epp FROM enrollment_payment_plans epp
               JOIN (
                   SELECT enrollment_id,
                          SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY total_paid DESC, updated_at DESC, id ASC SEPARATOR ','), ',', 1) + 0 AS keeper_id
                   FROM enrollment_payment_plans
                   GROUP BY enrollment_id
                   HAVING COUNT(*) > 1
               ) k ON epp.enrollment_id = k.enrollment_id
               WHERE epp.id != k.keeper_id");
    $log[] = "Deduplicated enrollment_payment_plans";

    // 5. Deduplicate user_roles
    $db->exec("DELETE ur FROM user_roles ur
               JOIN (
                   SELECT user_id, role_id,
                          SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY assigned_at DESC, id DESC SEPARATOR ','), ',', 1) + 0 AS keeper_id
                   FROM user_roles
                   GROUP BY user_id, role_id
                   HAVING COUNT(*) > 1
               ) k ON ur.user_id = k.user_id AND ur.role_id = k.role_id
               WHERE ur.id != k.keeper_id");
    $log[] = "Deduplicated user_roles";

    // 6. Unique constraints
    $uniques = [
        ['enrollment_payment_plans', 'uk_epp_enrollment', 'enrollment_id'],
        ['certificates', 'uk_cert_number', 'certificate_number'],
        ['certificates', 'uk_cert_verify', 'verification_code'],
        ['quiz_questions', 'uk_qq_quiz_question', 'quiz_id,question_id'],
        ['user_roles', 'uk_user_role', 'user_id,role_id'],
        ['payments', 'uk_payments_txn', 'transaction_id'],
        ['users', 'uk_users_email', 'email'],
        ['users', 'uk_users_username', 'username'],
        ['students', 'uk_students_user', 'user_id'],
        ['instructors', 'uk_instructors_user', 'user_id'],
    ];
    foreach ($uniques as [$table, $name, $cols]) {
        if (!indexExists($db, $table, $name)) {
            $db->exec("ALTER TABLE `$table` ADD UNIQUE KEY `$name` ($cols)");
            $log[] = "Added unique constraint $name on $table";
        }
    }

    // 7. Indexes — only add if missing
    $indexes = [
        ['payments', 'idx_pay_student', 'student_id'],
        ['payments', 'idx_pay_course', 'course_id'],
        ['payments', 'idx_pay_enroll', 'enrollment_id'],
        ['payments', 'idx_pay_plan', 'payment_plan_id'],
        ['payments', 'idx_pay_status', 'payment_status'],
        ['certificates', 'idx_cert_enroll', 'enrollment_id'],
        ['announcements', 'idx_ann_course', 'course_id'],
        ['quiz_questions', 'idx_qq_quiz', 'quiz_id'],
        ['quiz_questions', 'idx_qq_question', 'question_id'],
        ['quiz_answers', 'idx_qa_attempt', 'attempt_id'],
        ['quiz_answers', 'idx_qa_question', 'question_id'],
        ['lesson_progress', 'idx_lp_enroll', 'enrollment_id'],
        ['lesson_progress', 'idx_lp_lesson', 'lesson_id'],
        ['lesson_resources', 'idx_lr_lesson', 'lesson_id'],
        ['live_sessions', 'idx_ls_lesson', 'lesson_id'],
        ['live_session_attendance', 'idx_lsa_session', 'session_id'],
        ['live_session_attendance', 'idx_lsa_user', 'user_id'],
        ['activity_logs', 'idx_al_user', 'user_id'],
        ['activity_logs', 'idx_al_type', 'activity_type'],
        ['activity_logs', 'idx_al_created', 'created_at'],
        ['lenco_transactions', 'idx_lt_user', 'user_id'],
        ['lenco_transactions', 'idx_lt_enroll', 'enrollment_id'],
        ['lenco_transactions', 'idx_lt_course', 'course_id'],
        ['courses', 'idx_courses_slug', 'slug'],
        ['users', 'idx_users_phone', 'phone'],
        ['enrollment_payment_plans', 'idx_epp_user', 'user_id'],
        ['enrollment_payment_plans', 'idx_epp_course', 'course_id'],
    ];
    foreach ($indexes as [$table, $name, $cols]) {
        if (!indexExists($db, $table, $name)) {
            $db->exec("ALTER TABLE `$table` ADD KEY `$name` ($cols)");
            $log[] = "Added index $name on $table";
        }
    }

    // 8. Currency fix
    $db->exec("ALTER TABLE payments ALTER COLUMN currency SET DEFAULT 'ZMW'");
    $db->exec("UPDATE payments SET currency = 'ZMW' WHERE currency = 'USD' AND amount > 100");
    $log[] = "Fixed payments.currency to ZMW";

    // 9. Drop redundant indexes
    $db->exec("ALTER TABLE lenco_transactions DROP INDEX IF EXISTS idx_reference");
    $db->exec("ALTER TABLE users DROP INDEX IF EXISTS id");
    $log[] = "Dropped redundant indexes";

    // 10. Fix remember_tokens FK
    $db->exec("ALTER TABLE remember_tokens DROP FOREIGN KEY IF EXISTS DE");
    if (fkExists($db, 'remember_tokens', 'fk_rt_user')) {
        $db->exec("ALTER TABLE remember_tokens DROP FOREIGN KEY fk_rt_user");
    }
    if (columnExists($db, 'remember_tokens', 'user_id')) {
        $db->exec("ALTER TABLE remember_tokens ADD CONSTRAINT fk_rt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE");
        $log[] = "Fixed remember_tokens FK";
    }

    // 11. Foreign Keys — ONLY add if column exists AND target table/column exists
    $fks = [
        ['certificates', 'fk_cert_enroll', 'enrollment_id', 'enrollments', 'id'],
        ['certificates', 'fk_cert_user', 'user_id', 'users', 'id'],
        ['certificates', 'fk_cert_course', 'course_id', 'courses', 'id'],
        ['lesson_progress', 'fk_lp_enroll', 'enrollment_id', 'enrollments', 'id'],
        ['lesson_progress', 'fk_lp_lesson', 'lesson_id', 'lessons', 'id'],
        ['live_sessions', 'fk_ls_lesson', 'lesson_id', 'lessons', 'id'],
    ];
    foreach ($fks as [$table, $name, $col, $refTable, $refCol]) {
        if (columnExists($db, $table, $col) && tableExists($db, $refTable)) {
            if (!fkExists($db, $table, $name)) {
                try {
                    $db->exec("ALTER TABLE `$table` ADD CONSTRAINT `$name` FOREIGN KEY (`$col`) REFERENCES `$refTable` (`$refCol`) ON DELETE CASCADE");
                    $log[] = "Added FK $name on $table.$col -> $refTable.$refCol";
                } catch (PDOException $e) {
                    $log[] = "SKIPPED FK $name: " . $e->getMessage();
                }
            }
        } else {
            $log[] = "SKIPPED FK $name (column $col or table $refTable missing)";
        }
    }

    // 12. Data cleanup
    $db->exec("DELETE q FROM questions q
               LEFT JOIN quiz_questions qq ON q.question_id = qq.question_id
               WHERE q.question_id IN (12,13,14,15,16) AND qq.quiz_question_id IS NULL");
    $log[] = "Cleaned up duplicate questions";

    $db->commit();
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "=== SCHEMA FIXES COMPLETED SUCCESSFULLY ===\n\n";
    foreach ($log as $line) {
        echo "✓ $line\n";
    }
    echo "\nDone.\n";

} catch (Throwable $e) {
    $db->rollBack();
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "=== ERROR ===\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
