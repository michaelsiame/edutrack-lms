<?php
/**
 * Lesson Class
 * Handles lesson management and content
 */

class Lesson {
    private $db;
    private $id;
    private $data = [];
    
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }
    
    /**
     * Load lesson data
     */
    private function load() {
        $sql = "SELECT l.*, m.title as module_title, m.course_id, 
                c.title as course_title, c.slug as course_slug
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                WHERE l.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }
    
    /**
     * Check if lesson exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find lesson by ID
     */
    public static function find($id) {
        $lesson = new self($id);
        return $lesson->exists() ? $lesson : null;
    }
    
    /**
     * Get all lessons for a module
     */
    public static function getByModule($moduleId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM lessons 
                WHERE module_id = :module_id 
                ORDER BY order_index ASC";
        return $db->query($sql, ['module_id' => $moduleId])->fetchAll();
    }
    
    /**
     * Get all lessons for a course
     */
    public static function getByCourse($courseId) {
        $db = Database::getInstance();
        $sql = "SELECT l.*, m.title as module_title, m.order_index as module_order
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                ORDER BY m.order_index ASC, l.order_index ASC";
        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }
    
    /**
     * Create new lesson
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO lessons (
            module_id, title, slug, description, lesson_type,
            video_url, video_duration, content, attachments,
            order_index, is_preview, duration
        ) VALUES (
            :module_id, :title, :slug, :description, :lesson_type,
            :video_url, :video_duration, :content, :attachments,
            :order_index, :is_preview, :duration
        )";
        
        $params = [
            'module_id' => $data['module_id'],
            'title' => $data['title'],
            'slug' => $data['slug'] ?? slugify($data['title']),
            'description' => $data['description'] ?? '',
            'lesson_type' => $data['lesson_type'] ?? 'video',
            'video_url' => $data['video_url'] ?? null,
            'video_duration' => $data['video_duration'] ?? null,
            'content' => $data['content'] ?? '',
            'attachments' => $data['attachments'] ?? null,
            'order_index' => $data['order_index'] ?? 0,
            'is_preview' => $data['is_preview'] ?? 0,
            'duration' => $data['duration'] ?? 0
        ];
        
        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update lesson
     */
    public function update($data) {
        $allowed = ['title', 'slug', 'description', 'lesson_type', 'video_url', 
                   'video_duration', 'content', 'attachments', 'order_index', 
                   'is_preview', 'duration'];
        
        $updates = [];
        $params = ['id' => $this->id];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE lessons SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete lesson
     */
    public function delete() {
        $sql = "DELETE FROM lessons WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get next lesson
     */
    public function getNext() {
        $sql = "SELECT l.id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                AND (m.order_index > (SELECT m2.order_index FROM modules m2 WHERE m2.id = :module_id)
                     OR (m.order_index = (SELECT m2.order_index FROM modules m2 WHERE m2.id = :module_id) 
                         AND l.order_index > :order_index))
                ORDER BY m.order_index ASC, l.order_index ASC
                LIMIT 1";
        
        $result = $this->db->query($sql, [
            'course_id' => $this->getCourseId(),
            'module_id' => $this->getModuleId(),
            'order_index' => $this->getOrderIndex()
        ])->fetch();
        
        return $result ? self::find($result['id']) : null;
    }
    
    /**
     * Get previous lesson
     */
    public function getPrevious() {
        $sql = "SELECT l.id
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                AND (m.order_index < (SELECT m2.order_index FROM modules m2 WHERE m2.id = :module_id)
                     OR (m.order_index = (SELECT m2.order_index FROM modules m2 WHERE m2.id = :module_id) 
                         AND l.order_index < :order_index))
                ORDER BY m.order_index DESC, l.order_index DESC
                LIMIT 1";
        
        $result = $this->db->query($sql, [
            'course_id' => $this->getCourseId(),
            'module_id' => $this->getModuleId(),
            'order_index' => $this->getOrderIndex()
        ])->fetch();
        
        return $result ? self::find($result['id']) : null;
    }
    
    /**
     * Check if user completed this lesson
     */
    public function isCompletedByUser($userId) {
        $sql = "SELECT completed FROM lesson_progress 
                WHERE lesson_id = :lesson_id AND user_id = :user_id";
        
        $result = $this->db->query($sql, [
            'lesson_id' => $this->id,
            'user_id' => $userId
        ])->fetch();
        
        return $result ? (bool)$result['completed'] : false;
    }
    
    /**
     * Get user's progress for this lesson
     */
    public function getUserProgress($userId) {
        $sql = "SELECT * FROM lesson_progress 
                WHERE lesson_id = :lesson_id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            'lesson_id' => $this->id,
            'user_id' => $userId
        ])->fetch();
    }
    
    /**
     * Mark as completed for user
     */
    public function markCompleted($userId) {
        $sql = "INSERT INTO lesson_progress (
            user_id, course_id, lesson_id, completed, completed_at
        ) VALUES (
            :user_id, :course_id, :lesson_id, 1, NOW()
        ) ON DUPLICATE KEY UPDATE 
            completed = 1, completed_at = NOW()";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $this->getCourseId(),
            'lesson_id' => $this->id
        ]);
    }
    
    /**
     * Update video progress
     */
    public function updateProgress($userId, $progress) {
        $sql = "INSERT INTO lesson_progress (
            user_id, course_id, lesson_id, progress_seconds, last_position
        ) VALUES (
            :user_id, :course_id, :lesson_id, :progress, :progress
        ) ON DUPLICATE KEY UPDATE 
            progress_seconds = :progress, last_position = :progress, updated_at = NOW()";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'course_id' => $this->getCourseId(),
            'lesson_id' => $this->id,
            'progress' => $progress
        ]);
    }
    
    /**
     * Get lesson attachments
     */
    public function getAttachments() {
        if (!$this->getAttachmentsData()) {
            return [];
        }
        return json_decode($this->getAttachmentsData(), true) ?? [];
    }
    
    /**
     * Get video embed HTML
     */
    public function getVideoEmbed($autoplay = false) {
        if ($this->getType() !== 'video' || !$this->getVideoUrl()) {
            return null;
        }
        
        $url = $this->getVideoUrl();
        $autoplayParam = $autoplay ? '&autoplay=1' : '';
        
        // YouTube
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            $embedUrl = getYoutubeEmbedUrl($url) . $autoplayParam;
            return '<iframe src="' . $embedUrl . '" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
        
        // Vimeo
        if (strpos($url, 'vimeo.com') !== false) {
            $embedUrl = getVimeoEmbedUrl($url) . $autoplayParam;
            return '<iframe src="' . $embedUrl . '" class="w-full h-full" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }
        
        // Direct video
        return '<video controls class="w-full h-full" ' . ($autoplay ? 'autoplay' : '') . '>
                    <source src="' . htmlspecialchars($url) . '" type="video/mp4">
                    Your browser does not support the video tag.
                </video>';
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getModuleId() { return $this->data['module_id'] ?? null; }
    public function getModuleTitle() { return $this->data['module_title'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getCourseSlug() { return $this->data['course_slug'] ?? ''; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getSlug() { return $this->data['slug'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getType() { return $this->data['lesson_type'] ?? 'video'; }
    public function getVideoUrl() { return $this->data['video_url'] ?? ''; }
    public function getVideoDuration() { return $this->data['video_duration'] ?? 0; }
    public function getContent() { return $this->data['content'] ?? ''; }
    public function getAttachmentsData() { return $this->data['attachments'] ?? null; }
    public function getOrderIndex() { return $this->data['order_index'] ?? 0; }
    public function isPreview() { return $this->data['is_preview'] == 1; }
    public function getDuration() { return $this->data['duration'] ?? 0; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
    /**
     * Get lesson URL
     */
    public function getUrl() {
        return url('learn.php?course=' . $this->getCourseSlug() . '&lesson=' . $this->getId());
    }
    
    /**
     * Check if video type
     */
    public function isVideo() {
        return $this->getType() === 'video';
    }
    
    /**
     * Check if text/article type
     */
    public function isArticle() {
        return $this->getType() === 'article';
    }
    
    /**
     * Check if quiz type
     */
    public function isQuiz() {
        return $this->getType() === 'quiz';
    }
}