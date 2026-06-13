@extends('layouts.dashboard')

@section('title','Enrol a Student - Admin')
@section('page_title','Enrol a Student')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.enrollments.index') }}" class="od-btn od-btn-secondary od-btn-sm mb-4">&larr; Back to Enrolments</a>

    @if(session('error'))
        <div class="mb-4 p-4 od-toast-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 od-toast-danger">{{ $errors->first() }}</div>
    @endif

    <div class="od-card p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            Enrol a student directly — useful for in-person students. This skips the K150 registration-fee gate; record their payment afterwards to unlock the certificate.
        </p>

        <form action="{{ route('admin.enrollments.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="od-form-label">Student</label>
                <select name="user_id" class="od-input" required>
                    <option value="">Select a student…</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                            {{ trim($u->first_name . ' ' . $u->last_name) }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="od-form-label">Course</label>
                <select name="course_id" class="od-input" required
                        onchange="window.location='{{ route('admin.enrollments.create') }}?course='+this.value">
                    <option value="">Select a course…</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ optional($selectedCourse)->id == $c->id ? 'selected' : '' }}>
                            {{ $c->title }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Choosing a course loads its intakes below.</p>
            </div>

            <div>
                <label class="od-form-label">Intake</label>
                @if($selectedCourse && $selectedCourse->intakes->count())
                    <select name="intake_id" class="od-input">
                        @foreach($selectedCourse->intakes as $intake)
                            <option value="{{ $intake->id }}" {{ $intake->is_default ? 'selected' : '' }}>
                                {{ $intake->name }} ({{ ucfirst($intake->status) }}{{ $intake->max_students > 0 ? ', '.$intake->enrollment_count.'/'.$intake->max_students : '' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="od-input" value="Default intake will be used" disabled>
                @endif
            </div>

            <div>
                <label class="od-form-label">Delivery mode</label>
                <select name="mode" class="od-input" required>
                    <option value="in_person" {{ old('mode','in_person')==='in_person'?'selected':'' }}>In-Person</option>
                    <option value="online" {{ old('mode')==='online'?'selected':'' }}>Online</option>
                    <option value="hybrid" {{ old('mode')==='hybrid'?'selected':'' }}>Hybrid</option>
                </select>
            </div>

            <div class="pt-2">
                <button type="submit" class="od-btn od-btn-primary" {{ $selectedCourse ? '' : 'disabled' }}>
                    <i class="fas fa-user-plus mr-1"></i> Enrol Student
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
