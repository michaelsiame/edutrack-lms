<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // Microsoft Office Specialist
            ['id' => 1, 'course_id' => 1, 'title' => 'Microsoft Word', 'description' => 'Document creation and formatting', 'display_order' => 1, 'duration_minutes' => 240, 'is_published' => true],
            ['id' => 2, 'course_id' => 1, 'title' => 'Microsoft Excel', 'description' => 'Spreadsheets and data analysis', 'display_order' => 2, 'duration_minutes' => 300, 'is_published' => true],
            ['id' => 3, 'course_id' => 1, 'title' => 'Microsoft PowerPoint', 'description' => 'Presentations and slideshows', 'display_order' => 3, 'duration_minutes' => 180, 'is_published' => true],
            ['id' => 4, 'course_id' => 1, 'title' => 'Microsoft Access', 'description' => 'Database management', 'display_order' => 4, 'duration_minutes' => 240, 'is_published' => true],

            // Cybersecurity
            ['id' => 5, 'course_id' => 2, 'title' => 'Introduction to Cybersecurity', 'description' => 'Basic concepts and frameworks', 'display_order' => 1, 'duration_minutes' => 300, 'is_published' => true],
            ['id' => 6, 'course_id' => 2, 'title' => 'Network Security', 'description' => 'Securing network infrastructure', 'display_order' => 2, 'duration_minutes' => 360, 'is_published' => true],
            ['id' => 7, 'course_id' => 2, 'title' => 'Threat Detection', 'description' => 'Identifying and analyzing threats', 'display_order' => 3, 'duration_minutes' => 300, 'is_published' => true],
            ['id' => 8, 'course_id' => 2, 'title' => 'Ethical Hacking Basics', 'description' => 'Penetration testing fundamentals', 'display_order' => 4, 'duration_minutes' => 360, 'is_published' => true],

            // Web Development
            ['id' => 9, 'course_id' => 3, 'title' => 'HTML & CSS Fundamentals', 'description' => 'Web page structure and styling', 'display_order' => 1, 'duration_minutes' => 240, 'is_published' => true],
            ['id' => 10, 'course_id' => 3, 'title' => 'JavaScript Programming', 'description' => 'Client-side scripting', 'display_order' => 2, 'duration_minutes' => 360, 'is_published' => true],
            ['id' => 11, 'course_id' => 3, 'title' => 'PHP & MySQL', 'description' => 'Server-side programming and databases', 'display_order' => 3, 'duration_minutes' => 420, 'is_published' => true],
            ['id' => 12, 'course_id' => 3, 'title' => 'Laravel Framework', 'description' => 'Modern PHP development', 'display_order' => 4, 'duration_minutes' => 480, 'is_published' => true],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(['id' => $module['id']], $module);
        }
    }
}
