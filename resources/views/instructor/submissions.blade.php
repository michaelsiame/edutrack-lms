@extends('layouts.dashboard')

@section('title','Submissions - Edutrack LMS')
@section('page_title','Student Submissions')

@section('content')
<div class="space-y-6" x-data="{ tab: 'assignments' }">
    {{-- Tab Switcher --}}
    <div class="od-card p-1 flex gap-1">
        <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors">
            <i class="fas fa-tasks mr-2"></i>Assignments
        </button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors">
            <i class="fas fa-question-circle mr-2"></i>Quiz Attempts
        </button>
    </div>

    {{-- Assignment Submissions --}}
    <div x-show="tab === 'assignments'" x-cloak>
        <div class="od-card" style="padding: 0; overflow: hidden;">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Assignment Submissions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="od-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Assignment</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="text-right">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignmentSubmissions as $submission)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                        {{ substr($submission->student->user->first_name ?? 'S', 0, 1) }}{{ substr($submission->student->user->last_name ?? '', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $submission->student->user->full_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">{{ $submission->course->title ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900 dark:text-white font-medium">{{ $submission->assignment->title ?? 'N/A' }}</td>
                            <td class="od-meta">{{ $submission->submitted_at?->diffForHumans() ?? 'N/A' }}</td>
                            <td>
                                @php $status = $submission->status ?? 'submitted'; @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $status === 'graded' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' :
                                       ($status === 'late' ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' :
                                       'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400') }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="text-right text-sm font-semibold {{ ($submission->score ?? 0) >= ($submission->assignment->passing_points ?? 60) ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $submission->score ?? '-' }} / {{ $submission->assignment->max_points ?? 100 }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="od-empty-sm">
                                <i class="fas fa-clipboard-check text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                <p class="text-sm">No assignment submissions yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assignmentSubmissions->hasPages())
            <div class="od-card-header" style="border-top: 1px solid var(--od-border); border-bottom: none;">
                {{ $assignmentSubmissions->appends(['quiz_page' => $quizAttempts->currentPage()])->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Quiz Attempts --}}
    <div x-show="tab === 'quizzes'" x-cloak>
        <div class="od-card" style="padding: 0; overflow: hidden;">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Quiz Attempts</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="od-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Quiz</th>
                            <th>Attempted</th>
                            <th class="text-right">Score</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quizAttempts as $attempt)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                        {{ substr($attempt->student->user->first_name ?? 'S', 0, 1) }}{{ substr($attempt->student->user->last_name ?? '', 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $attempt->student->user->full_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">{{ $attempt->quiz->course->title ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900 dark:text-white font-medium">{{ $attempt->quiz->title ?? 'N/A' }}</td>
                            <td class="od-meta">{{ $attempt->completed_at?->diffForHumans() ?? ($attempt->submitted_at?->diffForHumans() ?? 'N/A') }}</td>
                            <td class="text-right text-sm font-semibold {{ ($attempt->score ?? 0) >= ($attempt->quiz->passing_score ?? 60) ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $attempt->score ?? '-' }}%
                            </td>
                            <td>
                                @php $passed = ($attempt->score ?? 0) >= ($attempt->quiz->passing_score ?? 60); @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $passed ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' }}">
                                    {{ $passed ? 'Passed' : 'Failed' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="od-empty-sm">
                                <i class="fas fa-question-circle text-3xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                <p class="text-sm">No quiz attempts yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($quizAttempts->hasPages())
            <div class="od-card-header" style="border-top: 1px solid var(--od-border); border-bottom: none;">
                {{ $quizAttempts->appends(['assignment_page' => $assignmentSubmissions->currentPage()])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
