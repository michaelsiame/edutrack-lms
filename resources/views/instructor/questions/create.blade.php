@extends('layouts.dashboard')

@section('title', 'Add Question - ' . $quiz->title)
@section('page_title', 'Add Question')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-arrow-left mr-1"></i>Back to Quiz
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">Add Question</h1>
        <p class="od-meta">Quiz: {{ $quiz->title }}</p>
    </div>

    @if($errors->any())
    <div class="bg-danger-100 border border-danger-400 text-danger-700 px-4 py-3 rounded-lg mb-6">
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('instructor.quizzes.questions.store', $quiz) }}" method="POST" class="od-card p-6 space-y-6" x-data="questionForm()" x-init="init()">
        @csrf

        <div>
            <label for="question_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Question Text</label>
            <textarea name="question_text" id="question_text" rows="3" required
                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                placeholder="Enter your question here...">{{ old('question_text') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="question_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Question Type</label>
                <select name="question_type" id="question_type" x-model="questionType"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="Multiple Choice">Multiple Choice</option>
                    <option value="True/False">True / False</option>
                    <option value="Short Answer">Short Answer</option>
                    <option value="Essay">Essay</option>
                    <option value="Fill in Blank">Fill in Blank</option>
                </select>
            </div>
            <div>
                <label for="points" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Points</label>
                <input type="number" name="points" id="points" value="{{ old('points', 1) }}" min="1" max="100" required
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label for="explanation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Explanation (optional)</label>
            <textarea name="explanation" id="explanation" rows="2"
                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                placeholder="Explain the correct answer...">{{ old('explanation') }}</textarea>
        </div>

        {{-- Correct Answer - shown for Short Answer, Fill in Blank, Essay --}}
        <div x-show="!hasOptions" x-cloak>
            <label for="correct_answer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                <span x-text="questionType === 'Essay' ? 'Grading Rubric / Sample Answer (for instructor reference)' : 'Correct Answer'"></span>
            </label>
            <textarea name="correct_answer" id="correct_answer" rows="2" x-model="correctAnswer"
                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                :placeholder="questionType === 'Essay' ? 'Describe what a good answer should include...' : 'Enter the exact correct answer...'">
{{ old('correct_answer') }}</textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="questionType === 'Short Answer' || questionType === 'Fill in Blank'">This will be used for automatic grading (case-insensitive).</p>
        </div>

        {{-- Options section - shown for Multiple Choice, True/False, Fill in Blank --}}
        <div x-show="hasOptions" x-cloak>
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Answer Options</label>
                <button type="button" @click="addOption()" x-show="questionType === 'Multiple Choice'"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                    <i class="fas fa-plus mr-1"></i>Add Option
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(option, index) in options" :key="index">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="correct_option_index" :value="index" :checked="index === correctIndex" @change="correctIndex = index"
                            class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-gray-300">
                        <input type="text" :name="`options[${index}][text]`" x-model="option.text" required
                            class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                            :placeholder="`Option ${index + 1}`">
                        <button type="button" @click="removeOption(index)" x-show="questionType === 'Multiple Choice' && options.length > 2"
                            class="p-1.5 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2"><i class="fas fa-info-circle mr-1"></i>Select the radio button next to the correct answer.</p>
        </div>

        {{-- Short Answer / Essay notice --}}
        <div x-show="!hasOptions" x-cloak class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p class="text-sm text-blue-700 dark:text-blue-400">
                <i class="fas fa-info-circle mr-1"></i>
                <span x-text="questionType === 'Short Answer' ? 'Students will provide a brief text response.' : 'Students will write a detailed essay response.'"></span>
            </p>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium">Cancel</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium">
                <i class="fas fa-save mr-2"></i>Save Question
            </button>
        </div>
    </form>
</div>

<script>
function questionForm() {
    return {
        questionType: '{{ old('question_type', 'Multiple Choice') }}',
        correctIndex: {{ old('correct_option_index', 0) }},
        correctAnswer: '{{ old('correct_answer', '') }}',
        options: {!! json_encode(old('options', [['text' => ''], ['text' => ''], ['text' => ''], ['text' => '']])) !!},
        get hasOptions() {
            return ['Multiple Choice', 'True/False', 'Fill in Blank'].includes(this.questionType);
        },
        addOption() {
            this.options.push({ text: '' });
        },
        removeOption(index) {
            if (this.options.length > 2) {
                this.options.splice(index, 1);
                if (this.correctIndex >= this.options.length) {
                    this.correctIndex = this.options.length - 1;
                }
            }
        },
        init() {
            this.$watch('questionType', value => {
                if (value === 'True/False') {
                    this.options = [
                        { text: 'True' },
                        { text: 'False' }
                    ];
                    this.correctIndex = 0;
                } else if ((value === 'Multiple Choice' || value === 'Fill in Blank') && this.options.length < 2) {
                    this.options = [
                        { text: '' },
                        { text: '' },
                        { text: '' },
                        { text: '' }
                    ];
                    this.correctIndex = 0;
                } else if (value === 'Short Answer' || value === 'Essay') {
                    this.options = [];
                    this.correctIndex = 0;
                }
            });
        }
    }
}
</script>
@endsection
