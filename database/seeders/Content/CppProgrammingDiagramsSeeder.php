<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class CppProgrammingDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into C++ Programming lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in C++ Programming')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in C++ Programming" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Your First C++ Program',
                'filename' => 'cpp-build-run-flow.svg',
                'alt' => 'C++ build flow from source code through compiler, object file, linker, to executable run.',
                'caption' => 'Figure: Source code is compiled, linked, and then executed.',
            ],
            [
                'lesson_title' => 'Pointers Explained Gently',
                'filename' => 'pointer-memory-diagram.svg',
                'alt' => 'A pointer variable holds the memory address of an int variable, not the value itself.',
                'caption' => 'Figure: A pointer stores an address, while the variable stores the actual value.',
            ],
            [
                'lesson_title' => 'Classes and Objects: A School Register',
                'filename' => 'class-object-blueprint.svg',
                'alt' => 'A Student class blueprint creates individual student objects with their own data.',
                'caption' => 'Figure: The class is the blueprint; each object is one student record.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/cpp-programming/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
