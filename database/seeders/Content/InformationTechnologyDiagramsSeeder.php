<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class InformationTechnologyDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Information Technology lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Information Technology')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Information Technology" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'IT Support Etiquette and Ticketing',
                'filename' => 'it-support-ticket-flow.svg',
                'alt' => 'IT support ticket flow from report through log, diagnose, resolve, and close.',
                'caption' => 'Figure: A structured ticket flow keeps IT support organised and accountable.',
            ],
            [
                'lesson_title' => 'How Office Networks Work',
                'filename' => 'office-network-layout.svg',
                'alt' => 'Small office network layout with router, switch, Wi-Fi access point, computers, and printer.',
                'caption' => 'Figure: A router shares one internet link between wired and wireless office devices.',
            ],
            [
                'lesson_title' => 'Introduction to Cloud Services for Zambian Businesses',
                'filename' => 'cloud-vs-local-comparison.svg',
                'alt' => 'Comparison of local on-premise IT versus cloud services for a business.',
                'caption' => 'Figure: Cloud services move hardware maintenance to the provider and enable remote access.',
            ],
        ];

        $updatedIds = [];

        foreach ($diagrams as $diagram) {
            $lesson = Lesson::whereIn('module_id', function ($query) use ($course) {
                $query->select('id')
                    ->from('modules')
                    ->where('course_id', $course->id);
            })
                ->where('title', 'like', '%'.$diagram['lesson_title'].'%')
                ->first();

            if (! $lesson) {
                $this->command->warn("Lesson '{$diagram['lesson_title']}' not found. Skipping.");
                continue;
            }

            if (str_contains($lesson->content ?? '', $diagram['filename'])) {
                $this->command->info("Lesson {$lesson->id} already contains {$diagram['filename']}. Skipping.");
                continue;
            }

            $figure = sprintf(
                '<figure><img class="lesson-diagram" src="/assets/diagrams/information-technology/%s" alt="%s"><figcaption>%s</figcaption></figure>',
                $diagram['filename'],
                $diagram['alt'],
                $diagram['caption']
            );

            $content = $lesson->content ?? '';

            $firstParagraphEnd = stripos($content, '</p>');

            if ($firstParagraphEnd !== false) {
                $insertAt = $firstParagraphEnd + 4;
                $content = substr($content, 0, $insertAt)."\n\n{$figure}".substr($content, $insertAt);
            } else {
                $content = "{$figure}\n\n{$content}";
            }

            $lesson->content = $content;
            $lesson->save();

            $updatedIds[] = $lesson->id;
            $this->command->info("Updated lesson {$lesson->id} with {$diagram['filename']}.");
        }

        if (empty($updatedIds)) {
            $this->command->info('No lessons were updated.');
        } else {
            $this->command->info('Updated lesson IDs: '.implode(', ', $updatedIds));
        }
    }
}
