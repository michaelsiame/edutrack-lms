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
                FROM categories c
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
        $sql = "SELECT id FROM categories WHERE slug = ?";
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
                FROM categories c";

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
                FROM categories c
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
                FROM categories c
                ORDER BY c.name ASC";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Create new category
     */
    public static function create($data) {
        $db = Database::getInstance();

        $sql = "INSERT INTO categories (
            name, slug, description, icon, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, NOW(), NOW()
        )";

        $params = [
            $data['name'],
            $data['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['name'])),
            $data['description'] ?? '',
            $data['icon'] ?? 'fa-folder'
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
        $allowed = ['name', 'slug', 'description', 'icon'];

        $updates = [];
        $params = [];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $updates[] = "updated_at = NOW()";
        $params[] = $this->id;

        $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";

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
     * Getters
     */
    public function getId() { return $this->data['id'] ?? null; }
    public function getName() { return $this->data['name'] ?? ''; }
    public function getSlug() { return $this->data['slug'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getIcon() { return $this->data['icon'] ?? 'fa-folder'; }
    public function getColor() { return $this->data['color'] ?? '#2E70DA'; }
    public function isActive() { return $this->data['is_active'] == 1; }
    public function getOrderIndex() { return $this->data['order_index'] ?? 0; }
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
            'slug' => $this->getSlug(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'is_active' => $this->isActive(),
            'order_index' => $this->getOrderIndex(),
            'course_count' => $this->getCourseCount(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}