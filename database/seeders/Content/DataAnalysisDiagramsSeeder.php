<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class DataAnalysisDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Data Analysis lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Data Analysis')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Data Analysis" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Organising and Cleaning Your First Dataset',
                'filename' => 'data-analysis-pipeline.svg',
                'alt' => 'Data analysis pipeline: Collect, Clean, Analyse, Visualise, Decide.',
                'caption' => 'Figure: The data analysis pipeline moves from raw data to a decision.',
            ],
            [
                'lesson_title' => 'Introduction to Pivot Tables',
                'filename' => 'pivot-table-anatomy.svg',
                'alt' => 'Pivot table anatomy with filters, rows, columns, and values areas.',
                'caption' => 'Figure: A pivot table rearranges fields into rows, columns, values, and filters.',
            ],
            [
                'lesson_title' => 'Building a Simple Dashboard',
                'filename' => 'dashboard-story-flow.svg',
                'alt' => 'Dashboard story flow: Question, Chart, Insight, Action.',
                'caption' => 'Figure: A dashboard should answer a question and lead to a clear action.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/data-analysis/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
