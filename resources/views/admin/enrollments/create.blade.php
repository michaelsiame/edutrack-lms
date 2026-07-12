@extends('layouts.dashboard')

@section('title','Enrol a Student - Admin')
@section('page_title','Enrol a Student')

@section('content')
<div class="max-w-2xl mx-auto" x-data="enrollmentForm()">
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

            {{-- Searchable student selector --}}
            <div class="relative" @click.away="studentOpen = false">
                <label class="od-form-label">Student</label>
                <input type="hidden" name="user_id" :value="selectedStudent">
                <div class="relative">
                    <input
                        type="text"
                        class="od-input w-full"
                        placeholder="Search student by name or email…"
                        autocomplete="off"
                        x-model="studentSearch"
                        @focus="studentOpen = true"
                        @input.debounce.150ms="studentOpen = true"
                    >
                    <button type="button" @click="studentOpen = !studentOpen" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas" :class="studentOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                </div>
                <div
                    x-show="studentOpen && filteredStudents.length"
                    x-transition
                    class="absolute z-10 mt-1 w-full max-h-60 overflow-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
                    style="display: none;"
                >
                    <template x-for="u in filteredStudents" :key="u.id">
                        <button
                            type="button"
                            @click="selectStudent(u)"
                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="selectedStudent == u.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200'"
                        >
                            <span x-text="u.label"></span>
                        </button>
                    </template>
                </div>
                <p x-show="selectedStudent && studentSearch" class="text-xs text-gray-400 mt-1">
                    Selected student ID: <span x-text="selectedStudent"></span>
                </p>
            </div>

            {{-- Searchable course selector --}}
            <div class="relative" @click.away="courseOpen = false">
                <label class="od-form-label">Course</label>
                <input type="hidden" name="course_id" :value="selectedCourse">
                <div class="relative">
                    <input
                        type="text"
                        class="od-input w-full"
                        placeholder="Search course…"
                        autocomplete="off"
                        x-model="courseSearch"
                        @focus="courseOpen = true"
                        @input.debounce.150ms="courseOpen = true"
                    >
                    <button type="button" @click="courseOpen = !courseOpen" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas" :class="courseOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                </div>
                <div
                    x-show="courseOpen && filteredCourses.length"
                    x-transition
                    class="absolute z-10 mt-1 w-full max-h-60 overflow-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
                    style="display: none;"
                >
                    <template x-for="c in filteredCourses" :key="c.id">
                        <button
                            type="button"
                            @click="selectCourse(c)"
                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="selectedCourse == c.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200'"
                        >
                            <span x-text="c.title"></span>
                        </button>
                    </template>
                </div>
                <p class="text-xs text-gray-400 mt-1">Choosing a course loads its intakes below.</p>
            </div>

            {{-- Intake cascade --}}
            <div>
                <label class="od-form-label">Intake</label>
                <template x-if="selectedCourse && filteredIntakes.length">
                    <select name="intake_id" class="od-input" x-model="selectedIntake">
                        <template x-for="intake in filteredIntakes" :key="intake.id">
                            <option :value="intake.id" :selected="intake.is_default" x-text="intakeText(intake)"></option>
                        </template>
                    </select>
                </template>
                <template x-if="!selectedCourse || !filteredIntakes.length">
                    <input type="text" class="od-input" value="Select a course to see intakes" disabled>
                </template>
            </div>

            <div>
                <label class="od-form-label">Delivery mode</label>
                <select name="mode" class="od-input" required>
                    <option value="in_person" {{ old('mode','in_person')==='in_person'?'selected':'' }}>In-Person</option>
                    <option value="online" {{ old('mode')==='online'?'selected':'' }}>Online</option>
                    <option value="hybrid" {{ old('mode')==='hybrid'?'selected':'' }}>Hybrid</option>
                </select>
            </div>

            <div>
                <label class="od-form-label">Funding source</label>
                <select name="funding_source" id="funding_source" class="od-input" required onchange="toggleConstituency()">
                    <option value="self" {{ old('funding_source','self')==='self'?'selected':'' }}>Self</option>
                    <option value="cdf" {{ old('funding_source')==='cdf'?'selected':'' }}>CDF</option>
                    <option value="bursary" {{ old('funding_source')==='bursary'?'selected':'' }}>Bursary</option>
                    <option value="employer" {{ old('funding_source')==='employer'?'selected':'' }}>Employer</option>
                </select>
            </div>

            <div id="cdf_constituency_wrapper" class="{{ old('funding_source','self') === 'cdf' ? '' : 'hidden' }}">
                <label class="od-form-label">Constituency</label>
                <input type="text" name="cdf_constituency" value="{{ old('cdf_constituency') }}" class="od-input" placeholder="e.g. Kalomo Central">
            </div>

            <div>
                <label class="od-form-label">Sponsor reference</label>
                <input type="text" name="sponsor_reference" value="{{ old('sponsor_reference') }}" class="od-input" placeholder="Optional sponsor/CDF reference number">
            </div>

            <div class="pt-2">
                <button type="submit" class="od-btn od-btn-primary" :disabled="!selectedCourse || !selectedStudent">
                    <i class="fas fa-user-plus mr-1"></i> Enrol Student
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function enrollmentForm() {
    const users = @json($users);
    const courses = @json($courses);
    const oldUserId = {{ old('user_id', 'null') }};
    const oldCourseId = {{ old('course_id', ($selectedCourse ? $selectedCourse->id : 'null')) }};
    const oldIntakeId = {{ old('intake_id', 'null') }};

    const preselectedUser = users.find(u => u.id == oldUserId);
    const preselectedCourse = courses.find(c => c.id == oldCourseId);
    const defaultIntakeId = preselectedCourse
        ? (oldIntakeId || preselectedCourse.intakes.find(i => i.is_default)?.id || preselectedCourse.intakes[0]?.id || null)
        : null;

    return {
        users: users,
        courses: courses,
        studentSearch: preselectedUser ? preselectedUser.label : '',
        courseSearch: preselectedCourse ? preselectedCourse.title : '',
        studentOpen: false,
        courseOpen: false,
        selectedStudent: preselectedUser ? preselectedUser.id : null,
        selectedCourse: preselectedCourse ? preselectedCourse.id : null,
        selectedIntake: defaultIntakeId,

        get filteredStudents() {
            const term = this.studentSearch.toLowerCase();
            if (!term) return this.users.slice(0, 50);
            return this.users.filter(u => u.label.toLowerCase().includes(term)).slice(0, 50);
        },

        get filteredCourses() {
            const term = this.courseSearch.toLowerCase();
            if (!term) return this.courses.slice(0, 50);
            return this.courses.filter(c => c.title.toLowerCase().includes(term)).slice(0, 50);
        },

        get filteredIntakes() {
            const course = this.courses.find(c => c.id == this.selectedCourse);
            return course ? course.intakes : [];
        },

        selectStudent(u) {
            this.selectedStudent = u.id;
            this.studentSearch = u.label;
            this.studentOpen = false;
        },

        selectCourse(c) {
            this.selectedCourse = c.id;
            this.courseSearch = c.title;
            this.courseOpen = false;
            this.selectedIntake = c.intakes.find(i => i.is_default)?.id || (c.intakes[0]?.id ?? null);
        },

        intakeText(intake) {
            let text = intake.name + ' (' + intake.status.charAt(0).toUpperCase() + intake.status.slice(1);
            if (intake.max_students > 0) {
                text += ', ' + intake.enrollment_count + '/' + intake.max_students;
            }
            text += ')';
            return text;
        },
    };
}

function toggleConstituency() {
    const funding = document.getElementById('funding_source').value;
    const wrapper = document.getElementById('cdf_constituency_wrapper');
    if (funding === 'cdf') {
        wrapper.classList.remove('hidden');
    } else {
        wrapper.classList.add('hidden');
    }
}
</script>
@endsection
