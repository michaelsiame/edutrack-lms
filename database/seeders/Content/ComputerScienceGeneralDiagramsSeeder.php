<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ComputerScienceGeneralDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Computer Science General lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Computer Science General')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Computer Science General" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Binary, Bits, and Bytes: Why Computers Use Only Two Digits',
                'filename' => 'binary-to-text-stack.svg',
                'alt' => 'Stack showing how bits become bytes, bytes become characters, and characters become text.',
                'caption' => 'Figure: Bits stack into bytes, bytes map to characters, and characters form text.',
            ],
            [
                'lesson_title' => 'Searching and Sorting Algorithms',
                'filename' => 'search-sort-algorithms-compare.svg',
                'alt' => 'Comparison of linear versus binary search and bubble versus merge sort.',
                'caption' => 'Figure: Different search and sort algorithms trade simplicity for speed.',
            ],
            [
                'lesson_title' => 'How the Internet Works: DNS, Servers, and Packets',
                'filename' => 'internet-request-journey.svg',
                'alt' => 'Journey of a web request from browser through DNS and server to rendered page.',
                'caption' => 'Figure: A web page loads through DNS lookup, server response, and packet reassembly.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/computer-science-general/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
