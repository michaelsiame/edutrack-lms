<?php
/**
 * Quiz Class
 * Handles quizzes and assessments
 */

class Quiz {
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
     * Load quiz data
     */
    private function load() {
        $sql = "SELECT q.*, l.title as lesson_title, m.course_id,
                c.title as course_title
                FROM quizzes q
                LEFT JOIN lessons l ON q.lesson_id = l.id
                LEFT JOIN modules m ON l.module_id = m.id
                LEFT JOIN courses c ON m.course_id = c.id
                WHERE q.id = :id";
        
        $result = $this->db->query($sql, ['id' => $this->id])->fetch();
        $this->data = $result ?: [];
    }

    /**
     * Check if quiz exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find quiz by ID
     */
    public static function find($id) {
        $quiz = new self($id);
        return $quiz->exists() ? $quiz : null;
    }
    
    /**
     * Get quizzes by course
     */
    public static function getByCourse($courseId) {
        $db = Database::getInstance();
        $sql = "SELECT q.*, l.title as lesson_title
                FROM quizzes q
                JOIN lessons l ON q.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                WHERE m.course_id = :course_id
                ORDER BY q.created_at DESC";
        
        return $db->query($sql, ['course_id' => $courseId])->fetchAll();
    }
    
    /**
     * Get quiz by lesson
     */
    public static function getByLesson($lessonId) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM quizzes WHERE lesson_id = :lesson_id";
        $result = $db->query($sql, ['lesson_id' => $lessonId])->fetch();
        
