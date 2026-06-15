<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class GraphicDesigningDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Graphic Designing lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Graphic Designing')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Graphic Designing" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'The Building Blocks of Good Design',
                'filename' => 'design-principles-balance.svg',
                'alt' => 'The four design principles: alignment, contrast, repetition, and proximity.',
                'caption' => 'Figure: Balance alignment, contrast, repetition and proximity to create professional designs.',
            ],
            [
                'lesson_title' => 'Colour Theory for the Zambian Market',
                'filename' => 'colour-harmony-palette.svg',
                'alt' => 'Colour wheel showing complementary pairs and warm versus cool colours for local designs.',
                'caption' => 'Figure: Use opposite colours on the wheel for strong contrast in flyers and logos.',
            ],
            [
                'lesson_title' => 'CMYK vs RGB, Bleed, and Exporting for Print Shops',
                'filename' => 'print-vs-digital-comparison.svg',
                'alt' => 'Comparison of screen design with RGB pixels and print design with CMYK, vectors and bleed.',
                'caption' => 'Figure: Match colour mode and file format to whether the design is viewed on a screen or printed.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/graphic-designing/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
