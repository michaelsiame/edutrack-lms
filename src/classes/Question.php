<?php
/**
 * Question Class
 * Handles quiz questions
 */

class Question {
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
     * Load question data
     */
    private function load() {
        $sql = "SELECT * FROM questions WHERE question_id = :id";
        $result = $this->db->query($sql, ['id' => $this->id])->fetch();
        $this->data = $result ?: [];
    }

    /**
     * Check if question exists
     */
    public function exists() {
        return !empty($this->data);
    }
    
    /**
     * Find question by ID
     */
    public static function find($id) {
        $question = new self($id);
        return $question->exists() ? $question : null;
    }
    
    /**
     * Get questions by quiz
     */
    public static function getByQuiz($quizId) {
        $db = Database::getInstance();
        $sql = "SELECT q.*, qq.display_order, qq.points_override,
                COALESCE(qq.points_override, q.points) as effective_points
                FROM quiz_questions qq
                JOIN questions q ON qq.question_id = q.question_id
                WHERE qq.quiz_id = :quiz_id
                ORDER BY qq.display_order ASC";

        return $db->query($sql, ['quiz_id' => $quizId])->fetchAll();
    }
    
    /**
     * Create new question
     */
    public static function create($data) {
        $db = Database::getInstance();

        // Insert into questions table (the actual question content)
        $sql = "INSERT INTO questions (
            question_type, question_text, explanation, points
        ) VALUES (
            :question_type, :question_text, :explanation, :points
        )";

        $params = [
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text'],
            'explanation' => $data['explanation'] ?? null,
            'points' => $data['points'] ?? 1
        ];

        if (!$db->query($sql, $params)) {
            return false;
        }

        $questionId = $db->lastInsertId();

        // If quiz_id is provided, create the junction record in quiz_questions
        if (!empty($data['quiz_id'])) {
            $junctionSql = "INSERT INTO quiz_questions (
                quiz_id, question_id, display_order, points_override
            ) VALUES (
                :quiz_id, :question_id, :display_order, :points_override
            )";

            $db->query($junctionSql, [
                'quiz_id' => $data['quiz_id'],
                'question_id' => $questionId,
                'display_order' => $data['order_index'] ?? $data['display_order'] ?? 0,
                'points_override' => $data['points_override'] ?? null
            ]);
        }

        return $questionId;
    }
    
    /**
     * Update question
     */
    public function update($data) {
        $allowed = ['question_type', 'question_text', 'explanation', 'points'];

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

        $sql = "UPDATE questions SET " . implode(', ', $updates) . " WHERE question_id = :id";

        if ($this->db->query($sql, $params)) {
            $this->load();
            return true;
        }
        return false;
    }
    
    /**
     * Delete question
     */
    public function delete() {
        // Remove junction records first
        $this->db->query("DELETE FROM quiz_questions WHERE question_id = :id", ['id' => $this->id]);
        // Delete the question itself
        $sql = "DELETE FROM questions WHERE question_id = :id";
        return $this->db->query($sql, ['id' => $this->id]);
    }
    
    /**
     * Get options array
     */
    public function getOptionsArray() {
        $options = $this->getOptions();
        return $options ? json_decode($options, true) : [];
    }
    
    /**
     * Get correct answer array
     */
    public function getCorrectAnswerArray() {
        $answer = $this->getCorrectAnswer();
        return $answer ? json_decode($answer, true) : null;
    }
    
    /**
     * Check if answer is correct
     */
    public function checkAnswer($userAnswer) {
        if ($userAnswer === null) {
            return false;
        }

        $correctAnswer = $this->getCorrectAnswerArray();

        switch ($this->getType()) {
            case 'multiple_choice':
            case 'true_false':
                return $userAnswer == $correctAnswer;

            case 'multiple_select':
                if (!is_array($userAnswer)) return false;
                sort($userAnswer);
                $correct = is_array($correctAnswer) ? $correctAnswer : [];
                sort($correct);
                return $userAnswer == $correct;

            case 'short_answer':
                $userStr = is_string($userAnswer) ? $userAnswer : '';
                $correctStr = is_string($correctAnswer) ? $correctAnswer : '';
                return strtolower(trim($userStr)) == strtolower(trim($correctStr));

            case 'essay':
                // Essays require manual grading
                return null;

            default:
                return false;
        }
    }
    
    // Getters
    public function getId() { return $this->data['question_id'] ?? null; }
    public function getQuizId() { return $this->data['quiz_id'] ?? null; }
    public function getType() { return $this->data['question_type'] ?? 'multiple_choice'; }
    public function getQuestionText() { return $this->data['question_text'] ?? ''; }
    public function getOptions() { return $this->data['options'] ?? null; }
    public function getCorrectAnswer() { return $this->data['correct_answer'] ?? null; }
    public function getExplanation() { return $this->data['explanation'] ?? ''; }
    public function getPoints() { return $this->data['points'] ?? 1; }
    public function getOrderIndex() { return $this->data['display_order'] ?? 0; }
    
    /**
     * Get question type label
     */
    public function getTypeLabel() {
        $types = [
            'multiple_choice' => 'Multiple Choice',
            'multiple_select' => 'Multiple Select',
            'true_false' => 'True/False',
            'short_answer' => 'Short Answer',
            'essay' => 'Essay'
        ];
        
        return $types[$this->getType()] ?? 'Unknown';
    }
}