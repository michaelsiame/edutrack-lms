<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $instructor = $user->instructor;
        if (!$user->isAdmin() && !$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $quizzes = Quiz::when(!$user->isAdmin(), function ($query) use ($instructor) {
                $query->whereHas('course', fn ($q) => $q->where('instructor_id', $instructor->id));
            })
            ->join('courses', 'courses.id', '=', 'quizzes.course_id')
            ->orderBy('courses.title')
            ->orderBy('quizzes.id')
            ->select('quizzes.*')
            ->with('course')->withCount('questions')->paginate(15);

        return view('instructor.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $courses = $instructor->courses()->orderBy('title')->get();
        return view('instructor.quizzes.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|numeric|min:0|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
        ]);

        // Verify course belongs to instructor
        $course = Course::where('id', $validated['course_id'])
            ->where('instructor_id', $instructor->id)
            ->firstOrFail();

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'passing_score' => $validated['passing_score'],
            'time_limit_minutes' => $validated['time_limit'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? 1,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('instructor.quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully.');
    }

    public function show(Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);
        $quiz->load(['course', 'questions.options']);
        return view('instructor.quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);
        $instructor = auth()->user()->instructor;
        $courses = $instructor->courses()->orderBy('title')->get();
        return view('instructor.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|numeric|min:0|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
        ]);

        if (isset($validated['course_id'])) {
            $newCourse = Course::find($validated['course_id']);
            if (!$newCourse || $newCourse->instructor_id !== auth()->user()->instructor->id) {
                abort(403, 'You do not own this course.');
            }
        }

        $quiz->update([
            'course_id' => $validated['course_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'passing_score' => $validated['passing_score'],
            'time_limit_minutes' => $validated['time_limit'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? 1,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('instructor.quizzes.show', $quiz)
            ->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);
        $quiz->delete();
        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Quiz deleted successfully.');
    }

    public function attempts(Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->with(['student.user', 'answers.question'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        $enrolledStudents = $quiz->course->enrollments()
            ->where('enrollment_status', '!=', 'Dropped')
            ->with('user')
            ->get();

        return view('instructor.quizzes.attempts', compact('quiz', 'attempts', 'enrolledStudents'));
    }

    /**
     * Record an offline quiz score for an enrolled student.
     */
    public function recordScore(Request $request, Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->where('enrollment_status', '!=', 'Dropped')
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'Selected user is not enrolled in this course.');
        }

        $student = $user->student;
        if (!$student) {
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => StudentNumberService::generate((int) now()->year),
                'enrollment_date' => now(),
            ]);
        }

        $attempt = DB::transaction(function () use ($quiz, $student, $validated) {
            $nextAttemptNumber = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->max('attempt_number') + 1;

            return QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'attempt_number' => $nextAttemptNumber,
                'started_at' => now(),
                'submitted_at' => now(),
                'completed_at' => now(),
                'score' => $validated['score'],
                'status' => 'Graded',
                'time_spent_minutes' => 0,
                'source' => 'offline',
            ]);
        });

        app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);

        return back()->with('success', "Offline score recorded for {$user->full_name}.");
    }

    public function grade(Quiz $quiz, QuizAttempt $attempt)
    {
        $this->authorizeInstructor($quiz);
        if ($attempt->quiz_id !== $quiz->id) {
            abort(404);
        }
        $attempt->load(['student.user', 'answers.question.options']);

        // Find next and previous ungraded attempts for navigation
        // Ordered by submitted_at to match the attempts list display order
        $nextAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('submitted_at', '>', $attempt->submitted_at ?? $attempt->created_at)
            ->where('status', '!=', 'Graded')
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->first();

        $prevAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('submitted_at', '<', $attempt->submitted_at ?? $attempt->created_at)
            ->where('status', '!=', 'Graded')
            ->orderBy('submitted_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return view('instructor.quizzes.grade', compact('quiz', 'attempt', 'nextAttempt', 'prevAttempt'));
    }

    public function saveGrades(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        $this->authorizeInstructor($quiz);
        if ($attempt->quiz_id !== $quiz->id) {
            abort(404);
        }

        $request->validate([
            'grades' => 'required|array',
        ]);

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            if (isset($request->grades[$answer->id])) {
                $gradeValue = $request->grades[$answer->id];
                if (!is_numeric($gradeValue) || $gradeValue < 0 || $gradeValue > $question->points) {
                    return redirect()->back()->withInput()->withErrors([
                        'grades.' . $answer->id => "Grade must be between 0 and {$question->points}."
                    ]);
                }
            }
        }

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            $totalPoints += $question->points;

            if (isset($request->grades[$answer->id])) {
                $manualPoints = (float) $request->grades[$answer->id];
                $answer->update([
                    'points_earned' => $manualPoints,
                    'is_correct' => $manualPoints >= $question->points,
                ]);
                $earnedPoints += $manualPoints;
            } else {
                $earnedPoints += $answer->points_earned;
            }
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        $attempt->update([
            'score' => $score,
            'status' => 'Graded',
            'completed_at' => now(),
        ]);

        $enrollment = \App\Models\Enrollment::where('user_id', $attempt->student?->user_id)
            ->where('course_id', $quiz->course_id)
            ->first();
        if ($enrollment) {
            app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);
        }

        return redirect()->route('instructor.quizzes.attempts', $quiz)
            ->with('success', 'Grades saved successfully.');
    }

    protected function authorizeInstructor(Quiz $quiz): void
    {
        if (auth()->user()?->isAdmin()) {
            return;
        }

        $instructor = auth()->user()->instructor;
        if (!$instructor || $quiz->course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this quiz.');
        }
    }
}
