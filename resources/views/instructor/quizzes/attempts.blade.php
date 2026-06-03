@extends('layouts.dashboard')

@section('title', 'Quiz Submissions - ' . $quiz->title)
@section('page_title', 'Quiz Submissions')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="mb-6">
        <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-arrow-left mr-1"></i>Back to Quiz
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">Submissions: {{ $quiz->title }}</h1>
        <p class="od-meta">{{ $attempts->total() }} attempt{{ $attempts->total() !== 1 ? 's' : '' }}</p>
    </div>

    @if(session('success'))
    <div class="p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">
        {{ session('success') }}
    </div>
    @endif

    <div class="od-card" style="padding: 0; overflow: hidden;">
        <div class="overflow-x-auto">
            <table class="od-table min-w-[640px]">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Attempt</th>
                        <th>Submitted</th>
                        <th>Time Spent</th>
                        <th>Status</th>
                        <th class="text-right">Score</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attempts as $attempt)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                    {{ substr($attempt->student->user->first_name ?? 'S', 0, 1) }}{{ substr($attempt->student->user->last_name ?? '', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $attempt->student->user->full_name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">#{{ $attempt->attempt_number }}</td>
                        <td class="od-meta">{{ $attempt->submitted_at?->diffForHumans() ?? 'N/A' }}</td>
                        <td class="od-meta">{{ $attempt->time_spent_minutes ?? 0 }} min</td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $attempt->status === 'Graded' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :
                                   ($attempt->status === 'Submitted' ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' :
                                   'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400') }}">
                                {{ $attempt->status }}
                            </span>
                        </td>
                        <td class="text-right text-sm font-semibold {{ ($attempt->score ?? 0) >= $quiz->passing_score ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                            {{ $attempt->score ?? '-' }}%
                        </td>
                        <td class="text-right">
                            <a href="{{ route('instructor.quizzes.attempts.grade', [$quiz, $attempt]) }}" class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <i class="fas fa-pen mr-1.5"></i>Grade
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-clipboard-list text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                            <p class="text-sm">No submissions yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attempts->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $attempts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
