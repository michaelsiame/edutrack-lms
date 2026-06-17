<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class JavaProgrammingDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Java Programming lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Java Programming')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Java Programming" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Compiling and Running from the Command Line',
                'filename' => 'java-compile-run-cycle.svg',
                'alt' => 'Java compile and run cycle from .java source through compiler and bytecode to JVM output.',
                'caption' => 'Figure: Java source is compiled to .class bytecode, then the JVM runs it.',
            ],
            [
                'lesson_title' => 'Writing and Calling Methods',
                'filename' => 'java-method-flow.svg',
                'alt' => 'Java method flow showing call, parameters, method body, and returned value.',
                'caption' => 'Figure: A method call passes values in, runs the body, and returns a result.',
            ],
            [
                'lesson_title' => 'Classes and Objects',
                'filename' => 'oop-inheritance-stack.svg',
                'alt' => 'Object-oriented inheritance stack with superclass, subclass, and object instance.',
                'caption' => 'Figure: Inheritance lets a subclass reuse a superclass and create objects.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/java-programming/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
