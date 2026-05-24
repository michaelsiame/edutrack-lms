@extends('layouts.dashboard')

@section('title','Badges & Achievements')
@section('page_title','Badges & Achievements')

@section('content')
<div class="max-w-5xl mx-auto">
 <div class="flex items-center justify-between mb-6">
 <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gamification Badges</h2>
 <button onclick="toggleBadgeForm()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">
 <i class="fas fa-plus mr-1"></i>Add Badge
 </button>
 </div>

 @if(session('success'))
 <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg text-success-700">{{ session('success') }}</div>
 @endif

 <!-- Create Form -->
 <div id="badge-form" class="hidden bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-6">
 <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Create New Badge</h3>
 <form action="{{ route('admin.badges.store') }}" method="POST">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Badge Name</label>
 <input type="text" name="badge_name" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Badge Type</label>
 <select name="badge_type" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 <option value="completion">Course Completion</option>
 <option value="streak">Learning Streak</option>
 <option value="achievement">Achievement</option>
 <option value="participation">Participation</option>
 </select>
 </div>
 </div>
 <div class="mb-4">
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
 <input type="text" name="description" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon URL (Font Awesome)</label>
 <input type="text" name="badge_icon_url" value="fas fa-medal"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Points</label>
 <input type="number" name="points" value="10" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Criteria</label>
 <input type="text" name="criteria" placeholder="e.g., complete_5_courses"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 </div>
 <div class="flex gap-2">
 <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium text-sm">Create Badge</button>
 <button type="button" onclick="toggleBadgeForm()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Cancel</button>
 </div>
 </form>
 </div>

 <!-- Badges Grid -->
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
 @forelse($badges as $badge)
 <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
 <div class="flex items-start gap-4">
 <div class="w-12 h-12 rounded-full bg-warning-100 dark:bg-warning-900 flex items-center justify-center flex-shrink-0">
 <i class="{{ $badge->badge_icon_url }} text-warning-600 dark:text-warning-400 text-lg"></i>
 </div>
 <div class="flex-1">
 <h3 class="font-semibold text-gray-900 dark:text-white">{{ $badge->badge_name }}</h3>
 <p class="text-xs text-gray-500 mt-0.5">{{ $badge->description }}</p>
 <div class="flex items-center gap-2 mt-2">
 <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded">{{ $badge->badge_type }}</span>
 <span class="text-xs bg-primary-100 text-primary-800 px-2 py-0.5 rounded">{{ $badge->points }} pts</span>
 </div>
 <div class="flex items-center gap-2 mt-3">
 <form action="{{ route('admin.badges.toggle', $badge) }}" method="POST">
 @csrf
 <button type="submit" class="text-xs px-2 py-1 rounded {{ $badge->is_active ?'bg-success-100 text-success-700' :'bg-gray-100 text-gray-500' }}">
 {{ $badge->is_active ?'Active' :'Inactive' }}
 </button>
 </form>
 <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" onsubmit="return confirm('Delete this badge?')">
 @csrf @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-xs text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete badge">
 <i class="fas fa-trash" aria-hidden="true"></i>
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>
 @empty
 <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
 <i class="fas fa-medal text-4xl text-gray-300 mb-4"></i>
 <h3 class="text-lg font-medium text-gray-900 dark:text-white">No Badges Yet</h3>
 <p class="text-gray-500 text-sm mt-1">Create badges to gamify student learning.</p>
 </div>
 @endforelse
 </div>
</div>

<script>
function toggleBadgeForm() {
 document.getElementById('badge-form').classList.toggle('hidden');
}
</script>
@endsection
