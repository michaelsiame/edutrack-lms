<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class RecordManagementDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Record Management lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Record Management')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Record Management" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Principles of Good Record Keeping',
                'filename' => 'record-lifecycle.svg',
                'alt' => 'Record lifecycle cycle: create, classify, store, use, then retain or dispose.',
                'caption' => 'Figure: Records move through create, classify, store, use, and retain or dispose.',
            ],
            [
                'lesson_title' => 'Alphabetical, Numerical and Subject Filing',
                'filename' => 'filing-systems-comparison.svg',
                'alt' => 'Comparison of alphabetical, numerical, subject, and chronological filing systems.',
                'caption' => 'Figure: Choose a filing system that matches how people search for records.',
            ],
            [
                'lesson_title' => 'Cloud Storage and Backup Basics',
                'filename' => 'backup-rule-of-three.svg',
                'alt' => 'The 3-2-1 backup rule: three copies, two media types, one off-site.',
                'caption' => 'Figure: The 3-2-1 rule keeps electronic records safe from loss.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/record-management/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
