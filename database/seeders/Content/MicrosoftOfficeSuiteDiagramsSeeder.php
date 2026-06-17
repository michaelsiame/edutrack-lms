<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class MicrosoftOfficeSuiteDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Microsoft Office Suite lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Microsoft Office Suite')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Microsoft Office Suite" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Working with Objects & Tables',
                'filename' => 'document-production-flow.svg',
                'alt' => 'Word document production flow: draft, format, review, and share.',
                'caption' => 'Figure: A finished document moves through four clear stages.',
            ],
            [
                'lesson_title' => 'Essential Formulas & Functions',
                'filename' => 'excel-formula-pipeline.svg',
                'alt' => 'Excel formula pipeline: data feeds a formula, producing a result that powers a chart.',
                'caption' => 'Figure: Formulas turn raw data into calculated results and visuals.',
            ],
            [
                'lesson_title' => 'Writing Professional Emails',
                'filename' => 'outlook-email-lifecycle.svg',
                'alt' => 'Outlook email lifecycle: compose, send, inbox delivery, then reply or action.',
                'caption' => 'Figure: Every email passes through compose, send, inbox, and action stages.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/microsoft-office-suite/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
