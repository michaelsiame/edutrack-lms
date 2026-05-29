<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function create(Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);
        return view('instructor.questions.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $this->authorizeInstructor($quiz);

        $validated = $request->validate([
            'question_text' => 'required|string|max:2000',
            'question_type' => 'required|in:Multiple Choice,True/False,Short Answer,Essay,Fill in Blank',
            'points' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:5000',
            'correct_answer' => 'nullable|string|max:2000',
            'correct_answer' => 'nullable|string|max:2000',
            'options' => 'nullable|array|min:2',
            'options.*.text' => 'required_with:options|string|max:1000',
            'options.*.is_correct' => 'boolean',
            'correct_option_index' => 'nullable|integer|min:0',
        ]);

        if (in_array($validated['question_type'], ['Multiple Choice', 'True/False', 'Fill in Blank'])) {
            $request->validate([
                'options' => 'required|array|min:2',
                'correct_option_index' => 'required|integer|min:0',
            ]);
        }

        $maxOrder = DB::table('quiz_questions')
            ->where('quiz_id', $quiz->id)
            ->max('display_order') ?? 0;

        DB::transaction(function () use ($validated, $quiz, $maxOrder, $request) {
            $question = Question::create([
                'question_type' => $validated['question_type'],
                'question_text' => $validated['question_text'],
                'points' => $validated['points'],
                'explanation' => $validated['explanation'] ?? null,
                'correct_answer' => $validated['correct_answer'] ?? null,
            ]);

            if (in_array($validated['question_type'], ['Multiple Choice', 'True/False', 'Fill in Blank'])) {
                $correctIndex = $validated['correct_option_index'] ?? $request->input('correct_option_index');
                foreach ($validated['options'] as $index => $option) {
                    QuestionOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => $option['text'],
                        'is_correct' => $index == $correctIndex,
                        'display_order' => $index + 1,
                    ]);
                }
            }

            DB::table('quiz_questions')->insert([
                'quiz_id' => $quiz->id,
                'question_id' => $question->question_id,
                'display_order' => $maxOrder + 1,
                'points_override' => $validated['points'],
            ]);
        });

        return redirect()->route('instructor.quizzes.show', $quiz)
            ->with('success', 'Question added successfully.');
    }

    public function edit(Quiz $quiz, Question $question)
    {
        $this->authorizeInstructor($quiz);
        $question->load('options');
        return view('instructor.questions.edit', compact('quiz', 'question'));
    }

    public function update(Request $request, Quiz $quiz, Question $question)
    {
        $this->authorizeInstructor($quiz);

        if (!$question->quizzes->contains($quiz)) {
            abort(403, 'This question does not belong to the specified quiz.');
        }

        $validated = $request->validate([
            'question_text' => 'required|string|max:2000',
            'question_type' => 'required|in:Multiple Choice,True/False,Short Answer,Essay,Fill in Blank',
            'points' => 'required|integer|min:1|max:100',
            'explanation' => 'nullable|string|max:5000',
            'options' => 'nullable|array|min:2',
            'options.*.text' => 'required_with:options|string|max:1000',
            'options.*.is_correct' => 'boolean',
            'correct_option_index' => 'nullable|integer|min:0',
        ]);

        if (in_array($validated['question_type'], ['Multiple Choice', 'True/False', 'Fill in Blank'])) {
            $request->validate([
                'options' => 'required|array|min:2',
                'correct_option_index' => 'required|integer|min:0',
            ]);
        }

        DB::transaction(function () use ($validated, $question, $request) {
            $question->update([
                'question_type' => $validated['question_type'],
                'question_text' => $validated['question_text'],
                'points' => $validated['points'],
                'explanation' => $validated['explanation'] ?? null,
                'correct_answer' => $validated['correct_answer'] ?? null,
            ]);

            if (in_array($validated['question_type'], ['Multiple Choice', 'True/False', 'Fill in Blank'])) {
                $usedOptionIds = QuizAnswer::whereIn('selected_option_id', $question->options->pluck('id'))->pluck('selected_option_id')->unique();
                $question->options()->whereNotIn('id', $usedOptionIds)->delete();
                $correctIndex = $validated['correct_option_index'] ?? $request->input('correct_option_index');
                foreach ($validated['options'] as $index => $option) {
                    if (isset($option['id'])) {
                        $existing = $question->options()->where('id', $option['id'])->first();
                        if ($existing) {
                            $existing->update([
                                'option_text' => $option['text'],
                                'is_correct' => $index == $correctIndex,
                                'display_order' => $index + 1,
                            ]);
                            continue;
                        }
                    }
                    QuestionOption::create([
                        'question_id' => $question->question_id,
                        'option_text' => $option['text'],
                        'is_correct' => $index == $correctIndex,
                        'display_order' => $index + 1,
                    ]);
                }
            } else {
                $question->options()->delete();
            }

            DB::table('quiz_questions')
                ->where('quiz_id', $quiz->id)
                ->where('question_id', $question->question_id)
                ->update(['points_override' => $validated['points']]);
        });

        return redirect()->route('instructor.quizzes.show', $quiz)
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Quiz $quiz, Question $question)
    {
        $this->authorizeInstructor($quiz);

        if (!$question->quizzes->contains($quiz)) {
            abort(403, 'This question does not belong to the specified quiz.');
        }

        DB::transaction(function () use ($quiz, $question) {
            DB::table('quiz_questions')
                ->where('quiz_id', $quiz->id)
                ->where('question_id', $question->question_id)
                ->delete();

            $question->options()->delete();
            $question->delete();
        });

        return redirect()->route('instructor.quizzes.show', $quiz)
            ->with('success', 'Question deleted successfully.');
    }

    protected function authorizeInstructor(Quiz $quiz): void
    {
        $instructor = auth()->user()->instructor;
        if (!$instructor || $quiz->course->instructor_id !== $instructor->id) {
            abort(403, 'You do not own this quiz.');
        }
    }
}
