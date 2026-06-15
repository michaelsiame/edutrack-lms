<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class MobileAppDevelopmentDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Mobile App Development lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Mobile App Development')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Mobile App Development" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'Firewalls and Network Defense',
                'filename' => 'defense-in-depth-layers.svg',
                'alt' => 'Defense in depth layers: People, Endpoint, Network, Application, and Data.',
                'caption' => 'Figure: Defense in depth uses multiple layers so one failure does not expose data.',
            ],
            [
                'lesson_title' => 'Incident Response Process',
                'filename' => 'incident-response-steps.svg',
                'alt' => 'Incident response steps: Prepare, Detect, Contain, Eradicate, Recover, and Learn.',
                'caption' => 'Figure: The incident response lifecycle stops, removes, and learns from security events.',
            ],
            [
                'lesson_title' => 'Access Control and Authentication',
                'filename' => 'security-controls-triad.svg',
                'alt' => 'Preventive, detective, and corrective controls surrounding the CIA triad.',
                'caption' => 'Figure: Controls protect confidentiality, integrity, and availability of information.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/mobile-app-development/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
