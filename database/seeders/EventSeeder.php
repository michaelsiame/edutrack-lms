<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Graduation Ceremony 2025',
                'description' => 'Over 200 students graduated with TEVETA-certified diplomas and certificates. The ceremony was attended by industry leaders and government officials. Family and friends gathered to celebrate the achievements of our graduates.',
                'excerpt' => 'Over 200 students graduated with TEVETA-certified diplomas and certificates.',
                'category' => 'Graduation',
                'event_date' => '2025-03-15 09:00:00',
                'location' => 'Edutrack Main Campus, Kalomo',
                'cover_image' => 'assets/images/group-campus-front-01.jpg',
                'is_featured' => true,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cybersecurity Workshop with Industry Experts',
                'description' => 'A hands-on workshop covering the latest in cybersecurity threats, defense strategies, and career opportunities in the field. Students practiced on real-world scenarios and learned from certified ethical hackers.',
                'excerpt' => 'A hands-on workshop covering the latest in cybersecurity threats and defense strategies.',
                'category' => 'Workshop',
                'event_date' => '2025-02-20 10:00:00',
                'location' => 'Edutrack Computer Lab 2',
                'cover_image' => 'assets/images/students-outdoor-class-03.jpg',
                'is_featured' => true,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Partnership with MTN Zambia',
                'description' => 'Edutrack signed an MOU with MTN Zambia to provide internship opportunities for our top-performing students. This partnership opens doors for hands-on industry experience and potential employment.',
                'excerpt' => 'Edutrack signed an MOU with MTN Zambia for student internships.',
                'category' => 'Partnership',
                'event_date' => '2025-01-10 14:00:00',
                'location' => 'MTN Head Office, Lusaka',
                'cover_image' => 'assets/images/group-campus-front-02.jpg',
                'is_featured' => false,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Digital Marketing Bootcamp',
                'description' => 'An intensive 3-day bootcamp covering social media marketing, SEO, content creation, and analytics. Participants built real campaigns and received certificates of participation.',
                'excerpt' => 'An intensive 3-day bootcamp covering social media marketing, SEO, and analytics.',
                'category' => 'Bootcamp',
                'event_date' => '2024-12-05 09:00:00',
                'location' => 'Edutrack Main Campus',
                'cover_image' => 'assets/images/students-outdoor-class-01.jpg',
                'is_featured' => false,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Student Hackathon 2024',
                'description' => 'Teams of students competed to build innovative web applications. The winning team received a full scholarship for advanced courses and mentorship from industry professionals.',
                'excerpt' => 'Teams of students competed to build innovative web applications.',
                'category' => 'Competition',
                'event_date' => '2024-11-18 08:00:00',
                'location' => 'Edutrack Innovation Hub',
                'cover_image' => 'assets/images/students-outdoor-class-02.jpg',
                'is_featured' => false,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'New Computer Lab Opening',
                'description' => 'We opened a new state-of-the-art computer lab with 50 high-performance workstations, dedicated servers, and networking equipment. This expansion allows us to accommodate more students.',
                'excerpt' => 'New state-of-the-art computer lab with 50 high-performance workstations.',
                'category' => 'Facility',
                'event_date' => '2024-10-30 10:00:00',
                'location' => 'Edutrack Main Campus',
                'cover_image' => 'assets/images/students-outdoor-class-05.jpg',
                'is_featured' => false,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Upcoming events
            [
                'title' => 'ICT Career Fair 2026',
                'description' => 'Connect with leading tech employers in Zambia. Bring your resume, practice your interview skills, and explore internship and job opportunities with companies like MTN, Airtel, and Zamtel.',
                'excerpt' => 'Connect with leading tech employers in Zambia.',
                'category' => 'Career',
                'event_date' => '2026-06-15 09:00:00',
                'location' => 'Edutrack Main Campus',
                'cover_image' => null,
                'is_featured' => true,
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Web Development Summer Bootcamp',
                'description' => 'An intensive 2-week bootcamp covering HTML, CSS, JavaScript, and Laravel. Perfect for beginners looking to start a career in web development.',
                'excerpt' => 'Intensive 2-week web development bootcamp for beginners.',
                'category' => 'Bootcamp',
                'event_date' => '2026-07-05 08:00:00',
                'location' => 'Edutrack Computer Lab 1',
                'cover_image' => null,
                'is_featured' => true,
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Alumni Networking Night',
                'description' => 'An evening of networking, mentorship, and knowledge sharing between Edutrack alumni and current students. Build connections that last a lifetime.',
                'excerpt' => 'Networking and mentorship evening for alumni and students.',
                'category' => 'Networking',
                'event_date' => '2026-08-20 17:00:00',
                'location' => 'Edutrack Main Hall',
                'cover_image' => null,
                'is_featured' => false,
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('events')->insert($events);
    }
}
