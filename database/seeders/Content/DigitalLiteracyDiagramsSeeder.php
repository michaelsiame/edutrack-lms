<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class DigitalLiteracyDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Digital Literacy lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Digital Literacy')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Digital Literacy" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'What Is a Computer and What Are Its Parts?',
                'filename' => 'computer-system-layers.svg',
                'alt' => 'Computer system layers: hardware, operating system, applications, and user.',
                'caption' => 'Figure: A computer is a stack of hardware, operating system, apps, and you.',
            ],
            [
                'lesson_title' => 'Mobile Money and Safe Online Payments',
                'filename' => 'safe-mobile-money-checklist.svg',
                'alt' => 'Safe mobile money checklist with four checks before sending money.',
                'caption' => 'Figure: Run through these four checks before every mobile money transaction.',
            ],
            [
                'lesson_title' => 'Online Safety and Avoiding Scams in Zambia',
                'filename' => 'online-safety-shield.svg',
                'alt' => 'Online safety shield showing threats on one side and protective habits on the other.',
                'caption' => 'Figure: Good digital habits act as a shield against common online threats.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/digital-literacy/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
