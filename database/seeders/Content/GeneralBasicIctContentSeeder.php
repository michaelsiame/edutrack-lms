<?php

namespace Database\Seeders\Content;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds "Certificate in General Basic ICT & Computing":
 *   - clones the 5 Microsoft Office Suite modules (lessons, quizzes, questions,
 *     options, assignments) so the core is identical ("same as Microsoft suite"),
 *   - adds 3 new modules: Digital Citizenship, Entrepreneurship, Scam Awareness.
 * Idempotent: skips if the course already has modules.
 */
class GeneralBasicIctContentSeeder extends Seeder
{
    public function run(): void
    {
        $source = Course::where('title', 'Certificate in Microsoft Office Suite')->first();
        if (! $source) {
            $this->command->error('Source course "Certificate in Microsoft Office Suite" not found. Aborting.');
            return;
        }

        $course = Course::firstOrCreate(
            ['slug' => 'general-basic-ict-computing'],
            [
                'title' => 'Certificate in General Basic ICT & Computing',
                'short_description' => 'Core Microsoft Office skills plus digital citizenship, entrepreneurship and online scam awareness.',
                'description' => 'A practical foundation in everyday computing for work and business in Zambia. You will master the Microsoft Office suite (Word, Excel, PowerPoint, Publisher and Outlook) and then learn to be a responsible digital citizen, start a small business with your new skills, and protect yourself from online and mobile-money scams.',
                'category_id' => $source->category_id,
                'instructor_id' => $source->instructor_id,
                'price' => $source->price,
                'duration_weeks' => $source->duration_weeks,
                'level' => 'Beginner',
                'status' => 'published',
                'is_template' => 0,
            ]
        );

        if (Module::where('course_id', $course->id)->exists()) {
            $this->command->info('General Basic ICT & Computing already has modules. Skipping.');
            return;
        }

        DB::transaction(function () use ($course, $source) {
            $lessonMap = $this->cloneModulesAndLessons($course, $source);
            $this->cloneQuizzes($course, $source, $lessonMap);
            $this->cloneAssignments($course, $source, $lessonMap);
            $this->addExtraModules($course);
        });

        $course->refresh();
        $this->command->info(sprintf(
            'Built General Basic ICT & Computing: modules=%d lessons=%d quizzes=%d assignments=%d',
            $course->modules()->count(),
            Lesson::whereIn('module_id', $course->modules()->pluck('id'))->count(),
            $course->quizzes()->count(),
            $course->assignments()->count()
        ));
    }

    /** @return array<int,int> map of old lesson id => new lesson id */
    private function cloneModulesAndLessons(Course $course, Course $source): array
    {
        $lessonMap = [];
        foreach ($source->modules()->orderBy('display_order')->get() as $module) {
            $newModule = $module->replicate(['course_id']);
            $newModule->course_id = $course->id;
            $newModule->save();

            foreach ($module->lessons()->orderBy('display_order')->get() as $lesson) {
                $newLesson = $lesson->replicate(['module_id']);
                $newLesson->module_id = $newModule->id;
                $newLesson->save();
                $lessonMap[$lesson->id] = $newLesson->id;
            }
        }

        return $lessonMap;
    }

    private function cloneQuizzes(Course $course, Course $source, array $lessonMap): void
    {
        foreach ($source->quizzes()->get() as $quiz) {
            $newQuiz = $quiz->replicate(['course_id', 'lesson_id']);
            $newQuiz->course_id = $course->id;
            $newQuiz->lesson_id = $lessonMap[$quiz->lesson_id] ?? null;
            $newQuiz->save();

            foreach ($quiz->questions()->get() as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->save();

                foreach ($question->options()->get() as $option) {
                    $newOption = $option->replicate(['question_id']);
                    $newOption->question_id = $newQuestion->question_id;
                    $newOption->save();
                }

                $newQuiz->questions()->attach($newQuestion->question_id, [
                    'display_order' => $question->pivot->display_order ?? 0,
                ]);
            }
        }
    }

    private function cloneAssignments(Course $course, Course $source, array $lessonMap): void
    {
        foreach ($source->assignments()->get() as $assignment) {
            $newAssignment = $assignment->replicate(['course_id', 'lesson_id']);
            $newAssignment->course_id = $course->id;
            $newAssignment->lesson_id = $lessonMap[$assignment->lesson_id] ?? null;
            $newAssignment->save();
        }
    }

    private function addExtraModules(Course $course): void
    {
        $order = (int) Module::where('course_id', $course->id)->max('display_order');

        foreach ($this->extraModules() as $modData) {
            $order++;
            $module = Module::create([
                'course_id' => $course->id,
                'title' => $modData['title'],
                'description' => $modData['description'],
                'display_order' => $order,
                'is_published' => 1,
            ]);

            $lessonIds = [];
            $lessonOrder = 0;
            foreach ($modData['lessons'] as $lessonData) {
                $lessonOrder++;
                $lesson = Lesson::create([
                    'module_id' => $module->id,
                    'title' => $lessonData['title'],
                    'content' => $lessonData['content'],
                    'lesson_type' => 'Reading',
                    'duration_minutes' => 20,
                    'display_order' => $lessonOrder,
                    'is_preview' => 0,
                    'is_mandatory' => 1,
                    'points' => 10,
                ]);
                $lessonIds[] = $lesson->id;
            }

            $quizData = $modData['quiz'];
            $quiz = Quiz::create([
                'course_id' => $course->id,
                'lesson_id' => end($lessonIds),
                'title' => $quizData['title'],
                'description' => $quizData['description'],
                'quiz_type' => 'Graded',
                'time_limit_minutes' => 15,
                'max_attempts' => 3,
                'passing_score' => 60.00,
                'show_correct_answers' => 1,
                'is_published' => 1,
            ]);

            $qOrder = 0;
            foreach ($quizData['questions'] as $qData) {
                $qOrder++;
                $question = Question::create([
                    'question_type' => $qData['type'],
                    'question_text' => $qData['text'],
                    'points' => 2,
                    'explanation' => $qData['explanation'],
                    'correct_answer' => $qData['correct_answer'] ?? null,
                ]);
                foreach (($qData['options'] ?? []) as $i => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => $opt['text'],
                        'is_correct' => $opt['correct'] ? 1 : 0,
                        'display_order' => $i + 1,
                    ]);
                }
                $quiz->questions()->attach($question->question_id, ['display_order' => $qOrder]);
            }

            // one practical assignment per new module
            if (! empty($modData['assignment'])) {
                Assignment::create([
                    'course_id' => $course->id,
                    'lesson_id' => $lessonIds[0],
                    'title' => $modData['assignment']['title'],
                    'description' => $modData['assignment']['description'],
                    'instructions' => $modData['assignment']['instructions'],
                    'max_points' => 100,
                    'passing_points' => 50,
                    'due_date' => now()->addWeeks(2),
                    'allow_late_submission' => 1,
                    'late_penalty_percent' => 0,
                    'max_file_size_mb' => 10,
                    'allowed_file_types' => 'pdf,doc,docx,jpg,png',
                ]);
            }
        }
    }

    private function extraModules(): array
    {
        return [
            require __DIR__ . '/general_ict/digital_citizenship.php',
            require __DIR__ . '/general_ict/entrepreneurship.php',
            require __DIR__ . '/general_ict/scam_awareness.php',
        ];
    }
}
