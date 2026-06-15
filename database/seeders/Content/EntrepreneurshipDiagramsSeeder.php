<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class EntrepreneurshipDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Entrepreneurship lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Entrepreneurship')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Entrepreneurship" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Testing Your Idea Before You Spend Money',
                'filename' => 'business-idea-validation-steps.svg',
                'alt' => 'Five step validation process: idea, assumptions, quick test, feedback, and decide.',
                'caption' => 'Figure: Validate your business idea in small, cheap steps before investing heavily.',
            ],
            [
                'lesson_title' => 'Pricing for Profit: Chicken Rearing and Salon Examples',
                'filename' => 'pricing-stack.svg',
                'alt' => 'Pricing stack showing cost, profit margin and VAT adding up to the selling price.',
                'caption' => 'Figure: Build your selling price from costs, profit margin, and VAT.',
            ],
            [
                'lesson_title' => 'Funding Your Business: Chilimba, CEEC, and Bank Loans',
                'filename' => 'funding-sources-comparison.svg',
                'alt' => 'Comparison of savings, chilimba, CEEC, and bank loans as funding sources.',
                'caption' => 'Figure: Match each funding source to your business stage and ability to repay.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/entrepreneurship/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
