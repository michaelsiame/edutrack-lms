@extends('layouts.dashboard')

@section('title', $quiz->title . ' - Edutrack LMS')
@section('page_title', $quiz->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <x-back-link route="enrollments.show" :routeParams="[$quiz->course]" label="Back to Course" class="mb-4" />

    <!-- Quiz Header Card -->
    <x-card variant="elevated" class="mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                    <span class="font-medium text-primary-600 dark:text-primary-400">{{ $quiz->course->title }}</span>
                    <span>&bull;</span>
                    <span>{{ $questions->count() }} Questions</span>
                    <span>&bull;</span>
                    <span>Pass: {{ $quiz->passing_score ?? 60 }}%</span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $quiz->title }}</h1>
                @if($quiz->description)
                    <p class="mt-2 text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{{ $quiz->description }}</p>
                @endif
            </div>

            @if($quiz->time_limit_minutes)
                <div class="shrink-0">
                    <div id="timer-card" class="inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-100 dark:border-danger-800">
                        <div class="w-8 h-8 rounded-lg bg-danger-100 dark:bg-danger-900/40 flex items-center justify-center">
                            <i class="fas fa-clock text-danger-600 dark:text-danger-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-danger-600 dark:text-danger-400 uppercase tracking-wide">Time Remaining</div>
                            <div id="timer" class="text-xl font-bold text-danger-700 dark:text-danger-300 tabular-nums" data-seconds="{{ $remainingSeconds }}">
                                {{ floor($remainingSeconds / 60) }}:{{ str_pad($remainingSeconds % 60, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Progress Bar -->
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="text-gray-500 dark:text-gray-400">Question <span id="current-q">1</span> of {{ $questions->count() }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ $questions->count() }} total</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div id="quiz-progress" class="bg-primary-600 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </x-card>

    <!-- Quiz Form -->
    <form action="{{ route('student.quizzes.submit', $quiz) }}" method="POST" id="quiz-form" class="space-y-5">
        @csrf
        <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">

        @foreach($questions as $index => $question)
            <x-card variant="default" class="question-card" data-index="{{ $index }}">
                <div class="flex items-start gap-3 mb-4">
                    <span class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full flex items-center justify-center font-semibold text-sm">
                        {{ $index + 1 }}
                    </span>
                    <h3 class="text-base font-medium text-gray-900 dark:text-white pt-1 leading-relaxed">{{ $question->question_text }}</h3>
                </div>

                <div class="ml-11 space-y-2.5">
                    @if($question->question_type === 'Multiple Choice')
                        @foreach($question->options as $option)
                            <label class="flex items-center p-3.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:border-primary-300 dark:hover:border-primary-700 cursor-pointer transition-all duration-200 group">
                                <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option->id }}" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    @elseif($question->question_type === 'True/False')
                        @foreach($question->options as $option)
                            <label class="flex items-center p-3.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:border-primary-300 dark:hover:border-primary-700 cursor-pointer transition-all duration-200 group">
                                <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option->option_text }}" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    @elseif($question->question_type === 'Short Answer' || $question->question_type === 'Fill in Blank')
                        <input type="text" name="answers[{{ $question->question_id }}]" class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-600 text-sm p-3.5" placeholder="Enter your answer...">
                    @elseif($question->question_type === 'Essay')
                        <textarea name="answers[{{ $question->question_id }}]" rows="6" class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:focus:ring-primary-600 text-sm p-3.5" placeholder="Write your essay response here..."></textarea>
                    @endif
                </div>
            </x-card>
        @endforeach

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-2">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-info-circle mr-1 text-primary-500"></i>
                Make sure to answer all questions before submitting.
            </p>
            <x-button type="submit" variant="primary" size="lg" icon="fa-paper-plane" iconRight>
                Submit Quiz
            </x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerEl = document.getElementById('timer');
    const timerCard = document.getElementById('timer-card');
    const form = document.getElementById('quiz-form');
    const quizId = {{ $quiz->id }};
    const attemptId = {{ $attempt->id }};
    const storageKey = 'quiz_draft_' + quizId + '_' + attemptId;

    // Timer
    @if($remainingSeconds)
    let totalSeconds = parseInt(timerEl.dataset.seconds);
    function updateTimer() {
        if (totalSeconds <= 0) {
            form.submit();
            return;
        }
        totalSeconds--;
        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        timerEl.textContent = m + ':' + s.toString().padStart(2, '0');
        if (totalSeconds < 60) {
            timerCard.classList.add('animate-pulse');
            timerEl.classList.add('text-danger-800', 'dark:text-danger-200');
        }
    }
    setInterval(updateTimer, 1000);
    @endif

    // Quiz progress tracking
    const progressBar = document.getElementById('quiz-progress');
    const currentQLabel = document.getElementById('current-q');
    const totalQuestions = {{ $questions->count() }};

    function updateProgress() {
        let answered = 0;
        document.querySelectorAll('.question-card').forEach((card, idx) => {
            const inputs = card.querySelectorAll('input[type="radio"]:checked, input[type="text"], textarea');
            const hasAnswer = Array.from(inputs).some(i => i.checked || (i.value && i.value.trim()));
            if (hasAnswer) answered++;
        });
        const pct = (answered / totalQuestions) * 100;
        progressBar.style.width = pct + '%';
        currentQLabel.textContent = Math.max(1, answered);
    }

    form.addEventListener('change', updateProgress);
    form.addEventListener('input', updateProgress);

    // Auto-save to localStorage every 30 seconds
    function saveDraft() {
        const data = {};
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('answers[')) {
                data[key] = value;
            }
        }
        localStorage.setItem(storageKey, JSON.stringify(data));
        localStorage.setItem(storageKey + '_time', new Date().toISOString());
    }

    function restoreDraft() {
        const saved = localStorage.getItem(storageKey);
        if (!saved) return;
        try {
            const data = JSON.parse(saved);
            const savedTime = localStorage.getItem(storageKey + '_time');
            for (let key in data) {
                const input = form.querySelector('[name="' + key + '"]');
                if (input) {
                    if (input.type === 'radio') {
                        const radio = form.querySelector('[name="' + key + '"][value="' + data[key] + '"]');
                        if (radio) radio.checked = true;
                    } else {
                        input.value = data[key];
                    }
                }
            }
            updateProgress();
            // Show restore notification
            const notice = document.createElement('div');
            notice.className = 'mb-4 p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg text-sm text-info-700 dark:text-info-300 flex items-center justify-between';
            notice.innerHTML = '<span><i class="fas fa-info-circle mr-1"></i>Your previous answers have been restored from ' + new Date(savedTime).toLocaleTimeString() + '.</span><button type="button" class="text-info-600 hover:text-info-800 font-medium" onclick="this.parentElement.remove();">Dismiss</button>';
            form.parentElement.insertBefore(notice, form);
        } catch (e) {
            console.error('Failed to restore quiz draft', e);
        }
    }

    // Restore on load
    restoreDraft();

    // Auto-save interval
    setInterval(saveDraft, 30000);

    // Save before submit
    form.addEventListener('submit', function() {
        localStorage.removeItem(storageKey);
        localStorage.removeItem(storageKey + '_time');
    });
});
</script>
@endpush
@endsection