        return $result ? new self($result['id']) : null;
    }
    
    /**
     * Create new quiz
     */
    public static function create($data) {
        $db = Database::getInstance();

        $sql = "INSERT INTO quizzes (
            lesson_id, title, description, passing_score,
            time_limit_minutes, max_attempts, show_correct_answers,
            randomize_questions
        ) VALUES (
            :lesson_id, :title, :description, :passing_score,
            :time_limit_minutes, :max_attempts, :show_correct_answers,
            :randomize_questions
        )";

        $params = [
            'lesson_id' => $data['lesson_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'passing_score' => $data['passing_score'] ?? 70,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? $data['time_limit'] ?? null,
            'max_attempts' => $data['max_attempts'] ?? null,
            'show_correct_answers' => $data['show_correct_answers'] ?? 1,
            'randomize_questions' => $data['randomize_questions'] ?? 0
        ];
        
        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update quiz
     */
    public function update($data) {
        $allowed = ['title', 'description', 'passing_score', 'time_limit_minutes',
                   'max_attempts', 'show_correct_answers', 'randomize_questions'];
        
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
        
        $sql = "UPDATE quizzes SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        
        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete quiz
     */
    public function delete() {
        // Delete all questions first
        $sql = "DELETE FROM quiz_questions WHERE quiz_id = :id";
        $this->db->query($sql, ['id' => $this->id]);
        
        // Delete all attempts
        $sql = "DELETE FROM quiz_attempts WHERE quiz_id = :id";
        $this->db->query($sql, ['id' => $this->id]);
        
        // Delete quiz
        $sql = "DELETE FROM quizzes WHERE id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get quiz questions
     */
    public function getQuestions($randomize = false) {
        require_once __DIR__ . '/Question.php';

        $sql = "SELECT q.*, qq.display_order, qq.points_override,
                COALESCE(qq.points_override, q.points) as points
                FROM quiz_questions qq
                JOIN questions q ON qq.question_id = q.question_id
                WHERE qq.quiz_id = :quiz_id";

        if ($randomize || $this->shouldRandomizeQuestions()) {
            $sql .= " ORDER BY RAND()";
        } else {
            $sql .= " ORDER BY qq.display_order ASC";
        }

        return $this->db->query($sql, ['quiz_id' => $this->id])->fetchAll();
    }
    
    /**
     * Get question count
     */
    public function getQuestionCount() {
        $sql = "SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = :quiz_id";
        $result = $this->db->query($sql, ['quiz_id' => $this->id])->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get user attempts
     */
    public function getUserAttempts($userId) {
        $sql = "SELECT * FROM quiz_attempts
                WHERE quiz_id = :quiz_id AND student_id = :student_id
                ORDER BY started_at DESC";

        return $this->db->query($sql, [
            'quiz_id' => $this->id,
            'student_id' => $userId
        ])->fetchAll();
    }
    
    /**
     * Get user's best score
     */
    public function getUserBestScore($userId) {
        $sql = "SELECT MAX(score) as best_score
                FROM quiz_attempts
                WHERE quiz_id = :quiz_id AND student_id = :student_id AND status IN ('Submitted', 'Graded')";

        $result = $this->db->query($sql, [
            'quiz_id' => $this->id,
            'student_id' => $userId
        ])->fetch();
        
        return $result['best_score'] ?? 0;
    }
    
    /**
     * Check if user can take quiz
     */
    public function canUserTake($userId) {
        $attempts = count($this->getUserAttempts($userId));
        $maxAttempts = $this->getMaxAttempts();
        
        // Unlimited attempts
        if ($maxAttempts === null || $maxAttempts == 0) {
            return true;
        }
        
        return $attempts < $maxAttempts;
    }
    
    /**
     * Check if user passed quiz
     */
    public function hasUserPassed($userId) {
        $bestScore = $this->getUserBestScore($userId);
        return $bestScore >= $this->getPassingScore();
    }
    
    /**
     * Start quiz attempt
     */
    public function startAttempt($userId) {
        if (!$this->canUserTake($userId)) {
            return false;
        }

        $sql = "INSERT INTO quiz_attempts (
            quiz_id, student_id, status, started_at
        ) VALUES (
            :quiz_id, :student_id, 'In Progress', NOW()
        )";

        if ($this->db->query($sql, [
            'quiz_id' => $this->id,
            'student_id' => $userId
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Submit quiz attempt
     */
    public function submitAttempt($attemptId, $answers) {
        // Get attempt
        $sql = "SELECT * FROM quiz_attempts WHERE attempt_id = :id";
        $attempt = $this->db->query($sql, ['id' => $attemptId])->fetch();

        if (!$attempt || $attempt['status'] != 'In Progress') {
            return false;
        }
        
        // Get questions
        $questions = $this->getQuestions();
        $totalPoints = 0;
        $earnedPoints = 0;
        $correctAnswers = 0;
        
        foreach ($questions as $question) {
            $totalPoints += $question['points'];

            $questionId = $question['question_id'] ?? $question['id'] ?? null;
            $userAnswer = $answers[$questionId] ?? null;
            $isCorrect = $this->checkAnswer($question, $userAnswer);

            if ($isCorrect) {
                $earnedPoints += $question['points'];
                $correctAnswers++;
            }

            // Save answer
            $this->saveAnswer($attemptId, $questionId, $userAnswer, $isCorrect);
        }
        
        // Calculate score percentage
        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $score >= $this->getPassingScore();
        
        // Update attempt
        $sql = "UPDATE quiz_attempts SET
                status = 'Submitted',
                submitted_at = NOW(),
                score = :score
                WHERE attempt_id = :id";

        $this->db->query($sql, [
            'score' => $score,
            'id' => $attemptId
        ]);
        
        return [
            'score' => $score,
            'passed' => $passed,
            'correct' => $correctAnswers,
            'total' => count($questions)
        ];
    }
    
    /**
     * Check if answer is correct
     */
    private function checkAnswer($question, $userAnswer) {
        if ($userAnswer === null) {
            return false;
        }

        $correctAnswerRaw = $question['correct_answer'] ?? null;
        if ($correctAnswerRaw === null) {
            return false;
        }
        $correctAnswer = is_string($correctAnswerRaw) ? json_decode($correctAnswerRaw, true) : $correctAnswerRaw;

        switch ($question['question_type'] ?? '') {
            case 'multiple_choice':
            case 'true_false':
                return $userAnswer == $correctAnswer;

            case 'multiple_select':
                if (!is_array($userAnswer)) return false;
                sort($userAnswer);
                if (is_array($correctAnswer)) {
                    sort($correctAnswer);
                }
                return $userAnswer == $correctAnswer;

            case 'short_answer':
                $userStr = is_string($userAnswer) ? $userAnswer : '';
                $correctStr = is_string($correctAnswer) ? $correctAnswer : '';
                return strtolower(trim($userStr)) == strtolower(trim($correctStr));

            default:
                return false;
        }
    }
    
    /**
     * Save user answer
     */
    private function saveAnswer($attemptId, $questionId, $answer, $isCorrect) {
        $sql = "INSERT INTO quiz_answers (
            attempt_id, question_id, user_answer, is_correct
        ) VALUES (
            :attempt_id, :question_id, :user_answer, :is_correct
        )";
        
        return $this->db->query($sql, [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'user_answer' => is_array($answer) ? json_encode($answer) : $answer,
            'is_correct' => $isCorrect ? 1 : 0
        ]);
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getLessonId() { return $this->data['lesson_id'] ?? null; }
    public function getLessonTitle() { return $this->data['lesson_title'] ?? ''; }
    public function getCourseId() { return $this->data['course_id'] ?? null; }
    public function getCourseTitle() { return $this->data['course_title'] ?? ''; }
    public function getTitle() { return $this->data['title'] ?? ''; }
    public function getDescription() { return $this->data['description'] ?? ''; }
    public function getPassingScore() { return $this->data['passing_score'] ?? 70; }
    public function getTimeLimit() { return $this->data['time_limit_minutes'] ?? null; }
    public function getMaxAttempts() { return $this->data['max_attempts'] ?? null; }
    public function shouldShowCorrectAnswers() { return ($this->data['show_correct_answers'] ?? 0) == 1; }
    public function shouldRandomizeQuestions() { return ($this->data['randomize_questions'] ?? 0) == 1; }
    public function getCreatedAt() { return $this->data['created_at'] ?? null; }
    public function getUpdatedAt() { return $this->data['updated_at'] ?? null; }
    
    /**
     * Has time limit
     */
    public function hasTimeLimit() {
        return $this->getTimeLimit() !== null && $this->getTimeLimit() > 0;
    }
    
    /**
     * Get formatted time limit
     */
    public function getFormattedTimeLimit() {
        if (!$this->hasTimeLimit()) {
            return 'No time limit';
        }
        
        $minutes = $this->getTimeLimit();
        if ($minutes < 60) {
            return $minutes . ' minutes';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
    }
}