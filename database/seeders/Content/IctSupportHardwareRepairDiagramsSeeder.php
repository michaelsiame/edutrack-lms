<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class IctSupportHardwareRepairDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into ICT Support & Hardware Repair lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in ICT Support & Hardware Repair')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in ICT Support & Hardware Repair" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Opening a Computer Safely and Identifying Parts',
                'filename' => 'pc-internal-parts-layout.svg',
                'alt' => 'Inside a desktop computer case showing the motherboard, CPU, RAM slots, power supply, storage, and GPU slots.',
                'caption' => 'Figure: Know the main parts inside a PC case before you touch anything.',
            ],
            [
                'lesson_title' => 'No Power, No Display and Beep Codes',
                'filename' => 'troubleshooting-decision-tree.svg',
                'alt' => 'Troubleshooting decision tree for a PC that will not start, branching by symptom.',
                'caption' => 'Figure: Follow the symptom to find the most likely cause quickly.',
            ],
            [
                'lesson_title' => 'Preventive Maintenance: Dust, Heat and Load-Shedding',
                'filename' => 'preventive-maintenance-checklist.svg',
                'alt' => 'Preventive maintenance checklist covering dust, heat, updates, and backups before load-shedding.',
                'caption' => 'Figure: Regular maintenance prevents expensive breakdowns and data loss.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/ict-support-hardware-repair/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
