<?php
/**
 * Event Class
 * Handles event data operations for the Recent Events feature
 */

require_once __DIR__ . '/../includes/database.php';

class Event {
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
     * Load event data from database
     */
    private function load() {
        $sql = "SELECT e.*, u.first_name, u.last_name, u.email as creator_email 
                FROM events e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE e.id = ?";
        $result = $this->db->fetchOne($sql, [$this->id]);
        if ($result) {
            $this->data = $result;
        }
    }

    /**
     * Get event ID
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get event property
     */
    public function get($key) {
        return $this->data[$key] ?? null;
    }

    /**
     * Get all event data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get formatted event date
     */
    public function getFormattedDate($format = 'F j, Y') {
        $date = $this->get('event_date');
        return $date ? date($format, strtotime($date)) : null;
    }

    /**
     * Get event images
     */
    public function getImages() {
        if (!$this->id) return [];
        $sql = "SELECT * FROM event_images 
                WHERE event_id = ? 
                ORDER BY display_order ASC, created_at ASC";
        return $this->db->fetchAll($sql, [$this->id]);
    }

    /**
     * Get cover image URL
     */
    public function getCoverImageUrl() {
        $cover = $this->get('cover_image');
        if ($cover) {
            return '/uploads/events/' . $cover;
        }
        // Return first image if no cover set
        $images = $this->getImages();
        if (!empty($images)) {
            return '/uploads/events/' . $images[0]['image_path'];
        }
        return null;
    }

    /**
     * Create new event
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        // Generate slug
        $slug = self::generateSlug($data['title']);
        
        $sql = "INSERT INTO events (title, slug, summary, story, event_date, location, 
                cover_image, is_featured, status, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['title'],
            $slug,
            $data['summary'] ?? null,
            $data['story'] ?? null,
            $data['event_date'] ?? null,
            $data['location'] ?? null,
            $data['cover_image'] ?? null,
            $data['is_featured'] ?? 0,
            $data['status'] ?? 'draft',
            $data['created_by']
        ];
        
        $db->query($sql, $params);
        $eventId = $db->lastInsertId();
        
        // Add images if provided
        if (!empty($data['images']) && is_array($data['images'])) {
            self::addImages($eventId, $data['images']);
        }
        
        return new self($eventId);
    }

    /**
     * Update event
     */
    public function update($data) {
        if (!$this->id) return false;
        
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'summary', 'story', 'event_date', 'location', 
                         'cover_image', 'is_featured', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        // Update slug if title changed
        if (isset($data['title'])) {
            $fields[] = "slug = ?";
            $params[] = self::generateSlug($data['title'], $this->id);
        }
        
        $params[] = $this->id;
        $sql = "UPDATE events SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        
        $this->db->query($sql, $params);
        $this->load();
        
        // Add new images if provided
        if (!empty($data['images']) && is_array($data['images'])) {
            self::addImages($this->id, $data['images']);
        }
        
        return true;
    }

    /**
     * Delete event
     */
    public function delete() {
        if (!$this->id) return false;
        
        // Delete images from filesystem
        $images = $this->getImages();
        foreach ($images as $image) {
            $path = __DIR__ . '/../../public/uploads/events/' . $image['image_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        // Delete cover image
        if ($this->get('cover_image')) {
            $path = __DIR__ . '/../../public/uploads/events/' . $this->get('cover_image');
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $this->db->query("DELETE FROM events WHERE id = ?", [$this->id]);
        return true;
    }

    /**
     * Add images to event
     */
    public static function addImages($eventId, $images) {
        $db = Database::getInstance();
        $sql = "INSERT INTO event_images (event_id, image_path, caption, display_order) VALUES (?, ?, ?, ?)";
        
        foreach ($images as $index => $image) {
            $db->query($sql, [
                $eventId,
                $image['path'],
                $image['caption'] ?? null,
                $image['order'] ?? $index
            ]);
        }
    }

    /**
     * Delete specific image
     */
    public static function deleteImage($imageId) {
        $db = Database::getInstance();
        
        // Get image path first
        $image = $db->fetchOne("SELECT * FROM event_images WHERE id = ?", [$imageId]);
        if ($image) {
            $path = __DIR__ . '/../../public/uploads/events/' . $image['image_path'];
            if (file_exists($path)) {
                unlink($path);
            }
            $db->query("DELETE FROM event_images WHERE id = ?", [$imageId]);
            return true;
        }
        return false;
    }

    /**
     * Generate unique slug
     */
    private static function generateSlug($title, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $db = Database::getInstance();
        
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT id FROM events WHERE slug = ?";
            $params = [$slug];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $existing = $db->fetchOne($sql, $params);
            if (!$existing) break;
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Get all events with filters
     */
    public static function getAll($filters = []) {
        $db = Database::getInstance();
        
        try {
            $where = ["1=1"];
            $params = [];
            
            if (!empty($filters['status'])) {
                $where[] = "e.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['featured'])) {
                $where[] = "e.is_featured = 1";
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(e.title LIKE ? OR e.summary LIKE ? OR e.story LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql = "SELECT e.*, u.first_name, u.last_name,
                    (SELECT COUNT(*) FROM event_images WHERE event_id = e.id) as image_count
                    FROM events e 
                    LEFT JOIN users u ON e.created_by = u.id 
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY e.event_date DESC, e.created_at DESC";
            
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . (int)$filters['limit'];
            }
            
            return $db->fetchAll($sql, $params);
        } catch (Throwable $e) {
            error_log("Event::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured events
     */
    public static function getFeatured($limit = 3) {
        return self::getAll(['status' => 'published', 'featured' => true, 'limit' => $limit]);
    }

    /**
     * Get event by slug
     */
    public static function findBySlug($slug) {
        try {
            $db = Database::getInstance();
            $result = $db->fetchOne("SELECT id FROM events WHERE slug = ? AND status = 'published'", [$slug]);
            return $result ? new self($result['id']) : null;
        } catch (Throwable $e) {
            error_log("Event::findBySlug error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get paginated events
     */
    public static function getPaginated($page = 1, $perPage = 9, $filters = []) {
        $db = Database::getInstance();
        
        try {
            $where = ["status = 'published'"];
            $params = [];
            
            if (!empty($filters['search'])) {
                $where[] = "(title LIKE ? OR summary LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM events WHERE " . implode(' AND ', $where);
            $total = $db->fetchColumn($countSql, $params);
            
            // Get page data
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT e.*, 
                    (SELECT COUNT(*) FROM event_images WHERE event_id = e.id) as image_count
                    FROM events e 
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY e.event_date DESC, e.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $events = $db->fetchAll($sql, $params);
            
            return [
                'events' => $events,
                'total' => $total,
                'pages' => ceil($total / $perPage),
                'current_page' => $page
            ];
        } catch (Throwable $e) {
            error_log("Event::getPaginated error: " . $e->getMessage());
            return [
                'events' => [],
                'total' => 0,
                'pages' => 0,
                'current_page' => $page
            ];
        }
    }
}
