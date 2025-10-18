<?php
/**
 * Progress Class
 * Tracks user progress through courses
 */

class Progress {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user's course progress
     */
    public function getCourseProgress($userId, $courseId) {
        $sql = "SELECT 
            e.progress_percentage,
            e.total_time_spent,
            e.last_accessed_at,
            COUNT(DISTINCT lp.lesson_id) as completed_lessons,
            (SELECT COUNT(*) FROM lessons l 
             JOIN modules m ON l.module_id = m.id 
             WHERE m.course_id = :course_id) as total_lessons
            FROM enrollments e
            LEFT JOIN lesson_progress lp ON e.user_id = lp.user_id 
                AND e.course_id = lp.course_id AND lp.completed = 1
            WHERE e.user_id = :user_id AND e.course_id = :course_id
            GROUP BY e.id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
    }
    
    /**
     * Get all completed lessons for user in course
     */
    public function getCompletedLessons($userId, $courseId) {
        $sql = "SELECT lesson_id, completed_at 
                FROM lesson_progress 
                WHERE user_id = :user_id AND course_id = :course_id AND completed = 1";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetchAll();
    }
    
    /**
     * Get lesson progress
     */
    public function getLessonProgress($userId, $lessonId) {
        $sql = "SELECT * FROM lesson_progress 
                WHERE user_id = :user_id AND lesson_id = :lesson_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'lesson_id' => $lessonId
        ])->fetch();
    }
    
    /**
     * Update lesson progress
     */
    public function updateLessonProgress($userId, $courseId, $lessonId, $data) {
        $sql = "INSERT INTO lesson_progress (
            user_id, course_id, lesson_id, progress_seconds, 
            last_position, completed, completed_at
        ) VALUES (
            :user_id, :course_id, :lesson_id, :progress_seconds,
            :last_position, :completed, :completed_at
        ) ON DUPLICATE KEY UPDATE
            progress_seconds = VALUES(progress_seconds),
            last_position = VALUES(last_position),
            completed = VALUES(completed),
            completed_at = VALUES(completed_at),
            updated_at = NOW()";
        
        $params = [
            'user_id' => $userId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'progress_seconds' => $data['progress_seconds'] ?? 0,
            'last_position' => $data['last_position'] ?? 0,
            'completed' => $data['completed'] ?? 0,
            'completed_at' => $data['completed'] ? date('Y-m-d H:i:s') : null
        ];
        
        if ($this->db->query($sql, $params)) {
            // Recalculate course progress
            $this->recalculateCourseProgress($userId, $courseId);
            return true;
        }
        return false;
    }
    
    /**
     * Mark lesson as complete
     */
    public function markLessonComplete($userId, $courseId, $lessonId) {
        return $this->updateLessonProgress($userId, $courseId, $lessonId, [
            'completed' => 1,
            'progress_seconds' => 0,
            'last_position' => 0
        ]);
    }
    
    /**
     * Recalculate course progress percentage
     */
    public function recalculateCourseProgress($userId, $courseId) {
        // Get total and completed lessons
        $sql = "SELECT 
            COUNT(DISTINCT l.id) as total_lessons,
            COUNT(DISTINCT CASE WHEN lp.completed = 1 THEN lp.lesson_id END) as completed_lessons
            FROM lessons l
            JOIN modules m ON l.module_id = m.id
            LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = :user_id
            WHERE m.course_id = :course_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        $totalLessons = $result['total_lessons'] ?? 0;
        $completedLessons = $result['completed_lessons'] ?? 0;
        
        if ($totalLessons == 0) {
            return 0;
        }
        
        $percentage = ($completedLessons / $totalLessons) * 100;
        
        // Update enrollment
        $sql = "UPDATE enrollments 
                SET progress_percentage = :percentage,
                    enrollment_status = CASE WHEN :percentage >= 100 THEN 'completed' ELSE enrollment_status END,
                    completed_at = CASE WHEN :percentage >= 100 THEN NOW() ELSE completed_at END
                WHERE user_id = :user_id AND course_id = :course_id";
        
        $this->db->query($sql, [
            'percentage' => $percentage,
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
        
        return $percentage;
    }
    
    /**
     * Update last accessed time
     */
    public function updateLastAccessed($userId, $courseId) {
        $sql = "UPDATE enrollments 
                SET last_accessed_at = NOW() 
                WHERE user_id = :user_id AND course_id = :course_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
    }
    
    /**
     * Add time spent
     */
    public function addTimeSpent($userId, $courseId, $seconds) {
        $sql = "UPDATE enrollments 
                SET total_time_spent = total_time_spent + :seconds 
                WHERE user_id = :user_id AND course_id = :course_id";
        
        return $this->db->query($sql, [
            'seconds' => $seconds,
            'user_id' => $userId,
            'course_id' => $courseId
        ]);
    }
    
    /**
     * Get user's current lesson in course
     */
    public function getCurrentLesson($userId, $courseId) {
        // Get last accessed incomplete lesson
        $sql = "SELECT l.id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = :user_id
                WHERE m.course_id = :course_id
                AND (lp.completed IS NULL OR lp.completed = 0)
                ORDER BY m.order_index ASC, l.order_index ASC
                LIMIT 1";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        if ($result) {
            require_once __DIR__ . '/Lesson.php';
            return Lesson::find($result['id']);
        }
        
        // If all complete, return first lesson
        $sql = "SELECT l.id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                ORDER BY m.order_index ASC, l.order_index ASC
                LIMIT 1";
        
        $result = $this->db->query($sql, ['course_id' => $courseId])->fetch();
        
        if ($result) {
            require_once __DIR__ . '/Lesson.php';
            return Lesson::find($result['id']);
        }
        
        return null;
    }
    
    /**
     * Get module progress
     */
    public function getModuleProgress($userId, $moduleId) {
        $sql = "SELECT 
            COUNT(DISTINCT l.id) as total_lessons,
            COUNT(DISTINCT CASE WHEN lp.completed = 1 THEN lp.lesson_id END) as completed_lessons
            FROM lessons l
            LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = :user_id
            WHERE l.module_id = :module_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'module_id' => $moduleId
        ])->fetch();
        
        $total = $result['total_lessons'] ?? 0;
        $completed = $result['completed_lessons'] ?? 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0
        ];
    }
    
    /**
     * Get user's learning statistics
     */
    public function getUserStats($userId) {
        $sql = "SELECT 
            COUNT(DISTINCT e.course_id) as total_courses,
            SUM(CASE WHEN e.enrollment_status = 'completed' THEN 1 ELSE 0 END) as completed_courses,
            SUM(e.total_time_spent) as total_time_spent,
            COUNT(DISTINCT lp.lesson_id) as total_lessons_completed
            FROM enrollments e
            LEFT JOIN lesson_progress lp ON e.user_id = lp.user_id AND lp.completed = 1
            WHERE e.user_id = :user_id";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetch();
    }
    
    /**
     * Get recent activity
     */
    public function getRecentActivity($userId, $limit = 10) {
        $sql = "SELECT 
            lp.lesson_id,
            lp.completed,
            lp.completed_at,
            lp.updated_at,
            l.title as lesson_title,
            c.title as course_title,
            c.slug as course_slug
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE lp.user_id = :user_id
            ORDER BY lp.updated_at DESC
            LIMIT :limit";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'limit' => $limit
        ])->fetchAll();
    }
    
    /**
     * Get learning streak (consecutive days)
     */
    public function getLearningStreak($userId) {
        $sql = "SELECT DATE(updated_at) as activity_date 
                FROM lesson_progress 
                WHERE user_id = :user_id 
                GROUP BY DATE(updated_at)
                ORDER BY activity_date DESC";
        
        $dates = $this->db->query($sql, ['user_id' => $userId])->fetchAll();
        
        if (empty($dates)) {
            return 0;
        }
        
        $streak = 1;
        $currentDate = new DateTime($dates[0]['activity_date']);
        
        for ($i = 1; $i < count($dates); $i++) {
            $prevDate = new DateTime($dates[$i]['activity_date']);
            $diff = $currentDate->diff($prevDate)->days;
            
            if ($diff == 1) {
                $streak++;
                $currentDate = $prevDate;
            } else {
                break;
            }
        }
        
        return $streak;
    }
}