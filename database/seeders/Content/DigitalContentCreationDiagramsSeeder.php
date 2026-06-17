<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class DigitalContentCreationDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Digital & Content Creation lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Digital & Content Creation')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Digital & Content Creation" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Finding Content Ideas Your Zambian Audience Actually Wants',
                'filename' => 'content-creation-pipeline.svg',
                'alt' => 'Content creation pipeline from idea and script to shoot, edit, post, and review.',
                'caption' => 'Figure: Move each content idea through a repeatable pipeline before you post.',
            ],
            [
                'lesson_title' => 'TikTok, Facebook, and WhatsApp Status Strategy for Zambia',
                'filename' => 'platform-choice-decision.svg',
                'alt' => 'Platform choice flow: start with audience, then goal, format, and finally platform.',
                'caption' => 'Figure: Choose your platform by matching audience, goal and content format.',
            ],
            [
                'lesson_title' => 'Turning Views into Income: Brand Deals, Selling Products, and Services',
                'filename' => 'monetisation-paths.svg',
                'alt' => 'Four monetisation paths for creators: brand deals, products, services, and fan support.',
                'caption' => 'Figure: Turn views into income through one or more of these paths.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/digital-content-creation/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
