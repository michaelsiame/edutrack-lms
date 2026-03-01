<?php
/**
 * LessonResource Class
 * Handles lesson resources and file attachments
 */

class LessonResource {
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
     * Load resource data
     */
    private function load() {
        $result = $this->db->fetchOne("SELECT * FROM lesson_resources WHERE id = ?", [$this->id]);
        if ($result) {
            $this->data = $result;
        }
    }

    /**
     * Find resource by ID
     */
    public static function find($id) {
        $resource = new self($id);
        return !empty($resource->data) ? $resource : null;
    }

    /**
     * Get all resources for a lesson
     */
    public static function getByLesson($lessonId) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM lesson_resources WHERE lesson_id = ? ORDER BY created_at DESC",
            [$lessonId]
        );
    }

    /**
     * Create new resource
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        // Map file_path to file_url for database compatibility
        $fileUrl = $data['file_path'] ?? $data['file_url'] ?? '';
        $fileSizeKb = isset($data['file_size']) ? round($data['file_size'] / 1024) : ($data['file_size_kb'] ?? 0);
        
        $sql = "INSERT INTO lesson_resources (
            lesson_id, title, description, resource_type, file_url, 
            file_size_kb, download_count, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['lesson_id'] ?? null,
            $data['title'] ?? '',
            $data['description'] ?? '',
            $data['resource_type'] ?? 'Other',
            $fileUrl,
            $fileSizeKb,
            $data['download_count'] ?? 0
        ];
        
        $db->query($sql, $params);
        return $db->lastInsertId();
    }

    /**
     * Update resource
     */
    public function update($data) {
        $fields = [];
        $params = ['id' => $this->id];
        
        $fieldMap = [
            'title' => 'title',
            'description' => 'description',
            'resource_type' => 'resource_type',
            'file_path' => 'file_url',
            'file_url' => 'file_url',
            'file_size' => 'file_size_kb',
            'file_size_kb' => 'file_size_kb',
            'download_count' => 'download_count'
        ];
        
        foreach ($fieldMap as $inputField => $dbField) {
            if (isset($data[$inputField])) {
                $fields[] = "$dbField = :$dbField";
                
                // Convert file_size to KB if needed
                if ($inputField === 'file_size' && is_numeric($data[$inputField])) {
                    $params[$dbField] = round($data[$inputField] / 1024);
                } else {
                    $params[$dbField] = $data[$inputField];
                }
            }
        }
        
        if (empty($fields)) return false;
        
        $sql = "UPDATE lesson_resources SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($sql, $params);
        $this->load();
        return true;
    }

    /**
     * Delete resource
     */
    public function delete() {
        // Delete file if exists
        if (!empty($this->data['file_url'])) {
            FileUpload::delete($this->data['file_url']);
        }
        
        return $this->db->query("DELETE FROM lesson_resources WHERE id = ?", [$this->id]);
    }

    /**
     * Increment download count
     */
    public function incrementDownloads() {
        return $this->db->query(
            "UPDATE lesson_resources SET download_count = download_count + 1 WHERE id = ?",
            [$this->id]
        );
    }

    /**
     * Get icon class based on resource type
     */
    public function getIconClass() {
        $type = strtoupper($this->data['resource_type'] ?? 'Other');
        
        $icons = [
            'PDF' => 'fa-file-pdf text-red-600',
            'DOCUMENT' => 'fa-file-word text-blue-600',
            'SPREADSHEET' => 'fa-file-excel text-green-600',
            'PRESENTATION' => 'fa-file-powerpoint text-orange-600',
            'VIDEO' => 'fa-file-video text-purple-600',
            'AUDIO' => 'fa-file-audio text-indigo-600',
            'ARCHIVE' => 'fa-file-archive text-yellow-600',
            'IMAGE' => 'fa-file-image text-pink-600',
            'OTHER' => 'fa-file text-gray-600'
        ];
        
        return $icons[$type] ?? 'fa-file text-gray-600';
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize() {
        $sizeKb = $this->data['file_size_kb'] ?? 0;
        
        if ($sizeKb === 0) return 'Unknown';
        if ($sizeKb < 1024) return $sizeKb . ' KB';
        return round($sizeKb / 1024, 2) . ' MB';
    }

    /**
     * Get download URL
     */
    public function getDownloadUrl() {
        if (!empty($this->data['file_url'])) {
            return url('uploads/' . $this->data['file_url']);
        }
        return '';
    }

    /**
     * Magic getter
     */
    public function __get($name) {
        // Map file_path to file_url for compatibility
        if ($name === 'file_path') {
            return $this->data['file_url'] ?? '';
        }
        return $this->data[$name] ?? null;
    }

    /**
     * Get all data as array
     */
    public function toArray() {
        return $this->data;
    }
}
