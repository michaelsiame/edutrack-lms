<?php
/**
 * Progress Class
 * Handles student course progress tracking
 */

class Progress {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user's progress for a course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array Progress data
     */
    public function getCourseProgress($userId, $courseId) {
        // Get total lessons
        $totalLessons = $this->db->fetchColumn("
            SELECT COUNT(*) 
            FROM lessons l
            JOIN modules m ON l.module_id = m.id
            WHERE m.course_id = ?
        ", [$courseId]);
        
        // Get completed lessons
        $completedLessons = $this->db->fetchColumn("
            SELECT COUNT(DISTINCT lp.lesson_id)
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            WHERE lp.user_id = ? AND m.course_id = ? AND lp.completed = 1
        ", [$userId, $courseId]);
        
        // Get quiz attempts
        $quizAttempts = $this->db->fetchColumn("
            SELECT COUNT(*) 
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            WHERE qa.user_id = ? AND q.course_id = ?
        ", [$userId, $courseId]);
        
        // Get average quiz score
        $avgQuizScore = $this->db->fetchColumn("
            SELECT AVG(qa.score)
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            WHERE qa.user_id = ? AND q.course_id = ?
        ", [$userId, $courseId]) ?? 0;
        
        // Calculate percentage
        $percentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;
        
        // Get last accessed
        $lastAccessed = $this->db->fetchColumn("
            SELECT MAX(lp.last_accessed)
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            WHERE lp.user_id = ? AND m.course_id = ?
        ", [$userId, $courseId]);
        
        // Get time spent (in seconds)
        $timeSpent = $this->db->fetchColumn("
            SELECT SUM(lp.time_spent)
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            WHERE lp.user_id = ? AND m.course_id = ?
        ", [$userId, $courseId]) ?? 0;
        
        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'percentage' => $percentage,
            'quiz_attempts' => $quizAttempts,
            'avg_quiz_score' => round($avgQuizScore, 2),
            'last_accessed' => $lastAccessed,
            'time_spent' => $timeSpent,
            'time_spent_formatted' => $this->formatTimeSpent($timeSpent),
            'is_completed' => $percentage >= 100
        ];
    }
    
    /**
     * Get user's lesson progress
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID
     * @return array|null
     */
    public function getLessonProgress($userId, $lessonId) {
        $sql = "SELECT * FROM lesson_progress 
                WHERE user_id = ? AND lesson_id = ?";
        return $this->db->query($sql, [$userId, $lessonId])->fetch();
    }
    
    /**
     * Mark lesson as started
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID
     * @return bool
     */
    public function startLesson($userId, $lessonId) {
        // Check if progress exists
        $existing = $this->getLessonProgress($userId, $lessonId);
        
        if ($existing) {
            // Update last accessed
            $sql = "UPDATE lesson_progress 
                    SET last_accessed = NOW() 
                    WHERE user_id = ? AND lesson_id = ?";
            return $this->db->query($sql, [$userId, $lessonId]);
        } else {
            // Create new progress record
            $sql = "INSERT INTO lesson_progress (user_id, lesson_id, started_at, last_accessed) 
                    VALUES (?, ?, NOW(), NOW())";
            return $this->db->query($sql, [$userId, $lessonId]);
        }
    }
    
    /**
     * Mark lesson as completed
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID
     * @return bool
     */
    public function completeLesson($userId, $lessonId) {
        $existing = $this->getLessonProgress($userId, $lessonId);
        
        if ($existing) {
            if ($existing['completed']) {
                return true; // Already completed
            }
            
            $sql = "UPDATE lesson_progress 
                    SET completed = 1, completed_at = NOW(), last_accessed = NOW() 
                    WHERE user_id = ? AND lesson_id = ?";
            $result = $this->db->query($sql, [$userId, $lessonId]);
            
            // Check if course is now complete
            $this->checkCourseCompletion($userId, $lessonId);
            
            return $result;
        } else {
            $sql = "INSERT INTO lesson_progress 
                    (user_id, lesson_id, completed, started_at, completed_at, last_accessed) 
                    VALUES (?, ?, 1, NOW(), NOW(), NOW())";
            $result = $this->db->query($sql, [$userId, $lessonId]);
            
            $this->checkCourseCompletion($userId, $lessonId);
            
            return $result;
        }
    }
    
    /**
     * Update time spent on lesson
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID
     * @param int $seconds Time spent in seconds
     * @return bool
     */
    public function updateTimeSpent($userId, $lessonId, $seconds) {
        $existing = $this->getLessonProgress($userId, $lessonId);
        
        if ($existing) {
            $sql = "UPDATE lesson_progress 
                    SET time_spent = time_spent + ?, last_accessed = NOW() 
                    WHERE user_id = ? AND lesson_id = ?";
            return $this->db->query($sql, [$seconds, $userId, $lessonId]);
        }
        
        return false;
    }
    
    /**
     * Get current lesson (last accessed or first incomplete)
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return Lesson|null
     */
    public function getCurrentLesson($userId, $courseId) {
        // Try to get last accessed lesson
        $sql = "SELECT l.id
                FROM lesson_progress lp
                JOIN lessons l ON lp.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                WHERE lp.user_id = ? AND m.course_id = ?
                ORDER BY lp.last_accessed DESC
                LIMIT 1";
        
        $lessonId = $this->db->fetchColumn($sql, [$userId, $courseId]);
        
        if ($lessonId) {
            require_once __DIR__ . '/Lesson.php';
            return Lesson::find($lessonId);
        }
        
        // Get first lesson
        $sql = "SELECT l.id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = ?
                ORDER BY m.display_order ASC, l.display_order ASC
                LIMIT 1";
        
        $lessonId = $this->db->fetchColumn($sql, [$courseId]);
        
        if ($lessonId) {
            require_once __DIR__ . '/Lesson.php';
            return Lesson::find($lessonId);
        }
        
        return null;
    }
    
    /**
     * Get next incomplete lesson
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return Lesson|null
     */
    public function getNextLesson($userId, $courseId) {
        $sql = "SELECT l.id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
                WHERE m.course_id = ? AND (lp.completed IS NULL OR lp.completed = 0)
                ORDER BY m.display_order ASC, l.display_order ASC
                LIMIT 1";
        
        $lessonId = $this->db->fetchColumn($sql, [$userId, $courseId]);
        
        if ($lessonId) {
            require_once __DIR__ . '/Lesson.php';
            return Lesson::find($lessonId);
        }
        
        return null;
    }
    
    /**
     * Update last accessed time
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool
     */
    public function updateLastAccessed($userId, $courseId) {
        // Update enrollment
        $sql = "UPDATE enrollments 
                SET last_accessed = NOW() 
                WHERE user_id = ? AND course_id = ?";
        return $this->db->query($sql, [$userId, $courseId]);
    }
    
    /**
     * Check if course is complete and update enrollment status
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID (to get course)
     * @return bool
     */
    private function checkCourseCompletion($userId, $lessonId) {
        // Get course ID
        $courseId = $this->db->fetchColumn("
            SELECT m.course_id 
            FROM lessons l
            JOIN modules m ON l.module_id = m.id
            WHERE l.id = ?
        ", [$lessonId]);
        
        if (!$courseId) {
            return false;
        }
        
        $progress = $this->getCourseProgress($userId, $courseId);
        
        if ($progress['percentage'] >= 100) {
            // Mark enrollment as completed
            $sql = "UPDATE enrollments 
                    SET enrollment_status = 'completed', completed_at = NOW() 
                    WHERE user_id = ? AND course_id = ? AND enrollment_status != 'completed'";
            
            $updated = $this->db->query($sql, [$userId, $courseId]);
            
            if ($updated) {
                // Trigger certificate generation
                require_once __DIR__ . '/Certificate.php';
                Certificate::generate($userId, $courseId);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all user's progress across all courses
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserProgress($userId) {
        $sql = "SELECT 
                    e.course_id,
                    c.title,
                    c.slug,
                    c.thumbnail,
                    e.enrolled_at,
                    e.last_accessed,
                    e.enrollment_status
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.user_id = ?
                ORDER BY e.last_accessed DESC";
        
        $enrollments = $this->db->query($sql, [$userId])->fetchAll();
        
        $result = [];
        foreach ($enrollments as $enrollment) {
            $progress = $this->getCourseProgress($userId, $enrollment['course_id']);
            $result[] = array_merge($enrollment, $progress);
        }
        
        return $result;
    }
    
    /**
     * Format time spent in human-readable format
     * 
     * @param int $seconds Time in seconds
     * @return string
     */
    private function formatTimeSpent($seconds) {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            return floor($seconds / 60) . 'm';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }
    
    /**
     * Get lesson progress for all lessons in a course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array Lesson ID => progress data
     */
    public function getCourseLessonsProgress($userId, $courseId) {
        $sql = "SELECT 
                    l.id,
                    lp.completed,
                    lp.time_spent,
                    lp.last_accessed,
                    lp.started_at,
                    lp.completed_at
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ?
                WHERE m.course_id = ?
                ORDER BY m.display_order, l.display_order";
        
        $results = $this->db->query($sql, [$userId, $courseId])->fetchAll();
        
        $progress = [];
        foreach ($results as $row) {
            $progress[$row['id']] = $row;
        }
        
        return $progress;
    }
}