<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class SoftwareEngineeringGitDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Software Engineering lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Software Engineering')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Software Engineering" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'The Software Development Life Cycle',
                'filename' => 'software-development-lifecycle.svg',
                'alt' => 'Software development life cycle: Requirements, Design, Code, Test, Deploy, Maintain.',
                'caption' => 'Figure: The SDLC is a repeating loop from planning through maintenance.',
            ],
            [
                'lesson_title' => 'Git Fundamentals: Commits, Branches, and Merging',
                'filename' => 'git-commit-branch-flow.svg',
                'alt' => 'Git branch flow showing a feature branch branched from main, commits, and a merge.',
                'caption' => 'Figure: Feature branches keep new work isolated until it is reviewed and merged.',
            ],
            [
                'lesson_title' => 'Agile vs Waterfall in Plain Language',
                'filename' => 'agile-vs-waterfall-comparison.svg',
                'alt' => 'Agile iterative cycle versus Waterfall sequential stages.',
                'caption' => 'Figure: Agile builds in short cycles; Waterfall moves through fixed phases.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/software-engineering-git/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
