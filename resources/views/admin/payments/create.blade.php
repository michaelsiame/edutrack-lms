@extends('layouts.dashboard')

@section('title','Record Payment - Edutrack LMS')
@section('page_title','Record Payment')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="od-card">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Record New Payment</h2>
        </div>

        <form action="{{ route('admin.payments.store') }}" method="POST" class="p-6 space-y-6"
              x-data="paymentForm({
                  students: @js($studentOptions),
                  allCourses: @js($courses),
                  currency: '{{ setting('currency', 'ZMW') }}',
                  oldStudent: {{ old('student_id') ?? 'null' }},
                  oldCourse: {{ old('course_id') ?? 'null' }},
                  oldAmount: '{{ old('amount') }}'
              })"
              x-init="init()">
            @csrf

            @if($errors->any())
            <div class="p-4 od-toast-error border rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Student searchable combobox --}}
                <div class="relative" x-ref="studentWrapper">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Student <span class="text-red-500">*</span>
                    </label>

                    <input type="hidden" name="student_id" x-model="selectedStudentId">

                    <div class="relative">
                        <button type="button"
                                @click="studentOpen = !studentOpen"
                                class="w-full text-left rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2 flex items-center justify-between"
                                :class="{ 'ring-2 ring-primary-500 border-primary-500': studentOpen }">
                            <span x-text="selectedStudent ? selectedStudent.name + ' (' + selectedStudent.email + ')' : 'Select Student'"></span>
                            <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform" :class="{ 'rotate-180': studentOpen }"></i>
                        </button>

                        <div x-show="studentOpen"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             @click.outside="studentOpen = false"
                             class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-72 flex flex-col"
                             style="display: none;">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                <input type="text"
                                       x-model="studentSearch"
                                       @keydown.escape="studentOpen = false"
                                       placeholder="Search student by name or email..."
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                                       x-ref="studentSearchInput">
                            </div>
                            <div class="overflow-y-auto flex-1 p-1">
                                <template x-if="filteredStudents.length === 0">
                                    <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No students found.</div>
                                </template>
                                <template x-for="student in filteredStudents" :key="student.id">
                                    <button type="button"
                                            @click="selectStudent(student)"
                                            class="w-full text-left px-3 py-2 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                                            :class="{ 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': selectedStudentId === student.id }">
                                        <div class="font-medium" x-text="student.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="student.email"></div>
                                    </button>
                                </template>
                                <div x-show="filteredStudents.length === maxStudentResults && studentSearch.length < 2" class="px-3 py-1 text-xs text-gray-400 dark:text-gray-500">
                                    Type more to refine results.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Course dropdown --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Course <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="course_id" x-model="selectedCourseId">

                    <button type="button"
                            @click="courseOpen = !courseOpen"
                            :disabled="!selectedStudentId"
                            class="w-full text-left rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2 flex items-center justify-between disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{ 'ring-2 ring-primary-500 border-primary-500': courseOpen }">
                        <span x-text="selectedCourse ? selectedCourse.course_title : (selectedStudentId ? 'Select Course' : 'Select a student first')"></span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform" :class="{ 'rotate-180': courseOpen }"></i>
                    </button>

                    <div x-show="courseOpen"
                         x-transition
                         @click.outside="courseOpen = false"
                         class="absolute z-40 mt-1 w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto"
                         style="display: none;">
                        <template x-if="courseOptions.length === 0">
                            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No courses available.</div>
                        </template>
                        <template x-for="course in courseOptions" :key="course.course_id">
                            <button type="button"
                                    @click="selectCourse(course)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                    :class="{ 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': selectedCourseId === course.course_id }">
                                <div class="font-medium" x-text="course.course_title"></div>
                                <div class="text-xs" :class="course.is_enrolled ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400'">
                                    <span x-text="course.is_enrolled ? 'Enrolled' : 'New enrollment'"></span>
                                    <span x-show="course.is_enrolled && course.outstanding > 0" x-text="' · Outstanding: ' + currency + ' ' + formatMoney(course.outstanding)"></span>
                                    <span x-show="course.is_enrolled && course.outstanding === 0" class="text-success-600 dark:text-success-400"> · Fully paid</span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Enrollment balance summary --}}
            <div x-show="selectedCourse && selectedCourse.is_enrolled" x-transition class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Enrollment Summary</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                              'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400': selectedCourse && selectedCourse.payment_status === 'completed',
                              'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400': selectedCourse && selectedCourse.payment_status === 'partial',
                              'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400': selectedCourse && selectedCourse.payment_status === 'pending'
                          }"
                          x-text="selectedCourse ? (selectedCourse.payment_status ? selectedCourse.payment_status.charAt(0).toUpperCase() + selectedCourse.payment_status.slice(1) : 'Pending') : ''">
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Total Fee</div>
                        <div class="font-medium text-gray-900 dark:text-white" x-text="currency + ' ' + formatMoney(selectedCourse ? selectedCourse.total_fee : 0)"></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Paid</div>
                        <div class="font-medium text-success-600 dark:text-success-400" x-text="currency + ' ' + formatMoney(selectedCourse ? selectedCourse.amount_paid : 0)"></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Discounts</div>
                        <div class="font-medium text-warning-600 dark:text-warning-400" x-text="currency + ' ' + formatMoney(selectedCourse ? selectedCourse.discounts : 0)"></div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Outstanding</div>
                        <div class="font-medium text-danger-600 dark:text-danger-400" x-text="currency + ' ' + formatMoney(selectedCourse ? selectedCourse.outstanding : 0)"></div>
                    </div>
                </div>
                <div x-show="selectedCourse && selectedCourse.outstanding === 0" class="mt-3 text-sm text-success-700 dark:text-success-300 bg-success-50 dark:bg-success-900/20 rounded px-3 py-2">
                    This enrollment is already fully paid.
                </div>
            </div>

            <div x-show="selectedCourse && !selectedCourse.is_enrolled" x-transition class="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-4 border border-warning-100 dark:border-warning-900/30 text-sm text-warning-800 dark:text-warning-200">
                <i class="fas fa-info-circle mr-1"></i>
                This student is not enrolled in the selected course. Submitting will create a new enrollment.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Amount ({{ setting('currency', 'ZMW') }}) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="amount" x-model="amount" required step="0.01" min="0"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                    <p x-show="selectedCourse && selectedCourse.outstanding > 0" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Suggested: <button type="button" @click="amount = formatMoneyRaw(selectedCourse.outstanding)" class="text-primary-600 hover:underline">full balance</button>
                        <span x-show="selectedCourse.outstanding > 0"> or </span>
                        <button type="button" @click="amount = formatMoneyRaw(Math.min(selectedCourse.outstanding, selectedCourse.total_fee * 0.30))" class="text-primary-600 hover:underline">30% deposit</button>
                    </p>
                    <p x-show="selectedCourse && amount" class="mt-1 text-xs">
                        This will be recorded as
                        <span class="font-medium"
                              :class="(parseFloat(amount || 0) >= (selectedCourse ? selectedCourse.outstanding : 0)) ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400'"
                              x-text="(parseFloat(amount || 0) >= (selectedCourse ? selectedCourse.outstanding : 0)) ? 'a full payment (course_fee)' : 'a partial payment'">
                        </span>.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Payment Method <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method_id" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <option value="">Select Method</option>
                        @foreach($paymentMethods as $method)
                        <option value="{{ $method->payment_method_id }}" {{ old('payment_method_id') == $method->payment_method_id ? 'selected' : '' }}>{{ $method->method_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Payment Status <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_status" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                        <option value="Pending" {{ old('payment_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Completed" {{ old('payment_status', 'Completed') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Failed" {{ old('payment_status') == 'Failed' ? 'selected' : '' }}>Failed</option>
                        <option value="Refunded" {{ old('payment_status') == 'Refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction ID</label>
                <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" maxlength="255"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
                       placeholder="Reference number or transaction ID">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea name="notes" rows="2" maxlength="1000"
                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"
                          placeholder="Optional notes about this payment">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" class="px-4 py-2 od-btn od-btn-primary text-sm">Record Payment</button>
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 od-btn od-btn-ghost text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function paymentForm(config) {
        return {
            students: config.students,
            allCourses: config.allCourses,
            currency: config.currency,
            studentOpen: false,
            courseOpen: false,
            studentSearch: '',
            selectedStudentId: config.oldStudent,
            selectedCourseId: config.oldCourse,
            amount: config.oldAmount || '',
            maxStudentResults: 50,

            init() {
                this.$watch('studentOpen', value => {
                    if (value) {
                        this.$nextTick(() => {
                            this.$refs.studentSearchInput?.focus();
                        });
                    }
                });

                this.$watch('selectedStudentId', () => {
                    this.autoSelectCourse();
                });
            },

            get filteredStudents() {
                const term = this.studentSearch.toLowerCase().trim();
                let list = this.students;

                if (term) {
                    list = this.students.filter(s =>
                        s.name.toLowerCase().includes(term) ||
                        s.email.toLowerCase().includes(term)
                    );
                }

                return list.slice(0, this.maxStudentResults);
            },

            get selectedStudent() {
                return this.students.find(s => s.id === this.selectedStudentId) || null;
            },

            get courseOptions() {
                if (!this.selectedStudent) return [];

                const enrolled = this.selectedStudent.enrollments.map(e => ({
                    ...e,
                    is_enrolled: true,
                }));

                // If student has enrollments, show enrolled courses first; still allow new enrollment.
                const enrolledIds = new Set(enrolled.map(e => e.course_id));
                const otherCourses = this.allCourses
                    .filter(c => !enrolledIds.has(c.id))
                    .map(c => ({
                        course_id: c.id,
                        course_title: c.title,
                        total_fee: parseFloat(c.discount_price ?? c.price ?? 0),
                        amount_paid: 0,
                        discounts: 0,
                        effective_paid: 0,
                        outstanding: parseFloat(c.discount_price ?? c.price ?? 0),
                        payment_status: 'pending',
                        enrollment_status: null,
                        is_fully_paid: false,
                        is_enrolled: false,
                    }));

                return [...enrolled, ...otherCourses];
            },

            get selectedCourse() {
                return this.courseOptions.find(c => c.course_id === this.selectedCourseId) || null;
            },

            selectStudent(student) {
                this.selectedStudentId = student.id;
                this.selectedCourseId = null;
                this.studentSearch = '';
                this.studentOpen = false;
                this.autoSelectCourse();
            },

            selectCourse(course) {
                this.selectedCourseId = course.course_id;
                this.courseOpen = false;

                if (course.outstanding > 0 && !this.amount) {
                    this.amount = this.formatMoneyRaw(course.outstanding);
                }
            },

            autoSelectCourse() {
                if (!this.selectedStudent) return;

                const enrolled = this.selectedStudent.enrollments;
                if (enrolled.length === 1) {
                    this.selectedCourseId = enrolled[0].course_id;
                    if (enrolled[0].outstanding > 0 && !this.amount) {
                        this.amount = this.formatMoneyRaw(enrolled[0].outstanding);
                    }
                } else if (this.oldCourse) {
                    const match = this.courseOptions.find(c => c.course_id === this.oldCourse);
                    if (match) this.selectedCourseId = match.course_id;
                }
            },

            formatMoney(value) {
                return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatMoneyRaw(value) {
                return Number(value || 0).toFixed(2);
            },
        };
    }
</script>
@endpush
@endsection
