<?php
// src/classes/LessonResource.php

class LessonResource {
    public $id;
    public $title;
    public $description;
    public $resource_type;
    public $file_url;
    public $file_size_kb;
    public $download_count;

    // Fetch all resources linked to a specific lesson
    public static function getByLesson($lessonId) {
        global $db;
        return $db->fetchAll("
            SELECT * FROM lesson_resources 
            WHERE lesson_id = ? 
            ORDER BY created_at DESC
        ", [$lessonId]);
    }

    // Find a single resource
    public static function find($id) {
        global $db;
        $data = $db->fetchOne("SELECT * FROM lesson_resources WHERE id = ?", [$id]);
        if ($data) {
            $resource = new self();
            foreach ($data as $key => $value) {
                $resource->$key = $value;
            }
            return $resource;
        }
        return null;
    }

    // Helper to pick the right FontAwesome icon based on file type
    public function getIconClass() {
        switch (strtolower($this->resource_type)) {
            case 'pdf': return 'fa-file-pdf text-red-600';
            case 'document': return 'fa-file-word text-blue-600';
            case 'spreadsheet': return 'fa-file-excel text-green-600';
            case 'presentation': return 'fa-file-powerpoint text-orange-600';
            case 'video': return 'fa-file-video text-purple-600';
            case 'archive': return 'fa-file-archive text-yellow-600';
            default: return 'fa-file text-gray-600';
        }
    }

    // Helper for file size
    public function getFormattedFileSize() {
        if (!$this->file_size_kb) return 'Unknown';
        if ($this->file_size_kb < 1024) return $this->file_size_kb . ' KB';
        return round($this->file_size_kb / 1024, 2) . ' MB';
    }

    // Returns the URL (Google Drive link)
    public function getDownloadUrl() {
        return $this->file_url;
    }
}