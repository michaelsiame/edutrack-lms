<?php
/**
 * Edutrack computer training college
 * User Class
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
     * Load user data
     */
    private function load() {
        $this->data = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$this->id]);
        
        if ($this->data) {
            $this->profile = $this->db->fetchOne("SELECT * FROM user_profiles WHERE user_id = ?", [$this->id]);
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
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user) {
            $instance = new self();
            $instance->id = $user['id'];
            $instance->data = $user;
            $instance->profile = $db->fetchOne("SELECT * FROM user_profiles WHERE user_id = ?", [$user['id']]);
            return $instance;
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
        $result = registerUser($data);
        
        if ($result['success']) {
            return self::find($result['user_id']);
        }
        
        return null;
    }
    
    /**
     * Get user property
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
    
    /**
     * Get user ID
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get full name
     */
    public function getFullName() {
        return trim($this->data['first_name'] . ' ' . $this->data['last_name']);
    }
    
    /**
     * Get avatar URL
     */
    public function getAvatarUrl() {
        return userAvatar($this->profile['avatar'] ?? null, $this->data['email']);
    }
    
    /**
     * Get role
     */
    public function getRole() {
        return $this->data['role'] ?? 'student';
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($role) {
        if (is_array($role)) {
            return in_array($this->getRole(), $role);
        }
        return $this->getRole() === $role;
    }
    
    /**
     * Check if email is verified
     */
    public function isEmailVerified() {
        return (bool) ($this->data['email_verified'] ?? false);
    }
    
    /**
     * Check if account is active
     */
    public function isActive() {
        return $this->data['status'] === 'active';
    }
    
    /**
     * Update user data
     */
    public function update($data) {
        $userData = [];
        $profileData = [];
        
        // User table fields
        $userFields = ['email', 'first_name', 'last_name', 'phone', 'role', 'status'];
        foreach ($userFields as $field) {
            if (isset($data[$field])) {
                $userData[$field] = $data[$field];
            }
        }
        
        // Profile table fields
        $profileFields = ['bio', 'date_of_birth', 'gender', 'address', 'city', 'province', 
                         'country', 'nrc_number', 'education_level', 'occupation', 
                         'linkedin_url', 'facebook_url', 'twitter_url', 'avatar'];
        foreach ($profileFields as $field) {
            if (isset($data[$field])) {
                $profileData[$field] = $data[$field];
            }
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update user
            if (!empty($userData)) {
                $this->db->update('users', $userData, 'id = ?', [$this->id]);
            }
            
            // Update profile
            if (!empty($profileData)) {
                $this->db->update('user_profiles', $profileData, 'user_id = ?', [$this->id]);
            }
            
            $this->db->commit();
            $this->load(); // Reload data
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            logActivity("User update error: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Update password
     */
    public function updatePassword($newPassword) {
        $passwordHash = hashPassword($newPassword);
        return $this->db->update('users', ['password_hash' => $passwordHash], 'id = ?', [$this->id]) > 0;
    }
    
    /**
     * Upload avatar
     */
    public function uploadAvatar($file) {
        $uploadDir = UPLOAD_PATH . '/users/avatars/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validate file
        $validation = validateFileUpload($file, config('upload.allowed_images'));
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['error']];
        }
        
        // Generate filename
        $filename = cleanFilename($file['name']);
        $targetPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old avatar if exists
            if ($this->profile['avatar'] && file_exists($uploadDir . $this->profile['avatar'])) {
                unlink($uploadDir . $this->profile['avatar']);
            }
            
            // Update database
            $this->db->update('user_profiles', ['avatar' => $filename], 'user_id = ?', [$this->id]);
            $this->load();
            
            // Update session
            if (currentUserId() === $this->id) {
                $_SESSION['user_avatar'] = $filename;
            }
            
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
    
    /**
     * Delete avatar
     */
    public function deleteAvatar() {
        $uploadDir = UPLOAD_PATH . '/users/avatars/';
        
        if ($this->profile['avatar'] && file_exists($uploadDir . $this->profile['avatar'])) {
            unlink($uploadDir . $this->profile['avatar']);
        }
        
        $this->db->update('user_profiles', ['avatar' => null], 'user_id = ?', [$this->id]);
        $this->load();
        
        // Update session
        if (currentUserId() === $this->id) {
            $_SESSION['user_avatar'] = null;
        }
        
        return true;
    }
    
    /**
     * Get user enrollments
     */
    public function getEnrollments($status = null) {
        $sql = "SELECT e.*, c.title, c.thumbnail, c.slug 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE e.user_id = ?";
        $params = [$this->id];
        
        if ($status) {
            $sql .= " AND e.enrollment_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.enrolled_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get active enrollments count
     */
    public function getActiveEnrollmentsCount() {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'active'",
            [$this->id]
        );
    }
    
    /**
     * Get completed courses count
     */
    public function getCompletedCoursesCount() {
        return $this->db->fetchColumn(
            "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND enrollment_status = 'completed'",
            [$this->id]
        );
    }
    
    /**
     * Get certificates
     */
    public function getCertificates() {
        return $this->db->fetchAll(
            "SELECT c.*, co.title as course_title, co.slug as course_slug 
             FROM certificates c 
             JOIN courses co ON c.course_id = co.id 
             WHERE c.user_id = ? 
             ORDER BY c.issue_date DESC",
            [$this->id]
        );
    }
    
    /**
     * Check if enrolled in course
     */
    public function isEnrolledIn($courseId) {
        return $this->db->exists(
            'enrollments',
            'user_id = ? AND course_id = ?',
            [$this->id, $courseId]
        );
    }
    
    /**
     * Get total time spent learning (minutes)
     */
    public function getTotalTimeSpent() {
        return (int) $this->db->fetchColumn(
            "SELECT SUM(total_time_spent) FROM enrollments WHERE user_id = ?",
            [$this->id]
        );
    }
    
    /**
     * Get recent activity
     */
    public function getRecentActivity($limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM activity_logs 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$this->id, $limit]
        );
    }
    
    /**
     * Deactivate account
     */
    public function deactivate() {
        return $this->db->update('users', ['status' => 'inactive'], 'id = ?', [$this->id]) > 0;
    }
    
    /**
     * Activate account
     */
    public function activate() {
        return $this->db->update('users', ['status' => 'active'], 'id = ?', [$this->id]) > 0;
    }
    
    /**
     * Suspend account
     */
    public function suspend() {
        return $this->db->update('users', ['status' => 'suspended'], 'id = ?', [$this->id]) > 0;
    }
    
    /**
     * Delete account (soft delete)
     */
    public function delete() {
        return $this->deactivate();
    }
    
    /**
     * Get all users
     */
    public static function all($role = null, $limit = 100, $offset = 0) {
        $db = Database::getInstance();
        
        $sql = "SELECT u.*, up.avatar, up.city, up.province 
                FROM users u 
                LEFT JOIN user_profiles up ON u.id = up.user_id";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE u.role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Count users
     */
    public static function count($role = null) {
        $db = Database::getInstance();
        
        if ($role) {
            return $db->count('users', 'role = ?', [$role]);
        }
        
        return $db->count('users');
    }
    
    /**
     * Search users
     */
    public static function search($query, $role = null) {
        $db = Database::getInstance();
        
        $sql = "SELECT u.*, up.avatar, up.city 
                FROM users u 
                LEFT JOIN user_profiles up ON u.id = up.user_id 
                WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%{$query}%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        
        if ($role) {
            $sql .= " AND u.role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY u.created_at DESC LIMIT 50";
        
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Get user data as array
     */
    public function toArray() {
        return array_merge($this->data, $this->profile);
    }
}