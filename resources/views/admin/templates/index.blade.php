@extends('layouts.dashboard')

@section('title','Email Templates - Admin')
@section('page_title','Email Templates')

@section('content')
<div class="max-w-6xl mx-auto">
 @if(session('success'))
 <div class="mb-4 p-4 od-toast-success">{{ session('success') }}</div>
 @endif

 <!-- Create Form -->
 <div class="od-card p-6 mb-6">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create New Template</h3>
 <form action="{{ route('admin.templates.store') }}" method="POST">
 @csrf
 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
 <input type="text" name="template_name" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
 <input type="text" name="subject" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 </div>
 <div>
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
 <select name="template_type" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
 <option value="welcome">Welcome</option>
 <option value="enrollment">Enrollment</option>
 <option value="payment">Payment</option>
 <option value="certificate">Certificate</option>
 <option value="password_reset">Password Reset</option>
 <option value="notification">Notification</option>
 <option value="general">General</option>
 </select>
 </div>
 </div>
 <div class="mt-4">
 <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Body (HTML allowed)</label>
 <textarea name="body" rows="4" required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg font-mono text-sm dark:bg-gray-700 dark:text-white"
 placeholder="<p>Dear [Name],</p>
<p>Your message here...</p>"></textarea>
 </div>
 <div class="mt-4 flex items-center gap-4">
 <label class="flex items-center text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_active" value="1" checked
 class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
 <span class="ml-2">Active</span>
 </label>
 <button type="submit" class="ml-auto px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">Create Template</button>
 </div>
 </form>
 </div>

 <!-- Templates List -->
 <div class="od-card" style="padding: 0; overflow: hidden;">
 <div class="overflow-x-auto">
 <table class="od-table min-w-[640px]">
 <thead >
 <tr>
 <th class="px-4 py-3 text-left" scope="col">Template</th>
 <th class="px-4 py-3 text-left" scope="col">Type</th>
 <th class="px-4 py-3 text-left" scope="col">Status</th>
 <th class="px-4 py-3 text-right" scope="col">Actions</th>
 </tr>
 </thead>
 <tbody >
 @forelse($templates as $template)
 <tr >
 <td class="px-4 py-3">
 <div class="font-medium" style="color: var(--od-fg);">{{ $template->template_name }}</div>
 <div class="text-xs text-gray-500">{{ Str::limit($template->subject, 50) }}</div>
 </td>
 <td class="px-4 py-3">
 <span class="text-xs capitalize bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $template->template_type }}</span>
 </td>
 <td class="px-4 py-3">
 <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
 {{ $template->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
 {{ $template->is_active ? 'Active' : 'Inactive' }}
 </span>
 </td>
 <td class="px-4 py-3 text-right">
 <button onclick="toggleEditTemplate({{ $template->template_id }})" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-primary-600 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg mr-1" aria-label="Edit template">
 <i class="fas fa-edit" aria-hidden="true"></i>
 </button>
 <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="inline" data-confirm="Delete this template">
 @csrf
 @method('DELETE')
 <button type="submit" class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] text-danger-600 hover:text-danger-700 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg" aria-label="Delete template">
 <i class="fas fa-trash" aria-hidden="true"></i>
 </button>
 </form>
 </td>
 </tr>
 <!-- Edit Form -->
 <tr id="edit-template-{{ $template->template_id }}" class="hidden bg-gray-50 dark:bg-gray-700/30">
 <td colspan="4" class="px-4 py-4">
 <form action="{{ route('admin.templates.update', $template) }}" method="POST">
 @csrf
 @method('PUT')
 <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
 <input type="text" name="template_name" value="{{ $template->template_name }}" required
 class="od-input">
 <input type="text" name="subject" value="{{ $template->subject }}" required
 class="od-input">
 <select name="template_type" class="od-input">
 <option value="welcome" {{ $template->template_type ==='welcome' ?'selected' :'' }}>Welcome</option>
 <option value="enrollment" {{ $template->template_type ==='enrollment' ?'selected' :'' }}>Enrollment</option>
 <option value="payment" {{ $template->template_type ==='payment' ?'selected' :'' }}>Payment</option>
 <option value="certificate" {{ $template->template_type ==='certificate' ?'selected' :'' }}>Certificate</option>
 <option value="password_reset" {{ $template->template_type ==='password_reset' ?'selected' :'' }}>Password Reset</option>
 <option value="notification" {{ $template->template_type ==='notification' ?'selected' :'' }}>Notification</option>
 <option value="general" {{ $template->template_type ==='general' ?'selected' :'' }}>General</option>
 </select>
 </div>
 <textarea name="body" rows="4" required
 class="w-full px-3 py-2 border rounded-lg text-sm font-mono dark:bg-gray-700 dark:text-white">{{ $template->body }}</textarea>
 <div class="flex items-center gap-3 mt-3">
 <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
 <input type="checkbox" name="is_active" value="1" {{ $template->is_active ?'checked' :'' }}
 class="w-4 h-4 text-primary-600 mr-2 accent-primary-600">
 Active
 </label>
 <button type="submit" class="px-3 py-1.5 bg-primary-600 text-white text-sm rounded hover:bg-primary-700">Update</button>
 <button type="button" onclick="toggleEditTemplate({{ $template->template_id }})" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">Cancel</button>
 </div>
 </form>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="od-empty-sm">No email templates yet.</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <div class="mt-4">
 {{ $templates->links() }}
 </div>
</div>

<script>
function toggleEditTemplate(id) {
 document.getElementById('edit-template-' + id).classList.toggle('hidden');
}
</script>
@endsection
