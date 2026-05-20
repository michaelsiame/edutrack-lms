<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $photos = [
            [
                'title' => 'Students at Campus Front',
                'description' => 'Group photo of students at the main campus entrance',
                'image_path' => 'assets/images/group-campus-front-01.jpg',
                'category' => 'campus',
                'display_order' => 1,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Outdoor Learning Session',
                'description' => 'Students participating in an outdoor practical class',
                'image_path' => 'assets/images/students-outdoor-class-01.jpg',
                'category' => 'learning',
                'display_order' => 2,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Student Group with Flag',
                'description' => 'Proud students representing Edutrack',
                'image_path' => 'assets/images/group-campus-front-02.jpg',
                'category' => 'campus',
                'display_order' => 3,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Practical Training',
                'description' => 'Hands-on computer training session',
                'image_path' => 'assets/images/students-outdoor-class-03.jpg',
                'category' => 'learning',
                'display_order' => 4,
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Student Success Stories',
                'description' => 'Portrait of successful graduates',
                'image_path' => 'assets/images/students-banner-portrait-01.jpg',
                'category' => 'graduates',
                'display_order' => 5,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Instructor-led Session',
                'description' => 'Expert instructor guiding students',
                'image_path' => 'assets/images/students-outdoor-class-05.jpg',
                'category' => 'learning',
                'display_order' => 6,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Campus Activities',
                'description' => 'Students engaged in campus activities',
                'image_path' => 'assets/images/group-campus-front-03.jpg',
                'category' => 'campus',
                'display_order' => 7,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Graduate Spotlight',
                'description' => 'Celebrating our recent graduates',
                'image_path' => 'assets/images/students-banner-portrait-04.jpg',
                'category' => 'graduates',
                'display_order' => 8,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Collaborative Learning',
                'description' => 'Students working together on projects',
                'image_path' => 'assets/images/students-outdoor-class-02.jpg',
                'category' => 'learning',
                'display_order' => 9,
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('institution_photos')->insert($photos);
    }
}
