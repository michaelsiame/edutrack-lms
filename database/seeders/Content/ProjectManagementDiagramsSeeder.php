<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ProjectManagementDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Project Management lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Project Management')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Project Management" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'The Project Lifecycle from Start to Finish',
                'filename' => 'project-lifecycle-cycle.svg',
                'alt' => 'Project lifecycle cycle showing Initiate, Plan, Execute, and Monitor and Close stages.',
                'caption' => 'Figure: Projects move through four repeating stages from start to finish.',
            ],
            [
                'lesson_title' => 'Work Breakdown Structure (WBS)',
                'filename' => 'work-breakdown-structure.svg',
                'alt' => 'Work breakdown structure for a school IT lab project broken into planning, setup, and handover deliverables.',
                'caption' => 'Figure: A WBS breaks a project into phases and deliverables you can assign and track.',
            ],
            [
                'lesson_title' => 'Identifying and Managing Project Risks',
                'filename' => 'risk-matrix.svg',
                'alt' => 'Project risk matrix plotting probability against impact with monitor, plan, and act-now zones.',
                'caption' => 'Figure: Use a risk matrix to decide which risks need action first.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/project-management/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
