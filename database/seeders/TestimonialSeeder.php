<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Chileshe Banda',
                'course_taken' => 'Web Development',
                'graduation_year' => '2024',
                'rating' => 5,
                'testimonial_text' => 'Edutrack changed my life. I went from being unemployed to working as a junior developer at a tech startup in Lusaka. The practical skills I gained were exactly what employers were looking for.',
                'job_title' => 'Junior Web Developer',
                'company' => 'TechStart Zambia',
                'is_featured' => true,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mutale Mumba',
                'course_taken' => 'Digital Marketing',
                'graduation_year' => '2023',
                'rating' => 5,
                'testimonial_text' => 'The digital marketing course gave me the confidence to start my own agency. Within 6 months of graduating, I had 5 clients and was earning more than my previous job. TEVETA certification really helped.',
                'job_title' => 'Marketing Consultant',
                'company' => 'Mumba Digital',
                'is_featured' => true,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bwalya Chanda',
                'course_taken' => 'Data Science',
                'graduation_year' => '2024',
                'rating' => 5,
                'testimonial_text' => 'The instructors at Edutrack are world-class. They don\'t just teach theory - they make sure you can actually build things. The career support after graduation was exceptional.',
                'job_title' => 'Data Analyst',
                'company' => 'FinanceBank Zambia',
                'is_featured' => true,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Michael Siame',
                'course_taken' => 'Cybersecurity',
                'graduation_year' => '2024',
                'rating' => 5,
                'testimonial_text' => 'I always wanted to work in IT security but didn\'t know where to start. The Cybersecurity program at Edutrack gave me hands-on experience with real tools and scenarios. Now I work as a security analyst.',
                'job_title' => 'Security Analyst',
                'company' => 'MTN Zambia',
                'is_featured' => false,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grace Lungu',
                'course_taken' => 'Microsoft Office Specialist',
                'graduation_year' => '2023',
                'rating' => 4,
                'testimonial_text' => 'As an administrative assistant, improving my Office skills was essential. The course was practical and immediately applicable to my job. I got a promotion within 3 months of completing the program.',
                'job_title' => 'Office Manager',
                'company' => 'Zambia Railways',
                'is_featured' => false,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'John Phiri',
                'course_taken' => 'Computer Hardware & Networking',
                'graduation_year' => '2024',
                'rating' => 5,
                'testimonial_text' => 'The networking course was comprehensive and practical. We worked with real Cisco equipment and configured actual networks. The certification helped me land a job at an ISP in Livingstone.',
                'job_title' => 'Network Technician',
                'company' => 'Zamtel',
                'is_featured' => false,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('testimonials')->insert($testimonials);
    }
}
