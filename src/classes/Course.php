<?php
/**
 * Course Class
 * Handles all course-related operations
 */

class Course {
    private $db;
    private $id;
    private $data = [];
    
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        if ($id) {
            $this->id = (int)$id;
            $this->load();
        }
    }
    
    /**
     * Load course data from database
     */
    private function load() {
        $sql = "SELECT c.*, 
                       cat.name as category_name,
                       u.first_name as instructor_first_name, 
                       u.last_name as instructor_last_name,
                       (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_students,
                       (SELECT AVG(rating) FROM course_reviews WHERE course_id = c.id AND status = 'approved') as avg_rating,
                       (SELECT COUNT(*) FROM course_reviews WHERE course_id = c.id AND status = 'approved') as total_reviews
                FROM courses c
                LEFT JOIN course_categories cat ON c.category_id = cat.id
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE c.id = :id";

        $result = $this->db->query($sql, ['id' => $this->id])->fetch();
        $this->data = $result ?: [];
    }
    
    /**
     * Check if course exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Get course by ID
     */
    public static function find($id) {
        $course = new self($id);
        return $course->exists() ? $course : null;
    }
    
    /**
     * Get course by slug
     */
    public static function findBySlug($slug) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM courses WHERE slug = :slug AND status = 'published'";
        $result = $db->query($sql, ['slug' => $slug])->fetch();
        
        if ($result) {
            return new self($result['id']);
        }
        return null;
    }
    
    /**
     * Get all courses with filters
     */
    public static function all($filters = []) {
        $db = Database::getInstance();

        $sql = "SELECT c.*, 
                       cat.name as category_name,
                       u.first_name as instructor_first_name, 
                       u.last_name as instructor_last_name,
                       (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_students,
                       (SELECT AVG(rating) FROM course_reviews WHERE course_id = c.id AND status = 'approved') as avg_rating,
                       (SELECT COUNT(*) FROM course_reviews WHERE course_id = c.id AND status = 'approved') as total_reviews
                FROM courses c
                LEFT JOIN course_categories cat ON c.category_id = cat.id
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        // Filter by status (Default to published unless specified)
        if (!isset($filters['include_draft']) && !isset($filters['instructor_id'])) {
            $sql .= " AND c.status = 'published'";
        } elseif (isset($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }

        // Filter by instructor
        if (!empty($filters['instructor_id'])) {
            $sql .= " AND c.instructor_id = :instructor_id";
            $params['instructor_id'] = $filters['instructor_id'];
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $sql .= " AND c.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        // Filter by level
        if (!empty($filters['level'])) {
            $sql .= " AND c.level = :level";
            $params['level'] = $filters['level'];
        }
        
        // Filter by price
        if (isset($filters['is_free'])) {
            if ($filters['is_free']) {
                $sql .= " AND c.price = 0";
            } else {
                $sql .= " AND c.price > 0";
            }
        }
        
        // Filter by TEVETA
        if (!empty($filters['teveta_accredited'])) {
            $sql .= " AND c.is_teveta_certified = 1";
        }
        
        // Search
        if (!empty($filters['search'])) {
            $sql .= " AND (c.title LIKE :search OR c.description LIKE :search OR c.learning_outcomes LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Sorting
        $allowedOrderColumns = [
            'created_at', 'updated_at', 'title', 'price', 
            'rating', 'enrollment_count', 'level', 'featured'
        ];
        
        $orderByInput = $filters['order_by'] ?? 'created_at';
        $orderDir = strtoupper($filters['order_dir'] ?? 'DESC');
        
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'DESC';
        }

        // Map sort keys to actual SQL columns or aliases
        switch ($orderByInput) {
            case 'rating':
                $orderBy = 'avg_rating';
                break;
            case 'enrollment_count':
            case 'total_students':
                $orderBy = 'total_students';
                break;
            case 'price':
                $orderBy = 'c.price';
                break;
            case 'title':
                $orderBy = 'c.title';
                break;
            default:
                $orderBy = 'c.created_at';
                break;
        }

        $sql .= " ORDER BY $orderBy $orderDir";
        
        // Pagination
        if (isset($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET :offset";
                $params['offset'] = (int)$filters['offset'];
            }
        }
        
        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get featured courses
     */
    public static function featured($limit = 6) {
        return self::all([
            'limit' => $limit,
            'order_by' => 'featured',
            'order_dir' => 'DESC'
        ]);
    }
    
    /**
     * Get popular courses (Most Enrolled)
     */
    public static function popular($limit = 6) {
        return self::all([
            'limit' => $limit,
            'order_by' => 'enrollment_count',
            'order_dir' => 'DESC'
        ]);
    }
    
    /**
     * Get recent courses
     */
    public static function recent($limit = 6) {
        return self::all([
            'limit' => $limit,
            'order_by' => 'created_at',
            'order_dir' => 'DESC'
        ]);
    }

    /**
     * Get courses by instructor ID
     */
    public static function getByInstructor($instructorId) {
        return self::all(['instructor_id' => $instructorId]);
    }
    
    /**
     * Count total courses
     */
    public static function count($filters = []) {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) as total FROM courses WHERE status = 'published'";
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE :search OR description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $result = $db->query($sql, $params)->fetch();
        return $result['total'];
    }
    
    /**
     * Create new course
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Generate slug from title if not provided
        if (empty($data['slug'])) {
            $data['slug'] = self::generateSlug($data['title']);
        }

        // Ensure array keys exist
        $data = array_merge([
            'short_description' => '',
            'level' => 'beginner',
            'language' => 'English',
            'thumbnail_url' => null,
            'video_intro_url' => null,
            'price' => 0,
            'total_hours' => 0,
            'duration_weeks' => 0,
            'status' => 'draft',
            'prerequisites' => null,
            'learning_outcomes' => null,
            'target_audience' => null,
            'is_teveta_certified' => 0
        ], $data);

        $sql = "INSERT INTO courses (
            instructor_id, title, slug, description, short_description, category_id,
            level, language, thumbnail_url, video_intro_url, price,
            total_hours, duration_weeks, status, prerequisites, learning_outcomes, target_audience,
            is_teveta_certified, created_at, updated_at
        ) VALUES (
            :instructor_id, :title, :slug, :description, :short_description, :category_id,
            :level, :language, :thumbnail_url, :video_intro_url, :price,
            :total_hours, :duration_weeks, :status, :prerequisites, :learning_outcomes, :target_audience,
            :is_teveta_certified, NOW(), NOW()
        )";

        $params = [
            'instructor_id' => $data['instructor_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'category_id' => $data['category_id'],
            'level' => $data['level'],
            'language' => $data['language'],
            'thumbnail_url' => $data['thumbnail_url'],
            'video_intro_url' => $data['video_intro_url'],
            'price' => $data['price'],
            'total_hours' => $data['total_hours'],
            'duration_weeks' => $data['duration_weeks'],
            'status' => $data['status'],
            'prerequisites' => $data['prerequisites'],
            'learning_outcomes' => $data['learning_outcomes'],
            'target_audience' => $data['target_audience'],
            'is_teveta_certified' => $data['is_teveta_certified']
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update course
     */
    public function update($data) {
        $fields = [];
        $params = [];

        // Whitelist allowed fields for update
        $allowedFields = [
            'title', 'slug', 'description', 'short_description', 'category_id',
            'level', 'language', 'thumbnail_url', 'video_intro_url', 'price',
            'discount_price', 'duration_weeks', 'total_hours', 'max_students',
            'status', 'featured', 'prerequisites', 'learning_outcomes', 'target_audience',
            'is_teveta_certified'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $params['id'] = $this->id;

        $sql = "UPDATE courses SET " . implode(', ', $fields) . " WHERE id = :id";

        if ($this->db->query($sql, $params)) {
            $this->load(); // Reload data
            return true;
        }
        return false;
    }
    
    /**
     * Delete course
     */
    public function delete() {
        // Optional: Check for enrollments before deleting
        $sql = "DELETE FROM courses WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get course modules
     */
    public function getModules() {
        $sql = "SELECT * FROM modules 
                WHERE course_id = :course_id 
                ORDER BY display_order ASC";
        return $this->db->query($sql, ['course_id' => $this->id])->fetchAll();
    }
    
    /**
     * Get course lessons with Module info
     */
    public function getLessons() {
        $sql = "SELECT l.*, m.title as module_title 
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                ORDER BY m.display_order ASC, l.display_order ASC";
        return $this->db->query($sql, ['course_id' => $this->id])->fetchAll();
    }
    
    /**
     * Get total lessons count
     */
    public function getTotalLessons() {
        $sql = "SELECT COUNT(l.id) as total
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id";
        $result = $this->db->query($sql, ['course_id' => $this->id])->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get course reviews
     */
    public function getReviews($limit = null) {
        $sql = "SELECT r.*, u.first_name, u.last_name, up.avatar
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE r.course_id = :course_id
                ORDER BY r.created_at DESC";
        
        $params = ['course_id' => $this->id];

        if ($limit) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$limit;
        }
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get rating breakdown
     */
    public function getRatingBreakdown() {
        $sql = "SELECT 
                COUNT(*) as total_reviews,
                COALESCE(AVG(rating), 0) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM course_reviews 
                WHERE course_id = :course_id";
        
        return $this->db->query($sql, ['course_id' => $this->id])->fetch();
    }
    
    /**
     * Get enrolled students
     */
    public function getEnrolledStudents($limit = null) {
        $sql = "SELECT u.first_name, u.last_name, u.email, e.enrolled_at, e.progress_percentage
                FROM enrollments e
                JOIN users u ON e.user_id = u.id
                WHERE e.course_id = :course_id
                ORDER BY e.enrolled_at DESC";
        
        $params = ['course_id' => $this->id];

        if ($limit) {
            $sql .= " LIMIT :limit";
            $params['limit'] = (int)$limit;
        }
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Check if user is enrolled
     */
    public function isUserEnrolled($userId) {
        $sql = "SELECT id FROM enrollments 
                WHERE course_id = :course_id AND user_id = :user_id";
        $result = $this->db->query($sql, [
            'course_id' => $this->id,
            'user_id' => $userId
        ])->fetch();
        
        return !empty($result);
    }
    
    /**
     * Generate unique slug
     */
    private static function generateSlug($title) {
        $db = Database::getInstance();
        $slug = function_exists('slugify') ? slugify($title) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT id FROM courses WHERE slug = :slug";
            $result = $db->query($sql, ['slug' => $slug])->fetch();
            
            if (!$result) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    // --- Getters (Updated for key consistency) ---
    public function getId() { return $this->data['id'] ?? null; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getSlug() { return $this->data['slug'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getShortDescription() { return $this->data['short_description'] ?? ''; }
    
    // Mapped correctly to database columns used in create/update
    public function getThumbnailUrl() {
        $thumbnail = $this->data['thumbnail_url'] ?? null;
        
        if (!empty($thumbnail)) {
            // 1. If it's already a full URL (e.g. from S3), return it
            if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
                return $thumbnail;
            }
            
            // 3. Fallback: Return a standard relative path
            // Assumes your web root has an 'uploads' folder
            return '/uploads/courses/' . $thumbnail;
        }

        // Handle Placeholder
        if (function_exists('asset')) {
            return asset('images/course-placeholder.jpg');
        }
        
        return '/assets/images/course-placeholder.jpg';
    }

    public function getVideoIntroUrl() { return $this->data['video_intro_url'] ?? ''; }
    
    public function getCategoryId() { return $this->data['category_id'] ?? null; }
    public function getCategoryName() { return $this->data['category_name'] ?? ''; }
    
    public function getInstructorId() { return $this->data['instructor_id'] ?? null; }
    public function getInstructorName() { 
        return trim(($this->data['instructor_first_name'] ?? '') . ' ' . ($this->data['instructor_last_name'] ?? ''));
    }
    
    public function getLevel() { return $this->data['level'] ?? 'Beginner'; }
    public function getPrice() { return (float)($this->data['price'] ?? 0); }
    public function getDiscountPrice() { return (float)($this->data['discount_price'] ?? 0); }
    
    public function getTotalHours() { return $this->data['total_hours'] ?? 0; }
    public function getDurationWeeks() { return $this->data['duration_weeks'] ?? 0; }
    
    public function getLanguage() { return $this->data['language'] ?? 'English'; }
    public function getStatus() { return $this->data['status'] ?? 'draft'; }
    
    public function isTevetaCertified() {
        return !empty($this->data['is_teveta_certified']);
    }

    public function isFeatured() { return !empty($this->data['featured']); }
    public function getTotalStudents() { return (int)($this->data['total_students'] ?? 0); }
    public function getAvgRating() { return (float)($this->data['avg_rating'] ?? 0); }
    public function getTotalReviews() { return (int)($this->data['total_reviews'] ?? 0); }
    
    public function isFree() { return $this->getPrice() <= 0; }
    public function isPublished() { return $this->getStatus() === 'published'; }
    
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getPrerequisites() { return $this->data['prerequisites'] ?? ''; }
    public function getLearningOutcomes() { return $this->data['learning_outcomes'] ?? ''; }
    public function getTargetAudience() { return $this->data['target_audience'] ?? ''; }

    public function getFormattedPrice() {
        return $this->isFree() ? 'Free' : (function_exists('formatCurrency') ? formatCurrency($this->getPrice()) : '$' . number_format($this->getPrice(), 2));
    }
}