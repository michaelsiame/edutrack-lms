<?php
/**
 * Category Class
 * Handles course categories
 */

class Category {
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
     * Load category data
     */
    private function load() {
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM courses WHERE category_id = c.id) as course_count
                FROM course_categories c
                WHERE c.id = ?";

        $this->data = $this->db->query($sql, [$this->id])->fetch();
    }
    
    /**
     * Check if category exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find category by ID
     */
    public static function find($id) {
        $category = new self($id);
        return $category->exists() ? $category : null;
    }
    
    /**
     * Find category by slug
     */
    public static function findBySlug($slug) {
        $db = Database::getInstance();
        $sql = "SELECT id FROM course_categories WHERE slug = ?";
        $id = $db->fetchColumn($sql, [$slug]);

        return $id ? new self($id) : null;
    }
    
    /**
     * Get all categories
     */
    public static function all($options = []) {
        $db = Database::getInstance();

        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM courses WHERE category_id = c.id AND status = 'published') as course_count
                FROM course_categories c";

        $where = [];
        $params = [];

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY c.name ASC";

        if (isset($options['limit'])) {
            $sql .= " LIMIT " . (int)$options['limit'];
        }

        return $db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get active categories with published courses
     */
    public static function getActiveWithCourses() {
        $db = Database::getInstance();
        $sql = "SELECT c.*,
                COUNT(co.id) as course_count
                FROM course_categories c
                LEFT JOIN courses co ON c.id = co.category_id AND co.status = 'published'
                GROUP BY c.id
                HAVING course_count > 0
                ORDER BY c.name ASC";

        return $db->query($sql)->fetchAll();
    }
    
    public static function active() {
        $db = Database::getInstance();
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM courses WHERE category_id = c.id AND status = 'published') as course_count
                FROM course_categories c
                ORDER BY c.name ASC";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Create new category
     */
    public static function create($data) {
        $db = Database::getInstance();

        $sql = "INSERT INTO course_categories (
            name, category_description, icon_url, created_at, updated_at
        ) VALUES (
            ?, ?, ?, NOW(), NOW()
        )";

        $params = [
            $data['name'],
            $data['description'] ?? '',
            $data['icon_url'] ?? null
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }

        return false;
    }
    
    /**
     * Update category
     */
    public function update($data) {
        // Map input field names to database column names
        $fieldMapping = [
            'name' => 'name',
            'description' => 'category_description',
            'icon_url' => 'icon_url'
        ];

        $updates = [];
        $params = [];

        foreach ($fieldMapping as $inputField => $dbColumn) {
            if (isset($data[$inputField])) {
                $updates[] = "$dbColumn = ?";
                $params[] = $data[$inputField];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $updates[] = "updated_at = NOW()";
        $params[] = $this->id;

        $sql = "UPDATE course_categories SET " . implode(', ', $updates) . " WHERE id = ?";

        $result = $this->db->query($sql, $params);

        if ($result) {
            $this->load();
        }

        return $result;
    }
    
    /**
     * Delete category
     */
    public function delete() {
        // Check if category has courses
        if ($this->getCourseCount() > 0) {
            return false; // Cannot delete category with courses
        }
        
        $sql = "DELETE FROM course_categories WHERE id = ?";
        return $this->db->query($sql, [$this->id]);
    }
    
    /**
     * Get category courses
     */
    public function getCourses($status = null) {
        require_once __DIR__ . '/Course.php';
        
        $options = ['category_id' => $this->id];
        
        if ($status) {
            $options['status'] = $status;
        }
        
        return Course::all($options);
    }
    
    /**
     * Getters - using actual database column names
     */
    public function getId() { return $this->data['id'] ?? null; }
    public function getName() { return $this->data['name'] ?? ''; }
    public function getDescription() { return $this->data['category_description'] ?? ''; }
    public function getIcon() { return $this->data['icon_url'] ?? null; }
    public function isActive() { return ($this->data['is_active'] ?? 1) == 1; }
    public function getDisplayOrder() { return $this->data['display_order'] ?? 0; }
    public function getCourseCount() { return $this->data['course_count'] ?? 0; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
    /**
     * Get as array
     */
    public function toArray() {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'icon_url' => $this->getIcon(),
            'is_active' => $this->isActive(),
            'display_order' => $this->getDisplayOrder(),
            'course_count' => $this->getCourseCount(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}