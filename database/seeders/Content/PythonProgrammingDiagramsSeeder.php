<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class PythonProgrammingDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Python Programming lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Python Programming')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Python Programming" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Your First Python Program',
                'filename' => 'python-code-execution-flow.svg',
                'alt' => 'Python code execution flow from source file through interpreter and bytecode to output.',
                'caption' => 'Figure: Python reads your .py source, compiles it to bytecode, and runs it.',
            ],
            [
                'lesson_title' => 'If-Else Statements',
                'filename' => 'program-control-flow.svg',
                'alt' => 'Program control flow showing input, decision, loop, and output stages.',
                'caption' => 'Figure: Control flow steers input through decisions and loops to an output.',
            ],
            [
                'lesson_title' => 'Defining and Calling Functions',
                'filename' => 'python-function-parts.svg',
                'alt' => 'Parts of a Python function: definition, parameters, body, and returned value.',
                'caption' => 'Figure: A function packages a definition, parameters, body, and return value.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/python-programming/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
