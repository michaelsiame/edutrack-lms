<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Launch Your Tech Career',
                'subtitle' => 'With Industry-Recognized Skills',
                'description' => 'Join our growing community of Zambians who transformed their lives through TEVETA-certified programs.',
                'image_path' => 'assets/images/hero-bg-1.jpg',
                'cta_text' => 'Explore Courses',
                'cta_link' => '/courses',
                'secondary_cta_text' => 'Contact Us',
                'secondary_cta_link' => '/contact',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'State-of-the-Art Facilities',
                'subtitle' => 'Learn on Modern Equipment',
                'description' => 'Our computer labs feature the latest hardware and software for hands-on learning.',
                'image_path' => 'assets/images/hero-bg-2.jpg',
                'cta_text' => 'Take a Tour',
                'cta_link' => '/campus',
                'secondary_cta_text' => 'View Programs',
                'secondary_cta_link' => '/courses',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Your Success is Our Mission',
                'subtitle' => 'Real Skills, Real Careers',
                'description' => 'Our graduates work at top companies like MTN, Airtel, and major banks.',
                'image_path' => 'assets/images/hero-bg-3.jpg',
                'cta_text' => 'Apply Now',
                'cta_link' => '/register',
                'secondary_cta_text' => 'Contact Us',
                'secondary_cta_link' => '/contact',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hero_slides')->insert($slides);
    }
}
