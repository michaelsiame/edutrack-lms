<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor) {
            abort(403, 'Instructor profile not found.');
        }

        $quizzes = Quiz::whereHas('course', function ($q) use ($instructor) {
            $q->where('instructor_id', $instructor->id);
        })->with('course')->withCount('questions')->latest()->paginate(15);

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
        return view('instructor.quizzes.attempts', compact('quiz', 'attempts'));
    }

    public function grade(Quiz $quiz, QuizAttempt $attempt)
    {
        $this->authorizeInstructor($quiz);
        if ($attempt->quiz_id !== $quiz->id) {
            abort(404);
        }
        $attempt->load(['student.user', 'answers.question.options']);

        // Find next and previous ungraded attempts for navigation
        $nextAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('id', '>', $attempt->id)
            ->where('status', '!=', 'Graded')
            ->orderBy('id')
            ->first();

        $prevAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('id', '<', $attempt->id)
            ->where('status', '!=', 'Graded')
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

        $updateData = [
            'score' => $score,
            'status' => 'Graded',
        ];

        if (\Schema::hasColumn('quiz_attempts', 'graded_at')) {
            $updateData['graded_at'] = now();
        } else {
            $updateData['completed_at'] = now();
        }

        $attempt->update($updateData);

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
        $instructor = auth()->user()->instructor;
        if (!$instructor || $quiz->course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this quiz.');
        }
    }
}
