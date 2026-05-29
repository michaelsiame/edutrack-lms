@extends('layouts.dashboard')

@section('title', $quiz->title . ' - Edutrack LMS')
@section('page_title', $quiz->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/student-design.css') }}">
<style>
    .od-option {
        display: flex;
        align-items: center;
        padding: 14px;
        border-radius: 10px;
        border: 1px solid var(--od-border);
        background: var(--od-surface);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .od-option:hover {
        border-color: var(--od-navy);
        background: var(--od-navy-soft);
    }
    .od-option input {
        margin-right: 12px;
        accent-color: var(--od-navy);
    }
    .od-progress-bar {
        height: 6px;
        background: var(--od-fg-soft);
        border-radius: 999px;
        overflow: hidden;
    }
    .od-progress-bar-fill {
        height: 100%;
        background: var(--od-navy);
        border-radius: 999px;
        transition: width 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="od-page -m-4 md:-m-6 lg:-m-8 p-4 md:p-6 lg:p-8 min-h-full">
    <div class="max-w-4xl mx-auto">
        <x-back-link route="enrollments.show" :routeParams="[$quiz->course]" label="Back to Course" class="mb-4" variant="od" />

        <!-- Quiz Header -->
        <div class="od-card mb-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="flex-1">
                    <p class="od-meta mb-1">{{ $quiz->course->title }} &bull; {{ $questions->count() }} Questions &bull; Pass: {{ $quiz->passing_score ?? 60 }}%</p>
                    <h1 class="od-h2">{{ $quiz->title }}</h1>
                    @if($quiz->description)
                        <p class="od-lead mt-2">{{ $quiz->description }}</p>
                    @endif
                </div>

                @if($quiz->time_limit_minutes)
                    <div class="shrink-0">
                        <div id="timer-card" class="inline-flex items-center gap-3 px-4 py-3 rounded-xl" style="background: color-mix(in oklch, var(--od-danger) 8%, transparent); border: 1px solid color-mix(in oklch, var(--od-danger) 20%, transparent);">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: color-mix(in oklch, var(--od-danger) 15%, transparent);">
                                <i class="fas fa-clock text-sm" style="color: var(--od-danger);"></i>
                            </div>
                            <div>
                                <div class="text-xs font-medium uppercase tracking-wide" style="color: var(--od-danger);">Time Remaining</div>
                                <div id="timer" class="text-xl font-bold tabular-nums od-num" style="color: var(--od-danger);" data-seconds="{{ $remainingSeconds }}">
                                    {{ floor($remainingSeconds / 60) }}:{{ str_pad($remainingSeconds % 60, 2, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Progress -->
            <div class="mt-5 pt-5" style="border-top: 1px solid var(--od-border);">
                <div class="flex items-center justify-between text-xs mb-2" style="color: var(--od-muted);">
                    <span>Question <span id="current-q">1</span> of {{ $questions->count() }}</span>
                    <span>{{ $questions->count() }} total</span>
                </div>
                <div class="od-progress-bar">
                    <div id="quiz-progress" class="od-progress-bar-fill" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Quiz Form -->
        <form action="{{ route('student.quizzes.submit', $quiz) }}" method="POST" id="quiz-form" class="space-y-5">
            @csrf
            <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">

            @foreach($questions as $index => $question)
                <div class="od-card question-card" data-index="{{ $index }}">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm od-num" style="background: var(--od-navy-soft); color: var(--od-navy);">
                            {{ $index + 1 }}
                        </span>
                        <h3 class="text-base font-medium pt-1 leading-relaxed" style="color: var(--od-fg);">{{ $question->question_text }}</h3>
                    </div>

                    <div class="ml-11 space-y-2.5">
                        @if($question->question_type === 'Multiple Choice')
                            @foreach($question->options as $option)
                                <label class="od-option">
                                    <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option->id }}">
                                    <span class="text-sm" style="color: var(--od-fg);">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        @elseif($question->question_type === 'True/False')
                            @foreach($question->options as $option)
                                <label class="od-option">
                                    <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option->option_text }}">
                                    <span class="text-sm" style="color: var(--od-fg);">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        @elseif($question->question_type === 'Short Answer' || $question->question_type === 'Fill in Blank')
                            <input type="text" name="answers[{{ $question->question_id }}]" class="block w-full rounded-xl border text-sm p-3.5 shadow-sm" style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);" placeholder="Enter your answer...">
                        @elseif($question->question_type === 'Essay')
                            <textarea name="answers[{{ $question->question_id }}]" rows="6" class="block w-full rounded-xl border text-sm p-3.5 shadow-sm resize-y" style="border-color: var(--od-border); background: var(--od-surface); color: var(--od-fg);" placeholder="Write your essay response here..."></textarea>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-2">
                <p class="text-sm" style="color: var(--od-muted);">
                    <i class="fas fa-info-circle mr-1" style="color: var(--od-navy);"></i>
                    Make sure to answer all questions before submitting.
                </p>
                <button type="submit" class="od-btn od-btn-primary od-btn-lg">
                    <i class="fas fa-paper-plane"></i> Submit Quiz
                </button>
            </div>
        </form>
    </div>
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
            timerCard.style.animation = 'pulse 1s infinite';
        }
    }
    setInterval(updateTimer, 1000);
    @endif

    // Progress tracking
    const progressBar = document.getElementById('quiz-progress');
    const currentQLabel = document.getElementById('current-q');
    const totalQuestions = {{ $questions->count() }};

    function updateProgress() {
        let answered = 0;
        document.querySelectorAll('.question-card').forEach((card) => {
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
            const notice = document.createElement('div');
            notice.className = 'mb-4 p-3 rounded-lg text-sm flex items-center justify-between';
            notice.style.cssText = 'background: var(--od-navy-soft); border: 1px solid color-mix(in oklch, var(--od-navy) 20%, transparent); color: var(--od-navy);';
            notice.innerHTML = '<span><i class="fas fa-info-circle mr-1"></i>Your previous answers have been restored from ' + new Date(savedTime).toLocaleTimeString() + '.</span><button type="button" class="font-medium hover:opacity-70" onclick="this.parentElement.remove();">Dismiss</button>';
            form.parentElement.insertBefore(notice, form);
        } catch (e) {
            console.error('Failed to restore quiz draft', e);
        }
    }

    restoreDraft();
    setInterval(saveDraft, 30000);

    form.addEventListener('submit', function() {
        localStorage.removeItem(storageKey);
        localStorage.removeItem(storageKey + '_time');
    });
});
</script>
@endpush
@endsection
