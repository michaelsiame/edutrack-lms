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
                (SELECT COUNT(*) FROM courses WHERE category_id = c.id AND status = 'published') as course_count
                FROM categories c
                WHERE c.id = :id";
        
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
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
        $sql = "SELECT id FROM categories WHERE slug = :slug";
        $result = $db->query($sql, ['slug' => $slug])->fetch();
        
        if ($result) {
            return new self($result['id']);
        }
        return null;
    }
    
    /**
     * Get all categories
     */
    public static function all($includeEmpty = false) {
        $db = Database::getInstance();
        
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM courses WHERE category_id = c.id AND status = 'published') as course_count
                FROM categories c";
        
        if (!$includeEmpty) {
            $sql .= " HAVING course_count > 0";
        }
        
        $sql .= " ORDER BY c.order_index ASC, c.name ASC";
        
        return $db->query($sql)->fetchAll();
    }
    
    /**
     * Get active categories (with courses)
     */
    public static function active() {
        return self::all(false);
    }
    
    /**
     * Get popular categories
     */
    public static function popular($limit = 8) {
        $db = Database::getInstance();
        
        $sql = "SELECT c.*,
                COUNT(co.id) as course_count
                FROM categories c
                JOIN courses co ON c.id = co.category_id
                WHERE co.status = 'published'
                GROUP BY c.id
                ORDER BY course_count DESC
                LIMIT :limit";
        
        return $db->query($sql, ['limit' => $limit])->fetchAll();
    }
    
    /**
     * Create category
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        // Generate slug
        if (empty($data['slug'])) {
            $data['slug'] = self::generateSlug($data['name']);
        }
        
        $sql = "INSERT INTO categories (name, slug, description, icon, color, order_index)
                VALUES (:name, :slug, :description, :icon, :color, :order_index)";
        
        $params = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'icon' => $data['icon'] ?? 'fa-folder',
            'color' => $data['color'] ?? '#2E70DA',
            'order_index' => $data['order_index'] ?? 0
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
        $sql = "UPDATE categories SET
                name = :name,
                slug = :slug,
                description = :description,
                icon = :icon,
                color = :color,
                order_index = :order_index,
                updated_at = NOW()
                WHERE id = :id";
        
        $params = array_merge($data, ['id' => $this->id]);
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete category
     */
    public function delete() {
        // Check if category has courses
        if ($this->getCourseCount() > 0) {
            return false; // Cannot delete category with courses
        }
        
        $sql = "DELETE FROM categories WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get category courses
     */
    public function getCourses($filters = []) {
        $filters['category_id'] = $this->id;
        require_once __DIR__ . '/Course.php';
        return Course::all($filters);
    }
    
    /**
     * Generate unique slug
     */
    private static function generateSlug($name) {
        $db = Database::getInstance();
        $slug = slugify($name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT id FROM categories WHERE slug = :slug";
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
    public function getName() { return $this->data['name'] ?? ''; }
    public function getSlug() { return $this->data['slug'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getIcon() { return $this->data['icon'] ?? 'fa-folder'; }
    public function getColor() { return $this->data['color'] ?? '#2E70DA'; }
    public function getOrderIndex() { return $this->data['order_index'] ?? 0; }
    public function getCourseCount() { return $this->data['course_count'] ?? 0; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
    /**
     * Get category URL
     */
    public function getUrl() {
        return url('courses.php?category=' . $this->getSlug());
    }
}