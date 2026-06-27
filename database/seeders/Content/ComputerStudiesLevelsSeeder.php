<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Instructor;
use App\Services\AcceptanceLetterService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComputerStudiesLevelsSeeder extends Seeder
{
    /**
     * Create the three CDF-offered Computer Studies levels if they do not exist.
     */
    public function run(): void
    {
        $service = app(AcceptanceLetterService::class);

        // Mirror an existing published course's category and instructor where possible.
        $mirrorCourse = Course::published()->first();
        $categoryId = $mirrorCourse?->category_id ?? CourseCategory::first()?->id;
        $instructorId = $mirrorCourse?->instructor_id ?? Instructor::first()?->id;

        $levels = [
            [
                'title' => 'Trade Certificate in Computer Studies Level III',
                'slug' => 'computer-studies-level-iii',
                'duration' => '3 months',
                'duration_weeks' => 12,
                'fee_structure' => $service->threeMonthFeeStructure(),
                'price' => 3000,
            ],
            [
                'title' => 'Trade Certificate in Computer Studies Level II',
                'slug' => 'computer-studies-level-ii',
                'duration' => '6 months',
                'duration_weeks' => 24,
                'fee_structure' => $service->sixMonthFeeStructure(),
                'price' => 3000,
            ],
            [
                'title' => 'Trade Certificate in Computer Studies Level I',
                'slug' => 'computer-studies-level-i',
                'duration' => '1 year (12 months)',
                'duration_weeks' => 48,
                'fee_structure' => $service->oneYearFeeStructure(),
                'price' => 3000,
            ],
        ];

        foreach ($levels as $level) {
            Course::firstOrCreate(
                ['slug' => $level['slug']],
                [
                    'title' => $level['title'],
                    'description' => $this->description($level['title'], $level['duration']),
                    'short_description' => 'CDF-sponsored ' . $level['duration'] . ' computer studies programme.',
                    'category_id' => $categoryId,
                    'instructor_id' => $instructorId,
                    'level' => 'beginner',
                    'language' => 'English',
                    'price' => $level['price'],
                    'discount_price' => null,
                    'duration_weeks' => $level['duration_weeks'],
                    'total_hours' => $level['duration_weeks'] * 6,
                    'max_students' => 60,
                    'status' => 'published',
                    'is_featured' => false,
                    'is_template' => false,
                    'is_cdf' => true,
                    'fee_structure' => $level['fee_structure'],
                    'prerequisites' => 'Basic literacy and numeracy.',
                    'learning_outcomes' => 'Demonstrate practical computer skills relevant to the programme level.',
                ]
            );
        }
    }

    protected function description(string $title, string $duration): string
    {
        return "{$title} is a {$duration} CDF-sponsored programme at Edutrack Computer Training. " .
            "Students gain practical computer skills through hands-on lessons, assessments, and projects. " .
            "Day-school and boarding options are available.";
    }
}
