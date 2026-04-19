<?php
/**
 * InstitutionPhoto Class
 * Manages campus/facility photos
 */

require_once __DIR__ . '/../includes/database.php';

class InstitutionPhoto {
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

    private function load() {
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM institution_photos p 
                LEFT JOIN users u ON p.uploaded_by = u.id 
                WHERE p.id = ?";
        $result = $this->db->fetchOne($sql, [$this->id]);
        if ($result) {
            $this->data = $result;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function get($key) {
        return $this->data[$key] ?? null;
    }

    public function getData() {
        return $this->data;
    }

    public function getImageUrl() {
        $path = $this->get('image_path');
        return $path ? '/uploads/institution/' . $path : null;
    }

    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO institution_photos (title, description, category, image_path, 
                is_featured, display_order, uploaded_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['title'],
            $data['description'] ?? null,
            $data['category'] ?? 'campus',
            $data['image_path'],
            $data['is_featured'] ?? 0,
            $data['display_order'] ?? 0,
            $data['uploaded_by']
        ];
        
        $db->query($sql, $params);
        return new self($db->lastInsertId());
    }

    public function update($data) {
        if (!$this->id) return false;
        
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'description', 'category', 'image_path', 'is_featured', 'display_order'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $this->id;
        $sql = "UPDATE institution_photos SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $this->db->query($sql, $params);
        $this->load();
        return true;
    }

    public function delete() {
        if (!$this->id) return false;
        
        // Delete image file
        if ($this->get('image_path')) {
            $path = __DIR__ . '/../../public/uploads/institution/' . $this->get('image_path');
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $this->db->query("DELETE FROM institution_photos WHERE id = ?", [$this->id]);
        return true;
    }

    public static function getAll($filters = []) {
        $db = Database::getInstance();
        
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['featured'])) {
            $where[] = "is_featured = 1";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM institution_photos p 
                LEFT JOIN users u ON p.uploaded_by = u.id 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.display_order ASC, p.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        return $db->fetchAll($sql, $params);
    }

    public static function getByCategory($category, $limit = null) {
        return self::getAll(['category' => $category, 'limit' => $limit]);
    }

    public static function getFeatured($limit = 6) {
        return self::getAll(['featured' => true, 'limit' => $limit]);
    }

    public static function getCategories() {
        return [
            'campus' => 'Campus & Buildings',
            'classroom' => 'Classrooms',
            'lab' => 'Computer Labs',
            'event' => 'Events & Activities',
            'faculty' => 'Faculty & Staff',
            'student_life' => 'Student Life'
        ];
    }
}

/**
 * HeroSlide Class
 * Manages homepage carousel slides
 */
class HeroSlide {
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

    private function load() {
        $sql = "SELECT * FROM hero_slides WHERE id = ?";
        $result = $this->db->fetchOne($sql, [$this->id]);
        if ($result) {
            $this->data = $result;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function get($key) {
        return $this->data[$key] ?? null;
    }

    public function getImageUrl() {
        $path = $this->get('image_path');
        return $path ? '/uploads/hero/' . $path : '/assets/images/hero-default.jpg';
    }

    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO hero_slides (title, subtitle, description, image_path, 
                cta_text, cta_link, secondary_cta_text, secondary_cta_link, 
                is_active, display_order, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['title'],
            $data['subtitle'] ?? null,
            $data['description'] ?? null,
            $data['image_path'],
            $data['cta_text'] ?? 'Get Started',
            $data['cta_link'] ?? 'courses.php',
            $data['secondary_cta_text'] ?? null,
            $data['secondary_cta_link'] ?? null,
            $data['is_active'] ?? 1,
            $data['display_order'] ?? 0,
            $data['created_by']
        ];
        
        $db->query($sql, $params);
        return new self($db->lastInsertId());
    }

    public function update($data) {
        if (!$this->id) return false;
        
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'subtitle', 'description', 'image_path', 
                         'cta_text', 'cta_link', 'secondary_cta_text', 
                         'secondary_cta_link', 'is_active', 'display_order'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $params[] = $this->id;
        $sql = "UPDATE hero_slides SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $this->db->query($sql, $params);
        $this->load();
        return true;
    }

    public function delete() {
        if (!$this->id) return false;
        
        if ($this->get('image_path')) {
            $path = __DIR__ . '/../../public/uploads/hero/' . $this->get('image_path');
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $this->db->query("DELETE FROM hero_slides WHERE id = ?", [$this->id]);
        return true;
    }

    public static function getActive($limit = null) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        return $db->fetchAll($sql);
    }

    public static function getAll($filters = []) {
        $db = Database::getInstance();
        
        $where = ["1=1"];
        $params = [];
        
        if (isset($filters['active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['active'] ? 1 : 0;
        }
        
        $sql = "SELECT h.*, u.first_name, u.last_name 
                FROM hero_slides h 
                LEFT JOIN users u ON h.created_by = u.id 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY h.display_order ASC, h.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        return $db->fetchAll($sql, $params);
    }
}
