<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

/**
 * Deterministic content quality gate for courses. Catches the defects that are
 * easy to ship when authoring content in bulk: empty/thin notes, broken diagram
 * image references, quizzes with no questions, multiple-choice questions with no
 * correct option, modules with no lessons, and courses with no assignments.
 *
 *   php artisan content:qa            # check every course
 *   php artisan content:qa 8          # check one course by id
 *
 * Exit code is non-zero if any issue is found, so it works as a gate.
 */
class ContentQa extends Command
{
    protected $signature = 'content:qa {course? : Course id to check (default: all built courses)}';

    protected $description = 'Check course content for thin notes, broken diagram refs, and broken quizzes';

    public function handle(): int
    {
        $query = Course::with([
            'modules.lessons', 'quizzes.questions.options', 'assignments',
        ])->has('modules');

        if ($id = $this->argument('course')) {
            $query->whereKey($id);
        }

        $courses = $query->orderBy('id')->get();
        if ($courses->isEmpty()) {
            $this->error('No matching course(s) with content found.');
            return self::FAILURE;
        }

        $issues = 0;
        foreach ($courses as $course) {
            $found = [];

            foreach ($course->modules as $module) {
                if ($module->lessons->isEmpty()) {
                    $found[] = "module \"{$module->title}\" has no lessons";
                }

                foreach ($module->lessons as $lesson) {
                    // thin notes — only for reading-style lessons
                    if (in_array($lesson->lesson_type, ['Reading', 'Text', null], true)) {
                        $len = mb_strlen(trim(strip_tags((string) $lesson->content)));
                        if ($len < 150) {
                            $found[] = "lesson #{$lesson->id} \"{$lesson->title}\" has thin notes ({$len} chars)";
                        }
                    }

                    // broken diagram references
                    if (preg_match_all('/src="(\/assets\/diagrams\/[^"]+)"/', (string) $lesson->content, $m)) {
                        foreach ($m[1] as $src) {
                            if (! is_file(public_path(ltrim($src, '/')))) {
                                $found[] = "lesson #{$lesson->id} references missing diagram: {$src}";
                            }
                        }
                    }
                }
            }

            foreach ($course->quizzes as $quiz) {
                if ($quiz->questions->isEmpty()) {
                    $found[] = "quiz \"{$quiz->title}\" has no questions";
                    continue;
                }
                foreach ($quiz->questions as $q) {
                    $type = $q->question_type;
                    if (in_array($type, ['Multiple Choice', 'True/False'], true)) {
                        if (! $q->options->contains(fn ($o) => (bool) $o->is_correct)) {
                            $found[] = "quiz \"{$quiz->title}\": a {$type} question has no correct option";
                        }
                    } elseif ($type === 'Short Answer') {
                        if (blank($q->correct_answer)) {
                            $found[] = "quiz \"{$quiz->title}\": a Short Answer question has no correct_answer";
                        }
                    }
                }
            }

            if ($course->assignments->isEmpty()) {
                $found[] = 'course has no assignments';
            }

            if ($found) {
                $issues += count($found);
                $this->newLine();
                $this->warn("✗ [{$course->id}] {$course->title}");
                foreach ($found as $f) {
                    $this->line("    - {$f}");
                }
            } else {
                $this->info("✓ [{$course->id}] {$course->title}");
            }
        }

        $this->newLine();
        if ($issues === 0) {
            $this->info("Content QA passed for {$courses->count()} course(s).");
            return self::SUCCESS;
        }
        $this->error("{$issues} content issue(s) found across {$courses->count()} course(s).");
        return self::FAILURE;
    }
}
