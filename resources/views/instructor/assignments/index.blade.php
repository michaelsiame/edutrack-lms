@extends('layouts.dashboard')

@section('title','Assignments - Edutrack LMS')
@section('page_title','Assignments')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
 @if(session('success'))
 <div class="p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">
 {{ session('success') }}
 </div>
 @endif

 @foreach($courses as $course)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
 <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
 <div class="flex items-center justify-between">
 <h3 class="font-semibold text-gray-900 dark:text-white">{{ $course->title }}</h3>
 <button onclick="toggleCreateAssignment({{ $course->id }})" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
 <i class="fas fa-plus mr-1"></i>Add Assignment
 </button>
 </div>
 </div>

 <!-- Create Assignment Form -->
 <div id="create-assignment-{{ $course->id }}" class="hidden p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
 <form action="{{ route('instructor.courses.assignments.store', $course) }}" method="POST" class="space-y-3">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
 <input type="text" name="title" required
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Lesson (optional)</label>
 <select name="lesson_id"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 <option value="">-- None --</option>
 @foreach($course->modules as $module)
 @foreach($module->lessons as $lesson)
 <option value="{{ $lesson->id }}">{{ $module->title }} &gt; {{ $lesson->title }}</option>
 @endforeach
 @endforeach
 </select>
 </div>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
 <textarea name="description" rows="2"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Instructions</label>
 <textarea name="instructions" rows="3"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Max Points</label>
 <input type="number" name="max_points" value="100" min="1"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Passing Points</label>
 <input type="number" name="passing_points" value="60" min="0"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Due Date</label>
 <input type="datetime-local" name="due_date"
 class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div class="flex items-center pt-5">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="allow_late_submission" value="1"
 class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
 <span class="ml-2">Allow Late</span>
 </label>
 </div>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">Create</button>
 <button type="button" onclick="toggleCreateAssignment({{ $course->id }})" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Cancel</button>
 </div>
 </form>
 </div>

 <!-- Assignments List -->
 @if($course->assignments->isEmpty())
 <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
 No assignments for this course.
 </div>
 @else
 <div class="divide-y divide-gray-100 dark:divide-gray-700">
 @foreach($course->assignments as $assignment)
 <div class="p-4">
 <div class="flex items-center justify-between mb-2">
 <h4 class="font-medium text-gray-900 dark:text-white">{{ $assignment->title }}</h4>
 <span class="text-xs text-gray-500">{{ $assignment->submissions->count() }} submissions</span>
 </div>

 @if($assignment->submissions->isNotEmpty())
 <div class="mt-3 space-y-2">
 @foreach($assignment->submissions as $submission)
 <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
 <div class="flex items-center justify-between mb-2">
 <div class="flex items-center gap-2">
 <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $submission->student->full_name }}</span>
 <span class="text-xs text-gray-500">{{ $submission->submitted_at->diffForHumans() }}</span>
 @if($submission->is_late)
 <span class="text-xs text-warning-600 bg-warning-50 px-1.5 py-0.5 rounded">Late</span>
 @endif
 </div>
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
 {{ $submission->status ==='Graded' ?'bg-success-100 text-success-800' :'bg-primary-100 text-primary-800' }}">
 {{ $submission->status }}
 </span>
 </div>

 @if($submission->submission_text)
 <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">{{ $submission->submission_text }}</p>
 @endif

 @if($submission->file_url)
 <a href="{{ $submission->file_url }}" target="_blank" class="text-sm text-primary-600 hover:underline mb-2 inline-block">
 <i class="fas fa-file mr-1"></i>View Attachment
 </a>
 @endif

 @if($submission->status ==='Graded')
 <div class="flex items-center gap-2 text-sm">
 <span class="font-medium text-primary-600">{{ $submission->points_earned }}/{{ $assignment->max_points }}</span>
 @if($submission->feedback)
 <span class="text-gray-500">&bull; {{ Str::limit($submission->feedback, 50) }}</span>
 @endif
 </div>
 @else
 <form action="{{ route('instructor.courses.assignments.grade', [$course, $assignment, $submission]) }}" method="POST" class="flex items-end gap-2 mt-2">
 @csrf
 <div class="w-24">
 <label class="block text-xs text-gray-500 mb-1">Points</label>
 <input type="number" name="points_earned" min="0" max="{{ $assignment->max_points }}" step="0.01" required
 class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
 </div>
 <div class="flex-1">
 <label class="block text-xs text-gray-500 mb-1">Feedback</label>
 <input type="text" name="feedback"
 class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
 placeholder="Feedback...">
 </div>
 <button type="submit" class="px-3 py-1.5 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">Grade</button>
 </form>
 @endif
 </div>
 @endforeach
 </div>
 @endif
 </div>
 @endforeach
 </div>
 @endif
 </div>
 @endforeach
</div>

<script>
function toggleCreateAssignment(courseId) {
 const el = document.getElementById('create-assignment-' + courseId);
 el.classList.toggle('hidden');
}
</script>
@endsection
