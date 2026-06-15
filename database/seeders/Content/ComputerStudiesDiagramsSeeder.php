<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ComputerStudiesDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Computer Studies lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Computer Studies')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Computer Studies" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Computer Hardware: Input, Processing, Output and Storage',
                'filename' => 'computer-hardware-flow.svg',
                'alt' => 'The information processing cycle showing input, processing, output, and storage.',
                'caption' => 'Figure: A computer repeatedly processes input into output and saves data in storage.',
            ],
            [
                'lesson_title' => 'System Software and Application Software',
                'filename' => 'software-types-comparison.svg',
                'alt' => 'Comparison of system software and application software with examples.',
                'caption' => 'Figure: System software runs the machine; application software helps users do tasks.',
            ],
            [
                'lesson_title' => 'Computer Networks: LAN, WAN and Wireless',
                'filename' => 'network-types-ladder.svg',
                'alt' => 'Network size ladder from PAN to LAN to WAN showing coverage ranges.',
                'caption' => 'Figure: Networks grow in range from personal devices up to the global internet.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/computer-studies/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
