<?php
/**
 * Badge & Achievement System
 * Manages badges and student achievements
 */

class Badge {
    private $db;
    private $data = [];

    // Badge type constants
    const TYPE_COURSE_COMPLETION = 'course_completion';
    const TYPE_QUIZ_PERFECT = 'quiz_perfect';
    const TYPE_EARLY_SUBMISSION = 'early_submission';
    const TYPE_FIRST_ENROLLMENT = 'first_enrollment';
    const TYPE_STREAK = 'streak';

    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id !== null) {
            $this->load($id);
        }
    }

    private function load($id) {
        $result = $this->db->fetchOne("SELECT * FROM badges WHERE badge_id = ?", [$id]);
        $this->data = $result ?: [];
    }

    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM badges ORDER BY badge_id ASC");
    }

    public static function awardToStudent($studentId, $badgeId, $courseId = null, $description = '') {
        $db = Database::getInstance();

        // Check if already earned
        $existing = $db->fetchOne(
            "SELECT achievement_id FROM student_achievements WHERE student_id = ? AND badge_id = ? AND (course_id = ? OR (course_id IS NULL AND ? IS NULL))",
            [$studentId, $badgeId, $courseId, $courseId]
        );

        if ($existing) {
            return false; // Already earned
        }

        $db->query(
            "INSERT INTO student_achievements (student_id, badge_id, course_id, earned_date, description) VALUES (?, ?, ?, CURDATE(), ?)",
            [$studentId, $badgeId, $courseId, $description]
        );

        return $db->getConnection()->lastInsertId();
    }

    public static function getStudentAchievements($studentId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT sa.*, b.badge_name, b.badge_description, b.badge_icon, b.badge_color,
                    c.title as course_title
             FROM student_achievements sa
             JOIN badges b ON sa.badge_id = b.badge_id
             LEFT JOIN courses c ON sa.course_id = c.id
             WHERE sa.student_id = ?
             ORDER BY sa.earned_date DESC",
            [$studentId]
        );
    }

    public static function getStudentCount($studentId) {
        $db = Database::getInstance();
        return (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM student_achievements WHERE student_id = ?",
            [$studentId]
        );
    }

    /**
     * Check and award badges based on various triggers
     */
    public static function checkAndAward($studentId, $trigger, $context = []) {
        $db = Database::getInstance();
        $awarded = [];

        switch ($trigger) {
            case 'course_completed':
                // Award course completion badge
                $badge = $db->fetchOne("SELECT badge_id FROM badges WHERE badge_name LIKE '%Completion%' OR badge_name LIKE '%completion%' LIMIT 1");
                if ($badge) {
                    $courseTitle = $context['course_title'] ?? 'course';
                    $result = self::awardToStudent($studentId, $badge['badge_id'], $context['course_id'] ?? null, "Completed {$courseTitle}");
                    if ($result) $awarded[] = $badge['badge_id'];
                }
                break;

            case 'quiz_perfect_score':
                // Award perfect score badge
                $badge = $db->fetchOne("SELECT badge_id FROM badges WHERE badge_name LIKE '%Perfect%' OR badge_name LIKE '%perfect%' LIMIT 1");
                if ($badge) {
                    $result = self::awardToStudent($studentId, $badge['badge_id'], $context['course_id'] ?? null, "Perfect score on quiz");
                    if ($result) $awarded[] = $badge['badge_id'];
                }
                break;

            case 'early_submission':
                // Award early bird badge
                $badge = $db->fetchOne("SELECT badge_id FROM badges WHERE badge_name LIKE '%Early%' OR badge_name LIKE '%early%' LIMIT 1");
                if ($badge) {
                    $result = self::awardToStudent($studentId, $badge['badge_id'], $context['course_id'] ?? null, "Submitted assignment early");
                    if ($result) $awarded[] = $badge['badge_id'];
                }
                break;
        }

        return $awarded;
    }

    // Getters
    public function getId() { return $this->data['badge_id'] ?? null; }
    public function getName() { return $this->data['badge_name'] ?? ''; }
    public function getDescription() { return $this->data['badge_description'] ?? ''; }
    public function getIcon() { return $this->data['badge_icon'] ?? null; }
    public function getColor() { return $this->data['badge_color'] ?? '#333333'; }
    public function getData() { return $this->data; }
}
