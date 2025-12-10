<?php
/**
 * Edutrack Computer Training College
 * User Class - Normalized for Database Structure
 */

class User {
    
    private $db;
    private $id;
    private $data = [];
    private $profile = [];
    
    /**
     * Constructor
     */
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        
        if ($id) {
            $this->id = $id;
            $this->load();
        }
    }
    
    /**
     * Load user data from both tables
     */
    private function load() {
        // Load core credentials
        $this->data = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$this->id]);
        
        // Load extended profile
        if ($this->data) {
            $this->profile = $this->db->fetchOne("SELECT * FROM user_profiles WHERE user_id = ?", [$this->id]);
            // Ensure profile is an array even if empty
            if (!$this->profile) {
                $this->profile = [];
            }
        }
    }
    
    /**
     * Get user by ID
     */
    public static function find($id) {
        return new self($id);
    }
    
    /**
     * Get user by email
     */
    public static function findByEmail($email) {
        $db = Database::getInstance();
        $userData = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        
        if ($userData) {
            return new self($userData['id']);
        }
        
        return null;
    }
    
    /**
     * Get current logged in user
     */
    public static function current() {
        if (!isLoggedIn()) {
            return null;
        }
        
        return self::find(currentUserId());
    }
    
    /**
     * Create new user
     */
    public static function create($data) {
        // This relies on your global registerUser function which handles the INSERTs
        $result = registerUser($data);
        
        if ($result['success']) {
            return self::find($result['user_id']);
        }
        
        return null;
    }
    
    /**
     * Magic getter to access properties from both tables transparently
     */
    public function __get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        if (isset($this->profile[$key])) {
            return $this->profile[$key];
        }
        
        return null;
    }
    
    /**
     * Check if user exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    // --- Basic Getters ---

    public function getId() {
        return $this->id;
    }
    
    public function getFullName() {
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }

    public function getEmail() {
        return $this->data['email'] ?? null;
    }

    public function getFirstName() {
        return $this->data['first_name'] ?? '';
    }

    public function getLastName() {
        return $this->data['last_name'] ?? '';
    }
    
    /**
     * Get avatar URL
     */
    public function getAvatarUrl() {
        $avatarFile = $this->profile['avatar'] ?? null;
        
        // Check if custom avatar exists
        if ($avatarFile && defined('UPLOAD_PATH') && file_exists(UPLOAD_PATH . '/users/avatars/' . $avatarFile)) {
            return UPLOAD_URL . '/users/avatars/' . $avatarFile;
        }
        
        // Fallback to Gravatar
        $hash = md5(strtolower(trim($this->getEmail())));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }
    
    /**
     * Get role
     * Optimized to query the new user_roles table
     */
    public function getRole() {
        // Return cached role if available to save queries
        if (isset($this->data['_cached_role'])) {
            return $this->data['_cached_role'];
        }

        // Query user_roles table
        $roleData = $this->db->fetchOne("
            SELECT r.role_name
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = ?
            LIMIT 1
        ", [$this->id]);

        if ($roleData) {
            $roleName = strtolower($roleData['role_name']);
            // Normalize role names for code checks
            if (strpos($roleName, 'admin') !== false || strpos($roleName, 'super') !== false) {
                $role = 'admin';
            } elseif (strpos($roleName, 'instructor') !== false) {
                $role = 'instructor';
            } elseif (strpos($roleName, 'finance') !== false) {
                $role = 'finance';
            } else {
                $role = 'student';
            }
        } else {
            $role = 'student'; // Default fallback
        }

        $this->data['_cached_role'] = $role;
        return $role;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        $userRole = $this->getRole();
        if (is_array($role)) {
            return in_array($userRole, $role);
        }
        return $userRole === $role;
    }
    
    public function isEmailVerified() {
        return (bool) ($this->data['email_verified'] ?? false);
    }
    
    public function isActive() {
        return ($this->data['status'] ?? '') === 'active';
    }
    
    /**
     * Update user data
     * Handles splitting data between 'users' and 'user_profiles' tables correctly
     */
    public function update($data) {
        $userData = [];
        $profileData = [];
        
        // 1. Fields for 'users' table
        $userFields = ['first_name', 'last_name', 'phone', 'status']; 
        foreach ($userFields as $field) {
            if (isset($data[$field])) {
                $userData[$field] = $data[$field];
            }
        }
        
        // 2. Fields for 'user_profiles' table (Expanded for new schema)
        $profileFields = [
            'bio', 'date_of_birth', 'gender', 'address', 'city', 'province', 
            'country', 'postal_code', 'nrc_number', 'education_level', 'occupation', 
            'linkedin_url', 'facebook_url', 'twitter_url', 'avatar'
        ];
        
        // Note: phone is redundant in schema, sync it to both if present
        if (isset($data['phone'])) {
            $profileData['phone'] = $data['phone'];
        }

        foreach ($profileFields as $field) {
            if (isset($data[$field])) {
                $profileData[$field] = $data[$field];
            }
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update users table
            if (!empty($userData)) {
                $this->db->update('users', $userData, 'id = ?', [$this->id]);
            }
            
            // Update user_profiles table
            if (!empty($profileData)) {
                // Check if profile exists first
                $profileExists = $this->db->fetchColumn("SELECT id FROM user_profiles WHERE user_id = ?", [$this->id]);
                
                if ($profileExists) {
                    $this->db->update('user_profiles', $profileData, 'user_id = ?', [$this->id]);
                } else {
                    $profileData['user_id'] = $this->id;
                    $this->db->insert('user_profiles', $profileData);
                }
            }
            
            $this->db->commit();
            $this->load(); // Reload object data from DB
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update password
     */
    public function updatePassword($newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('users', ['password_hash' => $passwordHash], 'id = ?', [$this->id]);
    }
    
    /**
     * Upload avatar
     */
    public function uploadAvatar($file) {
        if (!defined('UPLOAD_PATH')) {
            return ['success' => false, 'message' => 'Upload path not configured'];
        }

        $uploadDir = UPLOAD_PATH . '/users/avatars/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Basic Validation
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowed)) {
            return ['success' => false, 'message' => 'Invalid file type.'];
        }
        
        // Generate filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $this->id . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old avatar
            $this->deleteAvatarFile();
            
            // Update database via the robust update method
            $this->update(['avatar' => $filename]);
            
            // Update session if current user
            if (function_exists('currentUserId') && currentUserId() === $this->id) {
                $_SESSION['user_avatar'] = $filename;
            }
            
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    
    /**
     * Delete avatar
     */
    public function deleteAvatar() {
        $this->deleteAvatarFile();
        $success = $this->update(['avatar' => null]);
        
        if (function_exists('currentUserId') && currentUserId() === $this->id) {
            $_SESSION['user_avatar'] = null;
        }
        
        return $success;
    }

    /**
     * Helper to physically remove file
     */
    private function deleteAvatarFile() {
        $oldAvatar = $this->profile['avatar'] ?? null;
        if ($oldAvatar && defined('UPLOAD_PATH') && file_exists(UPLOAD_PATH . '/users/avatars/' . $oldAvatar)) {
            @unlink(UPLOAD_PATH . '/users/avatars/' . $oldAvatar);
        }
    }
    
    /**
     * Get user enrollments
     * Updated to fetch balance from payment plans
     */
    public function getEnrollments($status = null) {
        $sql = "SELECT e.*, c.title, c.thumbnail_url, c.slug, 
                       ep.balance, ep.payment_status as financial_status, ep.total_paid, ep.total_fee
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                LEFT JOIN enrollment_payment_plans ep ON e.id = ep.enrollment_id
                WHERE e.user_id = ?";
        $params = [$this->id];
        
        if ($status) {
            $sql .= " AND e.enrollment_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.enrolled_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getActiveEnrollmentsCount() {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'In Progress'",
            [$this->id]
        );
    }
    
    public function getCompletedCoursesCount() {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'Completed'",
            [$this->id]
        );
    }
    
    public function getCertificates() {
        return $this->db->fetchAll(
            "SELECT c.*, co.title as course_title, co.slug as course_slug 
             FROM certificates c 
             JOIN courses co ON c.course_id = co.id 
             WHERE c.enrollment_id IN (SELECT id FROM enrollments WHERE user_id = ?) 
             ORDER BY c.issued_date DESC",
            [$this->id]
        );
    }
    
    public function isEnrolledIn($courseId) {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ? AND enrollment_status != 'Dropped'",
            [$this->id, $courseId]
        ) > 0;
    }
    
    public function getTotalTimeSpent() {
        return (int) $this->db->fetchColumn(
            "SELECT SUM(total_time_spent) FROM enrollments WHERE user_id = ?",
            [$this->id]
        );
    }
    
    public function getRecentActivity($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM activity_logs 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$this->id, $limit]
        );
    }
    
    public function deactivate() {
        return $this->update(['status' => 'inactive']);
    }
    
    public function activate() {
        return $this->update(['status' => 'active']);
    }
    
    public function suspend() {
        return $this->update(['status' => 'suspended']);
    }
    
    public function delete() {
        return $this->deactivate(); // Prefer soft delete
    }
    
    /**
     * Get all users (Admin)
     */
    public static function all($role = null, $limit = 100, $offset = 0) {
        $db = Database::getInstance();

        $sql = "SELECT u.*, up.avatar_url, up.city, up.province
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id";
        $params = [];

        if ($role) {
            $rolePattern = '%' . $role . '%';
            $sql .= " INNER JOIN user_roles ur ON u.id = ur.user_id
                      INNER JOIN roles r ON ur.role_id = r.id
                      WHERE r.role_name LIKE ?";
            $params[] = $rolePattern;
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $db->fetchAll($sql, $params);
    }

    public static function count($role = null) {
        $db = Database::getInstance();

        if ($role) {
            $rolePattern = '%' . $role . '%';
            return $db->fetchColumn(
                "SELECT COUNT(DISTINCT u.id)
                 FROM users u
                 INNER JOIN user_roles ur ON u.id = ur.user_id
                 INNER JOIN roles r ON ur.role_id = r.id
                 WHERE r.role_name LIKE ?",
                [$rolePattern]
            );
        }

        return $db->fetchColumn("SELECT COUNT(*) FROM users");
    }

    public static function search($query, $role = null) {
        $db = Database::getInstance();

        $sql = "SELECT u.*, up.avatar_url, up.city
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%{$query}%";
        $params = [$searchTerm, $searchTerm, $searchTerm];

        if ($role) {
            $rolePattern = '%' . $role . '%';
            $sql .= " AND u.id IN (
                SELECT ur.user_id FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE r.role_name LIKE ?
            )";
            $params[] = $rolePattern;
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT 50";

        return $db->fetchAll($sql, $params);
    }
    
    public function toArray() {
        return array_merge($this->data, $this->profile);
    }
}