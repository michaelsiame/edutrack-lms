<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class DigitalMarketingDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Digital Marketing lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Digital Marketing')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Digital Marketing" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Finding and Speaking to the Right Audience',
                'filename' => 'marketing-funnel.svg',
                'alt' => 'Marketing funnel showing Awareness, Interest, Decision, and Action stages.',
                'caption' => 'Figure: The marketing funnel shows how a stranger becomes a customer.',
            ],
            [
                'lesson_title' => 'Writing Captions and Creating a Simple Content Calendar',
                'filename' => 'social-media-plan.svg',
                'alt' => 'A five-day weekly social media rhythm: educate, story, engage, proof, promote.',
                'caption' => 'Figure: A repeatable weekly rhythm keeps your content calendar simple.',
            ],
            [
                'lesson_title' => 'Google Business Profile and Local Search',
                'filename' => 'local-search-visibility.svg',
                'alt' => 'Local search flow: search, see a Google Business Profile, then call, get directions, or visit.',
                'caption' => 'Figure: A complete Business Profile turns a search into a customer action.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/digital-marketing/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
