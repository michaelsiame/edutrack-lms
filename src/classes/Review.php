<?php
/**
 * Review Class
 * Handles course reviews and ratings
 */

class Review {
    private $db;
    private $id;
    private $courseId;
    private $userId;
    private $rating;
    private $reviewText;
    private $createdAt;
    private $updatedAt;
    private $userName;
    private $userEmail;
    private $userAvatar;

    public function __construct($db = null) {
        if ($db === null) {
            $this->db = Database::getInstance();
        } else {
            $this->db = $db;
        }
    }

    /**
     * Create a new review
     */
    public static function create($data) {
        global $db;

        // Validate required fields
        if (empty($data['course_id']) || empty($data['user_id']) || empty($data['rating'])) {
            throw new Exception('Course ID, User ID, and rating are required');
        }

        // Check if user is enrolled
        $enrollment = $db->fetchOne(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?",
            [$data['user_id'], $data['course_id']]
        );

        if (!$enrollment) {
            throw new Exception('User must be enrolled in the course to leave a review');
        }

        // Check if review already exists
        $existing = $db->fetchOne(
            "SELECT id FROM course_reviews WHERE user_id = ? AND course_id = ?",
            [$data['user_id'], $data['course_id']]
        );

        if ($existing) {
            throw new Exception('You have already reviewed this course. Please update your existing review.');
        }

        // Validate rating
        if ($data['rating'] < 1 || $data['rating'] > 5) {
            throw new Exception('Rating must be between 1 and 5');
        }

        $sql = "INSERT INTO course_reviews (
            course_id, user_id, rating, review, created_at
        ) VALUES (?, ?, ?, ?, NOW())";

        $params = [
            $data['course_id'],
            $data['user_id'],
            $data['rating'],
            $data['review'] ?? null
        ];

        $result = $db->query($sql, $params);

        if ($result) {
            $reviewId = $db->lastInsertId();

            // Update course rating statistics
            self::updateCourseRatings($data['course_id']);

            return self::find($reviewId);
        }

