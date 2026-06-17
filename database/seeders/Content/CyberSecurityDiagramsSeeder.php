<?php

namespace Database\Seeders\Content;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class CyberSecurityDiagramsSeeder extends Seeder
{
    /**
     * Idempotently inject diagram figures into Cyber Security lessons.
     */
    public function run(): void
    {
        $course = Course::where('title', 'Certificate in Cyber Security')->first();

        if (! $course) {
            $this->command->error('Course "Certificate in Cyber Security" not found. Aborting.');
            return;
        }

        $diagrams = [
            [
                'lesson_title' => 'The Mind of an Attacker: Who Targets You and Why',
                'filename' => 'cyber-kill-chain.svg',
                'alt' => 'The cyber kill chain with seven stages from reconnaissance to final action.',
                'caption' => 'Figure: The cyber kill chain shows how an attack advances stage by stage.',
            ],
            [
                'lesson_title' => 'Two-Factor Authentication (2FA)',
                'filename' => 'authentication-factors.svg',
                'alt' => 'The three authentication factors: something you know, something you have, and something you are.',
                'caption' => 'Figure: 2FA combines two different authentication factors for stronger login.',
            ],
            [
                'lesson_title' => 'Recognising Phishing Emails and Messages',
                'filename' => 'phishing-red-flags.svg',
                'alt' => 'Four phishing warning signs: urgent tone, suspicious sender, odd links, and unexpected attachments.',
                'caption' => 'Figure: Recognise these red flags before you click or reply.',
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
                '<figure><img class="lesson-diagram" src="/assets/diagrams/cyber-security/%s" alt="%s"><figcaption>%s</figcaption></figure>',
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
