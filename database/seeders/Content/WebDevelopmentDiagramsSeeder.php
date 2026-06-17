<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class WebDevelopmentDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Web Development lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Web Development')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Web Development" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'HTML Document Structure',
                'filename' => 'request-response-cycle.svg',
                'alt' => 'Web request-response cycle from browser through DNS and server to rendered page.',
                'caption' => 'Figure: Typing a URL triggers a request, DNS lookup, server response, and page render.',
            ],
            [
                'lesson_title' => 'CSS Basics and Selectors',
                'filename' => 'web-technology-stack.svg',
                'alt' => 'Web technology stack showing HTML structure, CSS presentation, and JavaScript behaviour.',
                'caption' => 'Figure: HTML, CSS, and JavaScript each handle structure, presentation, and behaviour.',
            ],
            [
                'lesson_title' => 'Responsive Design with Media Queries',
                'filename' => 'responsive-breakpoints-flow.svg',
                'alt' => 'Responsive breakpoints flow from mobile through tablet to desktop layouts.',
                'caption' => 'Figure: Media queries adapt one design across mobile, tablet, and desktop screens.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/web-development/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
