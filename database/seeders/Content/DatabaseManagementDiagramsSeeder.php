<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class DatabaseManagementDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Database Management Systems lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Database Management Systems')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Database Management Systems" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Primary Keys, Foreign Keys, and Relationships',
                'filename' => 'relational-tables-linked.svg',
                'alt' => 'Customers and orders tables linked by a foreign key relationship.',
                'caption' => 'Figure: A foreign key connects two related tables safely.',
            ],
            [
                'lesson_title' => 'SELECT, WHERE, and ORDER BY',
                'filename' => 'sql-crud-cycle.svg',
                'alt' => 'The SQL CRUD cycle: create, read, update, and delete records.',
                'caption' => 'Figure: SQL is built around four repeating operations: create, read, update, delete.',
            ],
            [
                'lesson_title' => 'Normalisation and Avoiding Data Duplication',
                'filename' => 'normalisation-stages.svg',
                'alt' => 'Database normalisation stages from unnormalised data to third normal form.',
                'caption' => 'Figure: Normalisation cleans a table through 1NF, 2NF, and 3NF.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/database-management/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
