<?php
/**
 * LessonResource Class
 * Handles downloadable resources for lessons (PDFs, documents, videos, etc.)
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
        $sql = "SELECT * FROM lesson_resources WHERE id = :id";
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
    }

    /**
     * Check if resource exists
     */
    public function exists() {
        return !empty($this->data);
    }

    /**
     * Find resource by ID
     */
    public static function find($id) {
        $resource = new self($id);
        return $resource->exists() ? $resource : null;
    }

    /**
     * Get all resources for a lesson
     */
    public static function getByLesson($lessonId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM lesson_resources
                WHERE lesson_id = :lesson_id
                ORDER BY created_at ASC";
        return $db->query($sql, ['lesson_id' => $lessonId])->fetchAll();
    }

    /**
     * Create new resource
     *
     * @param array $data Resource data including:
     *                    - lesson_id (required)
     *                    - title (required)
     *                    - description (optional)
     *                    - resource_type (required): PDF, Document, Spreadsheet, Presentation, Video, Audio, Archive, Other
     *                    - file_url (required): Path or external URL
     *                    - file_size_kb (optional)
     * @return int|false Resource ID on success, false on failure
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Validation
        if (empty($data['lesson_id']) || empty($data['title']) || empty($data['resource_type']) || empty($data['file_url'])) {
            return false;
        }

        $sql = "INSERT INTO lesson_resources (
            lesson_id, title, description, resource_type, file_url, file_size_kb
        ) VALUES (
            :lesson_id, :title, :description, :resource_type, :file_url, :file_size_kb
        )";

        $params = [
            'lesson_id' => $data['lesson_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'resource_type' => $data['resource_type'],
            'file_url' => $data['file_url'],
            'file_size_kb' => $data['file_size_kb'] ?? null
        ];

        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }

    /**
     * Update resource
     */
    public function update($data) {
        $allowed = ['title', 'description', 'resource_type', 'file_url', 'file_size_kb'];

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

        $sql = "UPDATE lesson_resources SET " . implode(', ', $updates) . " WHERE id = :id";

        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }

    /**
     * Delete resource
     */
    public function delete() {
        // Delete file from server if it's a local upload
        if ($this->isLocalFile()) {
            $filePath = UPLOAD_DIR . '/' . $this->getFileUrl();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $sql = "DELETE FROM lesson_resources WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount() {
        $sql = "UPDATE lesson_resources SET download_count = download_count + 1 WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }

    /**
     * Check if file is hosted locally (not external URL)
     */
    public function isLocalFile() {
        $url = $this->getFileUrl();
        return !filter_var($url, FILTER_VALIDATE_URL) ||
               strpos($url, 'courses/') === 0 ||
               strpos($url, 'uploads/') === 0;
    }

    /**
     * Get full download URL
     */
    public function getDownloadUrl() {
        $fileUrl = $this->getFileUrl();

        // If it's already a full URL, return it
        if (filter_var($fileUrl, FILTER_VALIDATE_URL)) {
            return $fileUrl;
        }

        // Local file - create download link
        return url('api/download-resource.php?id=' . $this->getId());
    }

    /**
     * Get file path on server (for local files)
     */
    public function getFilePath() {
        if (!$this->isLocalFile()) {
            return null;
        }

        return UPLOAD_DIR . '/' . $this->getFileUrl();
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSize() {
        $sizeKb = $this->getFileSizeKb();

        if (!$sizeKb) {
            return 'Unknown';
        }

        if ($sizeKb < 1024) {
            return $sizeKb . ' KB';
        }

        $sizeMb = round($sizeKb / 1024, 2);
        if ($sizeMb < 1024) {
            return $sizeMb . ' MB';
        }

        $sizeGb = round($sizeMb / 1024, 2);
        return $sizeGb . ' GB';
    }

    /**
     * Get icon class based on resource type
     */
    public function getIconClass() {
        $icons = [
            'PDF' => 'fa-file-pdf text-red-600',
            'Document' => 'fa-file-word text-blue-600',
            'Spreadsheet' => 'fa-file-excel text-green-600',
            'Presentation' => 'fa-file-powerpoint text-orange-600',
            'Video' => 'fa-file-video text-purple-600',
            'Audio' => 'fa-file-audio text-indigo-600',
            'Archive' => 'fa-file-archive text-gray-600',
            'Other' => 'fa-file text-gray-500'
        ];

        return $icons[$this->getResourceType()] ?? $icons['Other'];
    }

    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getLessonId() { return $this->data['lesson_id'] ?? null; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getResourceType() { return $this->data['resource_type'] ?? 'Other'; }
    public function getFileUrl() { return $this->data['file_url'] ?? ''; }
    public function getFileSizeKb() { return $this->data['file_size_kb'] ?? 0; }
    public function getDownloadCount() { return $this->data['download_count'] ?? 0; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
}
