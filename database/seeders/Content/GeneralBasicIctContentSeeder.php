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
            $this->createCourseAssignments($course);
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
                    'duration_minutes' => 45,
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
                    'points' => $qData['type'] === 'Short Answer' ? 3 : 2,
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

        }
    }

    private function createCourseAssignments(Course $course): void
    {
        // Find a suitable lesson from the new modules to attach assignments to.
        $entrepreneurshipModule = Module::where('course_id', $course->id)
            ->where('title', 'Starting a Small Business with Your Skills')
            ->first();
        $scamModule = Module::where('course_id', $course->id)
            ->where('title', 'Cybersecurity: Scam Awareness')
            ->first();

        $businessLessonId = $entrepreneurshipModule?->lessons()->orderByDesc('display_order')->value('id');
        $scamLessonId = $scamModule?->lessons()->orderByDesc('display_order')->value('id');

        Assignment::create([
            'course_id' => $course->id,
            'lesson_id' => $businessLessonId,
            'title' => 'My Small Business One-Pager',
            'description' => 'Create a one-page business plan for a service you could start with your new computer skills.',
            'instructions' => "<ol><li>Choose one service you could offer using your ICT skills (for example, CV typing, poster design, data entry or computer lessons).</li><li>Answer these five questions on one page: (1) What will you sell? (2) Who are your customers? (3) How much will you charge? Show your cost + time + profit for one item. (4) What do you need to start? (5) How will customers find and pay you?</li><li>Write one sample WhatsApp or Facebook advert for your service.</li><li>Save your work as a PDF or Word document named BusinessOnePager_YourName and upload it here.</li></ol>",
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => now()->addWeeks(2),
            'allow_late_submission' => 1,
            'late_penalty_percent' => 0,
            'max_file_size_mb' => 10,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
        ]);

        Assignment::create([
            'course_id' => $course->id,
            'lesson_id' => $scamLessonId,
            'title' => 'Spot the Scam Worksheet',
            'description' => 'Apply what you learned to identify scams and teach someone else how to stay safe.',
            'instructions' => "<ol><li>Find or write down three suspicious messages (SMS, WhatsApp, email, or examples from this module). For each one: (1) identify the type of scam, (2) list the red flags you can see, and (3) describe exactly what you would do.</li><li>Write a short paragraph teaching a family member how to avoid mobile-money scams. Use simple language they will understand.</li><li>Save your worksheet as a PDF or Word document named SpotTheScam_YourName and upload it here.</li></ol>",
            'max_points' => 100,
            'passing_points' => 50,
            'due_date' => now()->addWeeks(2),
            'allow_late_submission' => 1,
            'late_penalty_percent' => 0,
            'max_file_size_mb' => 10,
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
        ]);
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
