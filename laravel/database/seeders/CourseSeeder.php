<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'id' => 1,
                'title' => 'Microsoft Office Specialist',
                'slug' => 'microsoft-office-specialist',
                'description' => 'Master Microsoft Word, Excel, PowerPoint, and Access. This comprehensive course covers all essential office applications used in modern workplaces.',
                'short_description' => 'Learn Word, Excel, PowerPoint, and Access for professional productivity.',
                'category_id' => 1,
                'instructor_id' => 1,
                'level' => 'Beginner',
                'language' => 'English',
                'price' => 1500.00,
                'discount_price' => 1200.00,
                'duration_weeks' => 8,
                'total_hours' => 120.00,
                'max_students' => 30,
                'enrollment_count' => 25,
                'status' => 'published',
                'is_featured' => true,
                'rating' => 4.50,
                'total_reviews' => 18,
                'prerequisites' => 'Basic computer literacy',
                'learning_outcomes' => "Create professional documents in Word\nBuild spreadsheets and analyze data in Excel\nDesign engaging presentations in PowerPoint\nManage databases in Access",
            ],
            [
                'id' => 2,
                'title' => 'Cybersecurity Fundamentals',
                'slug' => 'cybersecurity-fundamentals',
                'description' => 'Introduction to cybersecurity principles, network security, threat detection, and ethical hacking basics.',
                'short_description' => 'Protect systems and networks from cyber threats.',
                'category_id' => 3,
                'instructor_id' => 1,
                'level' => 'Intermediate',
                'language' => 'English',
                'price' => 2500.00,
                'discount_price' => null,
                'duration_weeks' => 12,
                'total_hours' => 180.00,
                'max_students' => 20,
                'enrollment_count' => 15,
                'status' => 'published',
                'is_featured' => true,
                'rating' => 4.80,
                'total_reviews' => 12,
                'prerequisites' => 'Basic networking knowledge',
                'learning_outcomes' => "Understand cybersecurity frameworks\nIdentify and mitigate common threats\nImplement network security measures\nPerform basic penetration testing",
            ],
            [
                'id' => 3,
                'title' => 'Web Development with PHP',
                'slug' => 'web-development-php',
                'description' => 'Learn to build dynamic websites and web applications using PHP, MySQL, HTML, CSS, and JavaScript.',
                'short_description' => 'Build modern web applications from scratch.',
                'category_id' => 1,
                'instructor_id' => 1,
                'level' => 'Intermediate',
                'language' => 'English',
                'price' => 3000.00,
                'discount_price' => 2500.00,
                'duration_weeks' => 16,
                'total_hours' => 240.00,
                'max_students' => 25,
                'enrollment_count' => 10,
                'status' => 'published',
                'is_featured' => false,
                'rating' => 4.30,
                'total_reviews' => 8,
                'prerequisites' => 'HTML/CSS basics',
                'learning_outcomes' => "Build responsive websites\nCreate database-driven applications\nImplement user authentication\nDeploy web applications",
            ],
        ];

        foreach ($courses as $course) {
            DB::table('courses')->updateOrInsert(['id' => $course['id']], $course);
        }
    }
}
