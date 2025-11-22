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
    private $enrollmentId;
    private $rating;
    private $reviewTitle;
    private $reviewText;
    private $instructorRating;
    private $contentRating;
    private $valueRating;
    private $status;
    private $isFeatured;
    private $helpfulCount;
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
            course_id, user_id, enrollment_id, rating, review_title, review_text,
            instructor_rating, content_rating, value_rating, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $params = [
            $data['course_id'],
            $data['user_id'],
            $enrollment['id'],
            $data['rating'],
            $data['review_title'] ?? null,
            $data['review_text'] ?? null,
            $data['instructor_rating'] ?? null,
            $data['content_rating'] ?? null,
            $data['value_rating'] ?? null,
            $data['status'] ?? 'pending'
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
                       up.avatar
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
                       up.avatar
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
                       up.avatar
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE r.course_id = ?";

        $params = [$courseId];

        // Filter by status
        if (isset($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        } else {
            // Default to approved reviews only
            $sql .= " AND r.status = 'approved'";
        }

        // Filter by rating
        if (isset($filters['rating'])) {
            $sql .= " AND r.rating = ?";
            $params[] = $filters['rating'];
        }

        // Filter by featured
        if (isset($filters['featured']) && $filters['featured']) {
            $sql .= " AND r.is_featured = 1";
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';

        if ($orderBy === 'helpful') {
            $sql .= " ORDER BY r.helpful_count DESC, r.created_at DESC";
        } else if ($orderBy === 'rating') {
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
            'rating_distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            'instructor_rating' => 0,
            'content_rating' => 0,
            'value_rating' => 0
        ];

        // Get overall stats
        $sql = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    AVG(instructor_rating) as instructor_rating,
                    AVG(content_rating) as content_rating,
                    AVG(value_rating) as value_rating
                FROM course_reviews
                WHERE course_id = ? AND status = 'approved'";

        $result = $db->fetchOne($sql, [$courseId]);

        if ($result) {
            $stats['total_reviews'] = (int)$result['total_reviews'];
            $stats['average_rating'] = round((float)$result['average_rating'], 1);
            $stats['instructor_rating'] = round((float)$result['instructor_rating'], 1);
            $stats['content_rating'] = round((float)$result['content_rating'], 1);
            $stats['value_rating'] = round((float)$result['value_rating'], 1);
        }

        // Get rating distribution
        $sql = "SELECT rating, COUNT(*) as count
                FROM course_reviews
                WHERE course_id = ? AND status = 'approved'
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

        if (isset($data['review_title'])) {
            $fields[] = "review_title = ?";
            $params[] = $data['review_title'];
        }

        if (isset($data['review_text'])) {
            $fields[] = "review_text = ?";
            $params[] = $data['review_text'];
        }

        if (isset($data['instructor_rating'])) {
            $fields[] = "instructor_rating = ?";
            $params[] = $data['instructor_rating'];
        }

        if (isset($data['content_rating'])) {
            $fields[] = "content_rating = ?";
            $params[] = $data['content_rating'];
        }

        if (isset($data['value_rating'])) {
            $fields[] = "value_rating = ?";
            $params[] = $data['value_rating'];
        }

        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }

        if (isset($data['is_featured'])) {
            $fields[] = "is_featured = ?";
            $params[] = $data['is_featured'] ? 1 : 0;
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
     * Approve a review
     */
    public function approve() {
        return $this->update(['status' => 'approved']);
    }

    /**
     * Reject a review
     */
    public function reject() {
        return $this->update(['status' => 'rejected']);
    }

    /**
     * Mark as pending
     */
    public function setPending() {
        return $this->update(['status' => 'pending']);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured() {
        return $this->update(['is_featured' => !$this->isFeatured]);
    }

    /**
     * Increment helpful count
     */
    public function markHelpful() {
        $sql = "UPDATE course_reviews SET helpful_count = helpful_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$this->id]);
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
                WHERE course_id = ? AND status = 'approved'";

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
     * Get all pending reviews (for admin)
     */
    public static function getPendingReviews($limit = 50) {
        global $db;

        $sql = "SELECT r.*, u.first_name, u.last_name, c.title as course_title
                FROM course_reviews r
                JOIN users u ON r.user_id = u.id
                JOIN courses c ON r.course_id = c.id
                WHERE r.status = 'pending'
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
        $this->enrollmentId = $data['enrollment_id'] ?? null;
        $this->rating = $data['rating'] ?? null;
        $this->reviewTitle = $data['review_title'] ?? null;
        $this->reviewText = $data['review_text'] ?? null;
        $this->instructorRating = $data['instructor_rating'] ?? null;
        $this->contentRating = $data['content_rating'] ?? null;
        $this->valueRating = $data['value_rating'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->isFeatured = (bool)($data['is_featured'] ?? false);
        $this->helpfulCount = $data['helpful_count'] ?? 0;
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
    public function getEnrollmentId() { return $this->enrollmentId; }
    public function getRating() { return $this->rating; }
    public function getReviewTitle() { return $this->reviewTitle; }
    public function getReviewText() { return $this->reviewText; }
    public function getInstructorRating() { return $this->instructorRating; }
    public function getContentRating() { return $this->contentRating; }
    public function getValueRating() { return $this->valueRating; }
    public function getStatus() { return $this->status; }
    public function isFeatured() { return $this->isFeatured; }
    public function getHelpfulCount() { return $this->helpfulCount; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getUserName() { return $this->userName ?? 'Anonymous'; }
    public function getUserEmail() { return $this->userEmail ?? ''; }
    public function getUserAvatar() { return $this->userAvatar ?? null; }

    public function isApproved() { return $this->status === 'approved'; }
    public function isPending() { return $this->status === 'pending'; }
    public function isRejected() { return $this->status === 'rejected'; }
}
