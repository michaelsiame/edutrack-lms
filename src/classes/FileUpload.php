<?php
/**
 * FileUpload Class
 * Handles secure file uploads
 */

class FileUpload {
    private $file;
    private $uploadDir;
    private $allowedTypes = [];
    private $maxSize = 10485760; // 10MB default
    private $errors = [];
    
    public function __construct($file, $uploadDir = 'assignments/submissions') {
        $this->file = $file;
        $this->uploadDir = rtrim($uploadDir, '/');
    }
    
    /**
     * Set allowed file types
     */
    public function setAllowedTypes($types) {
        if (is_string($types)) {
            $types = explode(',', $types);
        }
        $this->allowedTypes = array_map('trim', $types);
        return $this;
    }
    
    /**
     * Set maximum file size
     */
    public function setMaxSize($bytes) {
        $this->maxSize = $bytes;
        return $this;
    }
    
    /**
     * Validate file
     */
    public function validate() {
        $this->errors = [];
        
        // Check if file was uploaded
        if (!isset($this->file['error']) || is_array($this->file['error'])) {
            $this->errors[] = 'Invalid file upload';
            return false;
        }
        
        // Check for upload errors
        switch ($this->file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'File size exceeds maximum allowed';
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->errors[] = 'File was only partially uploaded';
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file was uploaded';
                return false;
            default:
                $this->errors[] = 'Unknown upload error';
                return false;
        }
        
        // Check file size
        if ($this->file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed (' . formatFileSize($this->maxSize) . ')';
            return false;
        }
        
        // Check file type
        if (!empty($this->allowedTypes)) {
            $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedTypes)) {
                $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes);
                return false;
            }
        }
        
        // Check MIME type (basic validation)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            $this->errors[] = 'Invalid file type';
            return false;
        }
        
        return true;
    }
    
    /**
     * Upload file
     */
    public function upload() {
        if (!$this->validate()) {
            return false;
        }
        
        // Create upload directory if not exists
        $uploadPath = PUBLIC_PATH . '/uploads/' . $this->uploadDir;
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadPath . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($this->file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $this->uploadDir . '/' . $filename,
                'original_name' => $this->file['name'],
                'size' => $this->file['size'],
                'type' => $this->file['type']
            ];
        }
        
        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }
    
    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error
     */
    public function getError() {
        return !empty($this->errors) ? $this->errors[0] : null;
    }
    
    /**
     * Delete file
     */
    public static function delete($filepath) {
        $fullPath = PUBLIC_PATH . '/uploads/' . $filepath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
    
    /**
     * Get file info
     */
    public static function getFileInfo($filepath) {
        $fullPath = PUBLIC_PATH . '/uploads/' . $filepath;
        
        if (!file_exists($fullPath)) {
            return null;
        }
        
        return [
            'size' => filesize($fullPath),
            'mime_type' => mime_content_type($fullPath),
            'modified' => filemtime($fullPath),
            'exists' => true
        ];
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename) {
        // Remove any path info
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        return $filename;
    }
    
    /**
     * Validate image
     */
    public function validateImage($minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null) {
        if (!$this->validate()) {
            return false;
        }
        
        // Check if it's an image
        $imageInfo = getimagesize($this->file['tmp_name']);
        if ($imageInfo === false) {
            $this->errors[] = 'File is not a valid image';
            return false;
        }
        
        list($width, $height) = $imageInfo;
        
        // Check minimum dimensions
        if ($minWidth && $width < $minWidth) {
            $this->errors[] = "Image width must be at least {$minWidth}px";
            return false;
        }
        
        if ($minHeight && $height < $minHeight) {
            $this->errors[] = "Image height must be at least {$minHeight}px";
            return false;
        }
        
        // Check maximum dimensions
        if ($maxWidth && $width > $maxWidth) {
            $this->errors[] = "Image width must not exceed {$maxWidth}px";
            return false;
        }
        
        if ($maxHeight && $height > $maxHeight) {
            $this->errors[] = "Image height must not exceed {$maxHeight}px";
            return false;
        }
        
        return true;
    }
}