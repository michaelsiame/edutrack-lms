<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class AiMachineLearningDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Artificial Intelligence lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Artificial Intelligence')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Artificial Intelligence" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Talking to AI: ChatGPT, Gemini and Good Prompting',
                'filename' => 'ai-input-process-output.svg',
                'alt' => 'AI input-process-output flow from prompt to model to generated reply.',
                'caption' => 'Figure: A prompt goes into the AI model, which returns a predicted output.',
            ],
            [
                'lesson_title' => 'AI for Your Small Business',
                'filename' => 'prompt-engineering-parts.svg',
                'alt' => 'Four parts of a good prompt: context, task, constraints, and output format.',
                'caption' => 'Figure: A strong business prompt includes context, task, constraints, and output format.',
            ],
            [
                'lesson_title' => 'AI Risks: Scams, Deepfakes, and Wrong Answers',
                'filename' => 'ai-risk-vs-benefit-balance.svg',
                'alt' => 'Side-by-side comparison of AI benefits and AI risks.',
                'caption' => 'Figure: Balance AI benefits against scams, deepfakes, wrong answers, and data leaks.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/ai-machine-learning/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
