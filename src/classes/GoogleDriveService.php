<?php
/**
 * Google Drive Service
 * Handles file uploads to Google Drive and returns shareable links
 */

class GoogleDriveService {
    private $client;
    private $service;
    private $folderId;

    /**
     * Initialize Google Drive service
     *
     * @throws Exception if credentials file not found or invalid
     */
    public function __construct() {
        // Check if Google Drive is enabled
        if (!defined('GOOGLE_DRIVE_ENABLED') || !GOOGLE_DRIVE_ENABLED) {
            throw new Exception('Google Drive integration is not enabled');
        }

        // Path to service account credentials JSON file
        $credentialsPath = BASE_PATH . '/config/google-credentials.json';

        if (!file_exists($credentialsPath)) {
            throw new Exception('Google Drive credentials file not found. Please add google-credentials.json to /config/');
        }

        if (!class_exists('Google_Client')) {
            throw new Exception('Google API client library not installed. Run composer install.');
        }

        // Initialize Google Client
        $this->client = new Google_Client();
        $this->client->setAuthConfig($credentialsPath);
        $this->client->addScope(Google_Service_Drive::DRIVE_FILE);
        $this->client->setApplicationName('EduTrack LMS');

        // Initialize Drive service
        $this->service = new Google_Service_Drive($this->client);

        // Get folder ID from config (or use root)
        $this->folderId = defined('GOOGLE_DRIVE_FOLDER_ID') ? GOOGLE_DRIVE_FOLDER_ID : null;
    }

    /**
     * Upload file to Google Drive
     *
     * @param string $filePath Local file path
     * @param string $fileName Display name for the file
     * @param string $mimeType MIME type of the file
     * @return array ['success' => bool, 'file_id' => string, 'web_view_link' => string, 'error' => string]
     */
    public function uploadFile($filePath, $fileName, $mimeType) {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'File not found: ' . $filePath
                ];
            }

            // Create file metadata
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => $this->folderId ? [$this->folderId] : []
            ]);

            // Upload file
            $content = file_get_contents($filePath);
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink, webContentLink'
            ]);

            // Make file publicly accessible
            $permission = new Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]);

            $this->service->permissions->create($file->id, $permission);

            // Get shareable link
            $fileId = $file->getId();
            $webViewLink = $file->getWebViewLink();
            $webContentLink = $file->getWebContentLink();

            // Use direct download link if available, otherwise use view link
            $shareableLink = $webContentLink ?: $webViewLink;

            return [
                'success' => true,
                'file_id' => $fileId,
                'web_view_link' => $webViewLink,
                'web_content_link' => $webContentLink,
                'shareable_link' => $shareableLink,
                'message' => 'File uploaded to Google Drive successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Google Drive upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete file from Google Drive
     *
     * @param string $fileId Google Drive file ID
     * @return array ['success' => bool, 'error' => string]
     */
    public function deleteFile($fileId) {
        try {
            $this->service->files->delete($fileId);

            return [
                'success' => true,
                'message' => 'File deleted from Google Drive'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to delete file from Google Drive: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get file info from Google Drive
     *
     * @param string $fileId Google Drive file ID
     * @return array|null File information or null if not found
     */
    public function getFileInfo($fileId) {
        try {
            $file = $this->service->files->get($fileId, [
                'fields' => 'id, name, mimeType, size, webViewLink, webContentLink'
            ]);

            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
                'webViewLink' => $file->getWebViewLink(),
                'webContentLink' => $file->getWebContentLink()
            ];

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create folder in Google Drive
     *
     * @param string $folderName Name of the folder
     * @param string|null $parentFolderId Parent folder ID (optional)
     * @return array ['success' => bool, 'folder_id' => string, 'error' => string]
     */
    public function createFolder($folderName, $parentFolderId = null) {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => $parentFolderId ? [$parentFolderId] : ($this->folderId ? [$this->folderId] : [])
            ]);

            $folder = $this->service->files->create($fileMetadata, [
                'fields' => 'id'
            ]);

            return [
                'success' => true,
                'folder_id' => $folder->getId(),
                'message' => 'Folder created successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if Google Drive is properly configured
     *
     * @return array ['configured' => bool, 'error' => string]
     */
    public static function isConfigured() {
        try {
            if (!defined('GOOGLE_DRIVE_ENABLED') || !GOOGLE_DRIVE_ENABLED) {
                return [
                    'configured' => false,
                    'error' => 'Google Drive integration is disabled in config'
                ];
            }

            $credentialsPath = BASE_PATH . '/config/google-credentials.json';

            if (!file_exists($credentialsPath)) {
                return [
                    'configured' => false,
                    'error' => 'Google credentials file not found'
                ];
            }

            // Try to read credentials
            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (!$credentials || !isset($credentials['type'])) {
                return [
                    'configured' => false,
                    'error' => 'Invalid credentials file format'
                ];
            }

            return [
                'configured' => true,
                'message' => 'Google Drive is properly configured'
            ];

        } catch (Exception $e) {
            return [
                'configured' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
