<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class CybersecurityFundamentalsDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Cybersecurity Fundamentals lessons.
     */
    public function run(): void
    {
        $course = Course::find(35);

        if (! $course) {
            $this->command->error('Course "Cybersecurity Fundamentals" (ID 35) not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'The OSI Model',
                'filename' => 'osi-model-seven-layers.svg',
                'alt' => 'The seven-layer OSI model stack from Application to Physical, with send and receive arrows.',
                'caption' => 'Figure: The OSI model shows how data is wrapped and unwrapped across seven layers.',
            ],
            [
                'lesson_title' => 'Firewalls and Network Defense',
                'filename' => 'cybersecurity-cia-triad.svg',
                'alt' => 'The CIA triad: confidentiality, integrity, and availability protected by network defense.',
                'caption' => 'Figure: Network defense exists to protect confidentiality, integrity, and availability.',
            ],
            [
                'lesson_title' => 'Introduction to Ethical Hacking',
                'filename' => 'vulnerability-management-cycle.svg',
                'alt' => 'The vulnerability management cycle: identify, assess, patch or fix, and verify.',
                'caption' => 'Figure: Ethical hackers keep organisations moving through the vulnerability management cycle.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/cybersecurity-fundamentals/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
