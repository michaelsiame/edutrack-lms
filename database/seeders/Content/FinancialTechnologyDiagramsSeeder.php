<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class FinancialTechnologyDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Financial Technology lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Financial Technology')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Financial Technology" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'How Mobile Money Works Behind the Scenes',
                'filename' => 'mobile-money-ecosystem.svg',
                'alt' => 'Mobile money ecosystem showing customer, agent, wallet, national switch, and bank.',
                'caption' => 'Figure: Cash moves through agents and wallets, then the national switch settles with banks.',
            ],
            [
                'lesson_title' => 'What Is Blockchain? A Plain-Language Explanation',
                'filename' => 'blockchain-linked-blocks.svg',
                'alt' => 'Blockchain chain of three blocks linked by hashes, showing how tampering breaks the chain.',
                'caption' => 'Figure: Each block stores the previous block\'s hash, so changing one breaks the chain.',
            ],
            [
                'lesson_title' => 'Digital Lending Apps: Reading Terms and True Cost',
                'filename' => 'digital-lending-cost-comparison.svg',
                'alt' => 'Digital lending comparison showing K500 borrowed versus K725 total repayment including interest and fees.',
                'caption' => 'Figure: Interest and fees can make the total repayment much larger than the amount borrowed.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/financial-technology/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
