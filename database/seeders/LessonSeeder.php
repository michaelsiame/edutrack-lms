<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = [
            // Word Module
            ['module_id' => 1, 'title' => 'Introduction to Word', 'content' => 'Overview of Word interface and basic navigation.', 'lesson_type' => 'Video', 'duration_minutes' => 30, 'display_order' => 1, 'is_preview' => true],
            ['module_id' => 1, 'title' => 'Document Formatting', 'content' => 'Text formatting, styles, and templates.', 'lesson_type' => 'Reading', 'duration_minutes' => 45, 'display_order' => 2, 'is_preview' => false],
            ['module_id' => 1, 'title' => 'Advanced Features', 'content' => 'Mail merge, macros, and collaboration tools.', 'lesson_type' => 'Video', 'duration_minutes' => 60, 'display_order' => 3, 'is_preview' => false],

            // Excel Module
            ['module_id' => 2, 'title' => 'Excel Basics', 'content' => 'Cells, formulas, and functions.', 'lesson_type' => 'Video', 'duration_minutes' => 45, 'display_order' => 1, 'is_preview' => true],
            ['module_id' => 2, 'title' => 'Data Analysis', 'content' => 'Pivot tables, charts, and filtering.', 'lesson_type' => 'Reading', 'duration_minutes' => 60, 'display_order' => 2, 'is_preview' => false],
            ['module_id' => 2, 'title' => 'Advanced Functions', 'content' => 'VLOOKUP, macros, and data validation.', 'lesson_type' => 'Video', 'duration_minutes' => 75, 'display_order' => 3, 'is_preview' => false],

            // Cybersecurity Intro
            ['module_id' => 5, 'title' => 'Security Concepts', 'content' => 'CIA triad, risk management, and security frameworks.', 'lesson_type' => 'Video', 'duration_minutes' => 60, 'display_order' => 1, 'is_preview' => true],
            ['module_id' => 5, 'title' => 'Common Threats', 'content' => 'Malware, phishing, and social engineering.', 'lesson_type' => 'Reading', 'duration_minutes' => 45, 'display_order' => 2, 'is_preview' => false],

            // Web Dev HTML
            ['module_id' => 9, 'title' => 'HTML Structure', 'content' => 'Elements, attributes, and semantic HTML.', 'lesson_type' => 'Video', 'duration_minutes' => 45, 'display_order' => 1, 'is_preview' => true],
            ['module_id' => 9, 'title' => 'CSS Styling', 'content' => 'Selectors, box model, and responsive design.', 'lesson_type' => 'Reading', 'duration_minutes' => 60, 'display_order' => 2, 'is_preview' => false],
        ];

        foreach ($lessons as $lesson) {
            DB::table('lessons')->insert($lesson);
        }
    }
}
