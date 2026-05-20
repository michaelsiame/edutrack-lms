<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        // Quizzes
        $quizzes = [
            ['id' => 1, 'course_id' => 1, 'lesson_id' => null, 'title' => 'Word Fundamentals Quiz', 'description' => 'Test your knowledge of Microsoft Word basics.', 'quiz_type' => 'Graded', 'time_limit_minutes' => 30, 'max_attempts' => 2, 'passing_score' => 60.00, 'is_published' => true],
            ['id' => 2, 'course_id' => 1, 'lesson_id' => null, 'title' => 'Excel Skills Assessment', 'description' => 'Assessment of Excel formulas and functions.', 'quiz_type' => 'Graded', 'time_limit_minutes' => 45, 'max_attempts' => 2, 'passing_score' => 70.00, 'is_published' => true],
            ['id' => 3, 'course_id' => 2, 'lesson_id' => null, 'title' => 'Cybersecurity Basics', 'description' => 'Test understanding of security concepts.', 'quiz_type' => 'Graded', 'time_limit_minutes' => 30, 'max_attempts' => 3, 'passing_score' => 60.00, 'is_published' => true],
        ];

        foreach ($quizzes as $quiz) {
            DB::table('quizzes')->updateOrInsert(['id' => $quiz['id']], $quiz);
        }

        // Questions
        $questions = [
            ['question_id' => 1, 'question_type' => 'Multiple Choice', 'question_text' => 'Which ribbon tab contains the Bold button in Word?', 'points' => 1],
            ['question_id' => 2, 'question_type' => 'Multiple Choice', 'question_text' => 'What is the keyboard shortcut to save a document?', 'points' => 1],
            ['question_id' => 3, 'question_type' => 'True/False', 'question_text' => 'Excel uses rows and columns to organize data.', 'points' => 1],
            ['question_id' => 4, 'question_type' => 'Multiple Choice', 'question_text' => 'What does CIA stand for in cybersecurity?', 'points' => 1],
        ];

        foreach ($questions as $question) {
            DB::table('questions')->updateOrInsert(['question_id' => $question['question_id']], $question);
        }

        // Quiz Questions pivot
        $quizQuestions = [
            ['quiz_id' => 1, 'question_id' => 1, 'display_order' => 1],
            ['quiz_id' => 1, 'question_id' => 2, 'display_order' => 2],
            ['quiz_id' => 2, 'question_id' => 3, 'display_order' => 1],
            ['quiz_id' => 3, 'question_id' => 4, 'display_order' => 1],
        ];

        foreach ($quizQuestions as $qq) {
            DB::table('quiz_questions')->updateOrInsert(['quiz_id' => $qq['quiz_id'], 'question_id' => $qq['question_id']], $qq);
        }

        // Question Options
        $options = [
            ['question_id' => 1, 'option_text' => 'Home', 'is_correct' => true, 'display_order' => 1],
            ['question_id' => 1, 'option_text' => 'Insert', 'is_correct' => false, 'display_order' => 2],
            ['question_id' => 1, 'option_text' => 'View', 'is_correct' => false, 'display_order' => 3],
            ['question_id' => 2, 'option_text' => 'Ctrl+S', 'is_correct' => true, 'display_order' => 1],
            ['question_id' => 2, 'option_text' => 'Ctrl+P', 'is_correct' => false, 'display_order' => 2],
            ['question_id' => 2, 'option_text' => 'Ctrl+O', 'is_correct' => false, 'display_order' => 3],
            ['question_id' => 3, 'option_text' => 'True', 'is_correct' => true, 'display_order' => 1],
            ['question_id' => 3, 'option_text' => 'False', 'is_correct' => false, 'display_order' => 2],
            ['question_id' => 4, 'option_text' => 'Confidentiality, Integrity, Availability', 'is_correct' => true, 'display_order' => 1],
            ['question_id' => 4, 'option_text' => 'Control, Identify, Authenticate', 'is_correct' => false, 'display_order' => 2],
        ];

        foreach ($options as $option) {
            DB::table('question_options')->insert($option);
        }
    }
}