        return false;
    }

    /**
     * Find review by ID
     */
    public static function find($id) {
        global $db;

        $sql = "SELECT r.*, u.first_name, u.last_name, u.email,
                       up.avatar_url as avatar
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE r.id = ?";

        $data = $db->fetchOne($sql, [$id]);

        if ($data) {
            $review = new self();
            $review->hydrate($data);
            return $review;
        }

        return null;
    }

    /**
     * Get user's review for a specific course
     */
    public static function getUserReview($userId, $courseId) {
        global $db;

        $sql = "SELECT r.*, u.first_name, u.last_name, u.email,
                       up.avatar_url as avatar
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE r.user_id = ? AND r.course_id = ?";

        $data = $db->fetchOne($sql, [$userId, $courseId]);

        if ($data) {
            $review = new self();
            $review->hydrate($data);
            return $review;
        }

        return null;
    }

    /**
     * Get all reviews for a course
     */
    public static function getCourseReviews($courseId, $filters = []) {
        global $db;

        $sql = "SELECT r.*, u.first_name, u.last_name, u.email,
                       up.avatar_url as avatar
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE r.course_id = ?";

        $params = [$courseId];

        // Filter by rating
        if (isset($filters['rating'])) {
            $sql .= " AND r.rating = ?";
            $params[] = $filters['rating'];
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';

        if ($orderBy === 'rating') {
            $sql .= " ORDER BY r.rating $orderDir, r.created_at DESC";
        } else {
            $sql .= " ORDER BY r.created_at $orderDir";
        }

        // Limit
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        $results = $db->fetchAll($sql, $params);

        $reviews = [];
        foreach ($results as $data) {
            $review = new self();
            $review->hydrate($data);
            $reviews[] = $review;
        }

        return $reviews;
    }

    /**
     * Get review statistics for a course
     */
    public static function getCourseStats($courseId) {
        global $db;

        $stats = [
            'total_reviews' => 0,
            'average_rating' => 0,
            'rating_distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]
        ];

        // Get overall stats
        $sql = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating
                FROM course_reviews
                WHERE course_id = ?";

        $result = $db->fetchOne($sql, [$courseId]);

        if ($result) {
            $stats['total_reviews'] = (int)$result['total_reviews'];
            $stats['average_rating'] = round((float)$result['average_rating'], 1);
        }

        // Get rating distribution
        $sql = "SELECT rating, COUNT(*) as count
                FROM course_reviews
                WHERE course_id = ?
                GROUP BY rating";

        $distribution = $db->fetchAll($sql, [$courseId]);

        foreach ($distribution as $row) {
            $stats['rating_distribution'][(int)$row['rating']] = (int)$row['count'];
        }

        return $stats;
    }

    /**
     * Update a review
     */
    public function update($data) {
        $fields = [];
        $params = [];

        if (isset($data['rating'])) {
            if ($data['rating'] < 1 || $data['rating'] > 5) {
                throw new Exception('Rating must be between 1 and 5');
            }
            $fields[] = "rating = ?";
            $params[] = $data['rating'];
        }

        if (isset($data['review'])) {
            $fields[] = "review = ?";
            $params[] = $data['review'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $params[] = $this->id;

        $sql = "UPDATE course_reviews SET " . implode(', ', $fields) . " WHERE id = ?";

        $result = $this->db->query($sql, $params);

        if ($result) {
            // Update course rating statistics
            self::updateCourseRatings($this->courseId);
            return true;
        }

        return false;
    }

    /**
     * Delete a review
     */
    public function delete() {
        $result = $this->db->query("DELETE FROM course_reviews WHERE id = ?", [$this->id]);

        if ($result) {
            // Update course rating statistics
            self::updateCourseRatings($this->courseId);
            return true;
        }

        return false;
    }

    /**
     * Update course rating statistics
     */
    private static function updateCourseRatings($courseId) {
        global $db;

        $sql = "SELECT
                    AVG(rating) as avg_rating,
                    COUNT(*) as rating_count
                FROM course_reviews
                WHERE course_id = ?";

        $stats = $db->fetchOne($sql, [$courseId]);

        if ($stats) {
            $db->query(
                "UPDATE courses SET rating_average = ?, rating_count = ? WHERE id = ?",
                [
                    round((float)$stats['avg_rating'], 2),
                    (int)$stats['rating_count'],
                    $courseId
                ]
            );
        }
    }

    /**
     * Get all reviews (for admin)
     */
    public static function getAllReviews($limit = 50) {
        global $db;

        $sql = "SELECT r.*, u.first_name, u.last_name, c.title as course_title
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                JOIN courses c ON r.course_id = c.id
                ORDER BY r.created_at DESC
                LIMIT ?";

        $results = $db->fetchAll($sql, [$limit]);

        $reviews = [];
        foreach ($results as $data) {
            $review = new self();
            $review->hydrate($data);
            $reviews[] = $review;
        }

        return $reviews;
    }

    /**
     * Hydrate object from database row
     */
    private function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->courseId = $data['course_id'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->rating = $data['rating'] ?? null;
        $this->reviewText = $data['review'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;

        // Additional user data if available
        if (isset($data['first_name'])) {
            $this->userName = $data['first_name'] . ' ' . $data['last_name'];
            $this->userEmail = $data['email'] ?? null;
            $this->userAvatar = $data['avatar'] ?? null;
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCourseId() { return $this->courseId; }
    public function getUserId() { return $this->userId; }
    public function getRating() { return $this->rating; }
    public function getReviewTitle() { return null; }
    public function getReviewText() { return $this->reviewText; }
    public function getHelpfulCount() { return 0; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getUserName() { return $this->userName ?? 'Anonymous'; }
    public function getUserEmail() { return $this->userEmail ?? ''; }
    public function getUserAvatar() { return $this->userAvatar ?? null; }
    public function isFeatured() { return false; }
}
