<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'ICT & Computing', 'slug' => 'ict-computing', 'description' => 'Information Technology and Computer Science courses', 'icon' => 'computer'],
            ['name' => 'Business & Management', 'slug' => 'business-management', 'description' => 'Business administration and management skills', 'icon' => 'chart-bar'],
            ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'description' => 'Network security and digital protection', 'icon' => 'shield'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Data analysis and visualization', 'icon' => 'database'],
            ['name' => 'Graphic Design', 'slug' => 'graphic-design', 'description' => 'Creative design and multimedia', 'icon' => 'palette'],
        ];

        foreach ($categories as $category) {
            DB::table('course_categories')->updateOrInsert(['slug' => $category['slug']], $category);
        }
    }
}
