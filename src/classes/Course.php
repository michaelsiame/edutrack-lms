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
            $this->id = $id;
            $this->load();
        }
    }
    
    /**
     * Load course data from database
     */
    private function load() {
        $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                u.first_name as instructor_first_name, u.last_name as instructor_last_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_students,
                (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE course_id = c.id) as total_reviews
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE c.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
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
        
        $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                u.first_name as instructor_first_name, u.last_name as instructor_last_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as total_students,
                (SELECT AVG(rating) FROM reviews WHERE course_id = c.id) as avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE course_id = c.id) as total_reviews
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        // Filter by status
        if (!isset($filters['include_draft'])) {
            $sql .= " AND c.status = 'published'";
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
            $sql .= " AND c.teveta_accredited = 1";
        }
        
        // Search
        if (!empty($filters['search'])) {
            $sql .= " AND (c.title LIKE :search OR c.description LIKE :search OR c.what_you_will_learn LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Sorting
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        $sql .= " ORDER BY c.$orderBy $orderDir";
        
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
     * Get popular courses
     */
    public static function popular($limit = 6) {
        $db = Database::getInstance();
        
        $sql = "SELECT c.*, cat.name as category_name,
                u.first_name as instructor_first_name, u.last_name as instructor_last_name,
                COUNT(e.id) as total_students,
                AVG(r.rating) as avg_rating
                FROM courses c
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN users u ON c.instructor_id = u.id
                LEFT JOIN enrollments e ON c.id = e.course_id
                LEFT JOIN reviews r ON c.id = r.course_id
                WHERE c.status = 'published'
                GROUP BY c.id
                ORDER BY total_students DESC, avg_rating DESC
                LIMIT :limit";
        
        return $db->query($sql, ['limit' => $limit])->fetchAll();
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
        
        // Generate slug from title
        if (empty($data['slug'])) {
            $data['slug'] = self::generateSlug($data['title']);
        }
        
        $sql = "INSERT INTO courses (
            title, slug, description, short_description, what_you_will_learn,
            requirements, thumbnail, category_id, instructor_id, level,
            price, duration, language, status, teveta_accredited,
            teveta_course_code, certificate_available, featured
        ) VALUES (
            :title, :slug, :description, :short_description, :what_you_will_learn,
            :requirements, :thumbnail, :category_id, :instructor_id, :level,
            :price, :duration, :language, :status, :teveta_accredited,
            :teveta_course_code, :certificate_available, :featured
        )";
        
        $params = [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? '',
            'what_you_will_learn' => $data['what_you_will_learn'] ?? '',
            'requirements' => $data['requirements'] ?? '',
            'thumbnail' => $data['thumbnail'] ?? null,
            'category_id' => $data['category_id'],
            'instructor_id' => $data['instructor_id'],
            'level' => $data['level'] ?? 'beginner',
            'price' => $data['price'] ?? 0,
            'duration' => $data['duration'] ?? 0,
            'language' => $data['language'] ?? 'English',
            'status' => $data['status'] ?? 'draft',
            'teveta_accredited' => $data['teveta_accredited'] ?? 0,
            'teveta_course_code' => $data['teveta_course_code'] ?? null,
            'certificate_available' => $data['certificate_available'] ?? 1,
            'featured' => $data['featured'] ?? 0
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
        $sql = "UPDATE courses SET
            title = :title,
            slug = :slug,
            description = :description,
            short_description = :short_description,
            what_you_will_learn = :what_you_will_learn,
            requirements = :requirements,
            category_id = :category_id,
            level = :level,
            price = :price,
            duration = :duration,
            language = :language,
            status = :status,
            teveta_accredited = :teveta_accredited,
            teveta_course_code = :teveta_course_code,
            certificate_available = :certificate_available,
            featured = :featured,
            updated_at = NOW()
            WHERE id = :id";
        
        $params = array_merge($data, ['id' => $this->id]);
        
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
        $sql = "DELETE FROM courses WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get course modules
     */
    public function getModules() {
        $sql = "SELECT * FROM modules 
                WHERE course_id = :course_id 
                ORDER BY order_index ASC";
        return $this->db->query($sql, ['course_id' => $this->id])->fetchAll();
    }
    
    /**
     * Get course lessons
     */
    public function getLessons() {
        $sql = "SELECT l.*, m.title as module_title 
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                ORDER BY m.order_index ASC, l.order_index ASC";
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
        return $result['total'];
    }
    
    /**
     * Get course reviews
     */
    public function getReviews($limit = null) {
        $sql = "SELECT r.*, u.first_name, u.last_name, up.avatar
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE r.course_id = :course_id AND r.status = 'approved'
                ORDER BY r.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
            return $this->db->query($sql, ['course_id' => $this->id, 'limit' => $limit])->fetchAll();
        }
        
        return $this->db->query($sql, ['course_id' => $this->id])->fetchAll();
    }
    
    /**
     * Get rating breakdown
     */
    public function getRatingBreakdown() {
        $sql = "SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews 
                WHERE course_id = :course_id AND status = 'approved'";
        
        return $this->db->query($sql, ['course_id' => $this->id])->fetch();
    }
    
    /**
     * Get enrolled students
     */
    public function getEnrolledStudents($limit = null) {
        $sql = "SELECT u.*, e.enrolled_at, e.progress_percentage
                FROM enrollments e
                JOIN users u ON e.user_id = u.id
                WHERE e.course_id = :course_id
                ORDER BY e.enrolled_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
            return $this->db->query($sql, ['course_id' => $this->id, 'limit' => $limit])->fetchAll();
        }
        
        return $this->db->query($sql, ['course_id' => $this->id])->fetchAll();
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
        $slug = slugify($title);
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
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getSlug() { return $this->data['slug'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getShortDescription() { return $this->data['short_description'] ?? ''; }
    public function getWhatYouWillLearn() { return $this->data['what_you_will_learn'] ?? ''; }
    public function getRequirements() { return $this->data['requirements'] ?? ''; }
    public function getThumbnail() { return $this->data['thumbnail'] ?? null; }
    public function getCategoryId() { return $this->data['category_id'] ?? null; }
    public function getCategoryName() { return $this->data['category_name'] ?? ''; }
    public function getCategorySlug() { return $this->data['category_slug'] ?? ''; }
    public function getInstructorId() { return $this->data['instructor_id'] ?? null; }
    public function getInstructorName() { 
        return trim(($this->data['instructor_first_name'] ?? '') . ' ' . ($this->data['instructor_last_name'] ?? ''));
    }
    public function getLevel() { return $this->data['level'] ?? 'beginner'; }
    public function getPrice() { return $this->data['price'] ?? 0; }
    public function getDuration() { return $this->data['duration'] ?? 0; }
    public function getLanguage() { return $this->data['language'] ?? 'English'; }
    public function getStatus() { return $this->data['status'] ?? 'draft'; }
    public function isTeveta() { return $this->data['teveta_accredited'] == 1; }
    public function getTevetaCourseCode() { return $this->data['teveta_course_code'] ?? ''; }
    public function hasCertificate() { return $this->data['certificate_available'] == 1; }
    public function isFeatured() { return $this->data['featured'] == 1; }
    public function getTotalStudents() { return $this->data['total_students'] ?? 0; }
    public function getAvgRating() { return $this->data['avg_rating'] ?? 0; }
    public function getTotalReviews() { return $this->data['total_reviews'] ?? 0; }
    public function isFree() { return $this->getPrice() == 0; }
    public function isPublished() { return $this->getStatus() == 'published'; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
    /**
     * Get formatted price
     */
    public function getFormattedPrice() {
        return $this->isFree() ? 'Free' : formatCurrency($this->getPrice());
    }
    
    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl() {
        if ($this->getThumbnail()) {
            return upload_url('courses/thumbnails/' . $this->getThumbnail());
        }
        return asset('images/course-placeholder.jpg');
    }
    
    /**
     * Get course URL
     */
    public function getUrl() {
        return url('course.php?slug=' . $this->getSlug());
    }
    
    /**
     * Get enroll URL
     */
    public function getEnrollUrl() {
        return url('enroll.php?course_id=' . $this->getId());
    }
}