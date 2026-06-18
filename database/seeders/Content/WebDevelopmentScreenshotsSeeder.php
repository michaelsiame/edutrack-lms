<?php

namespace Database\Seeders\Content;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Course;
use Illuminate\Database\Seeder;

/**
 * Embeds real annotated screenshots into Web Development lessons.
 * Idempotent: skips a lesson if it already contains the image filename.
 */
class WebDevelopmentScreenshotsSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'web-development')->first();
        if (! $course) {
            $this->command->warn('Web Development course not found; skipping screenshots.');
            return;
        }

        $lessonIds = Module::where('course_id', $course->id)->pluck('id');

        $shots = [
            [
                'match' => 'Semantic HTML',
                'file'  => '/assets/screenshots/web-development/semantic-html-layout.png',
                'alt'   => 'A real web page with each semantic region labelled: header, nav, main, article, aside, footer',
                'cap'   => 'Figure: A real web page with each HTML5 semantic element labelled — header, nav, main (containing an article), aside, and footer.',
            ],
        ];

        foreach ($shots as $s) {
            $lesson = Lesson::whereIn('module_id', $lessonIds)
                ->where('title', 'like', '%'.$s['match'].'%')
                ->first();

            if (! $lesson) {
                $this->command->warn("No lesson matching '{$s['match']}'.");
                continue;
            }
            if (str_contains((string) $lesson->content, $s['file'])) {
                $this->command->info("Lesson {$lesson->id} already has this screenshot; skipping.");
                continue;
            }

            $figure = '<figure><img class="lesson-diagram" src="'.$s['file'].'" alt="'.$s['alt'].'"><figcaption>'.$s['cap'].'</figcaption></figure>';

            // place after the first paragraph if present, else prepend
            $content = (string) $lesson->content;
            $pos = stripos($content, '</p>');
            if ($pos !== false) {
                $content = substr($content, 0, $pos + 4).$figure.substr($content, $pos + 4);
            } else {
                $content = $figure.$content;
            }
            $lesson->content = $content;
            $lesson->save();
            $this->command->info("Embedded screenshot into lesson {$lesson->id}: {$lesson->title}");
        }
    }
}
