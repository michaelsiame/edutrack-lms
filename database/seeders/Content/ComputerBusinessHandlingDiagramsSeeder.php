<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class ComputerBusinessHandlingDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Computer & Business Handling lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Computer & Business Handling')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Computer & Business Handling" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Business Letters and Invoices in Word',
                'filename' => 'business-document-workflow.svg',
                'alt' => 'A four-stage business document workflow: draft, format, proof, then send or print.',
                'caption' => 'Figure: Follow draft → format → proof → send/print for every business document.',
            ],
            [
                'lesson_title' => 'Simple Bookkeeping in Excel: The Cash Book',
                'filename' => 'cash-book-columns.svg',
                'alt' => 'A cash book spreadsheet with Date, Details, Income, Expense, and Balance columns.',
                'caption' => 'Figure: The five cash-book columns show how each transaction changes the balance.',
            ],
            [
                'lesson_title' => 'Stock Sheets and Inventory Tracking',
                'filename' => 'inventory-stock-movement.svg',
                'alt' => 'Inventory movement from goods received to goods sold and the remaining balance.',
                'caption' => 'Figure: Stock movement tracks receipts, sales, and the reorder point.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/computer-business-handling/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
