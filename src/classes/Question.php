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
        $sql = "SELECT * FROM quiz_questions WHERE id = :id";
        $this->data = $this->db->query($sql, ['id' => $this->id])->fetch();
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
        $sql = "SELECT * FROM quiz_questions 
                WHERE quiz_id = :quiz_id 
                ORDER BY order_index ASC";
        
        return $db->query($sql, ['quiz_id' => $quizId])->fetchAll();
    }
    
    /**
     * Create new question
     */
    public static function create($data) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO quiz_questions (
            quiz_id, question_type, question_text, options,
            correct_answer, explanation, points, order_index
        ) VALUES (
            :quiz_id, :question_type, :question_text, :options,
            :correct_answer, :explanation, :points, :order_index
        )";
        
        $params = [
            'quiz_id' => $data['quiz_id'],
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text'],
            'options' => isset($data['options']) ? json_encode($data['options']) : null,
            'correct_answer' => json_encode($data['correct_answer']),
            'explanation' => $data['explanation'] ?? null,
            'points' => $data['points'] ?? 1,
            'order_index' => $data['order_index'] ?? 0
        ];
        
        if ($db->query($sql, $params)) {
            return $db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update question
     */
    public function update($data) {
        $allowed = ['question_type', 'question_text', 'options', 'correct_answer', 
                   'explanation', 'points', 'order_index'];
        
        $updates = [];
        $params = ['id' => $this->id];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                if ($field == 'options' || $field == 'correct_answer') {
                    $updates[] = "$field = :$field";
                    $params[$field] = json_encode($data[$field]);
                } else {
                    $updates[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql = "UPDATE quiz_questions SET " . implode(', ', $updates) . " WHERE id = :id";
        
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
        $sql = "DELETE FROM quiz_questions WHERE id = :id";
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
        $correctAnswer = $this->getCorrectAnswerArray();
        
        switch ($this->getType()) {
            case 'multiple_choice':
            case 'true_false':
                return $userAnswer == $correctAnswer;
                
            case 'multiple_select':
                if (!is_array($userAnswer)) return false;
                sort($userAnswer);
                $correct = $correctAnswer;
                sort($correct);
                return $userAnswer == $correct;
                
            case 'short_answer':
                return strtolower(trim($userAnswer)) == strtolower(trim($correctAnswer));
                
            case 'essay':
                // Essays require manual grading
                return null;
                
            default:
                return false;
        }
    }
    
    // Getters
    public function getId() { return $this->data['id'] ?? null; }
    public function getQuizId() { return $this->data['quiz_id'] ?? null; }
    public function getType() { return $this->data['question_type'] ?? 'multiple_choice'; }
    public function getQuestionText() { return $this->data['question_text'] ?? ''; }
    public function getOptions() { return $this->data['options'] ?? null; }
    public function getCorrectAnswer() { return $this->data['correct_answer'] ?? null; }
    public function getExplanation() { return $this->data['explanation'] ?? ''; }
    public function getPoints() { return $this->data['points'] ?? 1; }
    public function getOrderIndex() { return $this->data['order_index'] ?? 0; }
    
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