<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateLegacyData extends Seeder
{
    protected string $sourceDb = 'edutrack_legacy';
    protected int $batchSize = 500;

    /**
     * Run the data migration.
     */
    public function run(): void
    {
        $this->command->info('Starting legacy data migration...');

        // Verify source database exists
        $databases = DB::select('SHOW DATABASES');
        $dbNames = array_column($databases, 'Database');

        if (!in_array($this->sourceDb, $dbNames)) {
            $this->command->error("Source database '{$this->sourceDb}' not found. Skipping migration.");
            return;
        }

        $this->migrateUsers();
        $this->migrateRoles();
        $this->migrateCourses();
        $this->migrateEnrollments();
        $this->migratePayments();
        $this->migrateCertificates();
        $this->migrateQuizzesAndAttempts();

        $this->command->info('Legacy data migration completed!');
    }

    protected function migrateUsers(): void
    {
        $this->command->info('Migrating users...');

        $count = DB::table("{$this->sourceDb}.users")->count();
        $this->command->info("Found {$count} users");

        DB::table("{$this->sourceDb}.users")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($users) {
                foreach ($users as $user) {
                    try {
                        DB::table('users')->updateOrInsert(
                            ['id' => $user->id],
                            [
                                'name' => $user->name ?? $user->first_name . ' ' . ($user->last_name ?? ''),
                                'email' => $user->email,
                                'password_hash' => $user->password_hash ?? $user->password ?? '',
                                'email_verified_at' => $user->email_verified_at ?? null,
                                'remember_token' => $user->remember_token ?? null,
                                'created_at' => $user->created_at ?? now(),
                                'updated_at' => $user->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate user {$user->id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('Users migrated.');
    }

    protected function migrateRoles(): void
    {
        $this->command->info('Migrating user roles...');

        DB::table("{$this->sourceDb}.user_roles")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($roles) {
                foreach ($roles as $role) {
                    try {
                        DB::table('user_roles')->updateOrInsert(
                            ['id' => $role->id],
                            [
                                'user_id' => $role->user_id,
                                'role_id' => $role->role_id,
                                'created_at' => $role->created_at ?? now(),
                                'updated_at' => $role->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate role {$role->id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('User roles migrated.');
    }

    protected function migrateCourses(): void
    {
        $this->command->info('Migrating courses, modules, and lessons...');

        // Courses
        DB::table("{$this->sourceDb}.courses")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($courses) {
                foreach ($courses as $course) {
                    try {
                        DB::table('courses')->updateOrInsert(
                            ['id' => $course->id],
                            [
                                'title' => $course->title,
                                'slug' => $course->slug ?? \Illuminate\Support\Str::slug($course->title),
                                'description' => $course->description ?? '',
                                'instructor_id' => $course->instructor_id ?? null,
                                'category_id' => $course->category_id ?? null,
                                'price' => $course->price ?? 0,
                                'status' => $course->status ?? 'draft',
                                'created_at' => $course->created_at ?? now(),
                                'updated_at' => $course->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate course {$course->id}: " . $e->getMessage());
                    }
                }
            });

        // Modules
        DB::table("{$this->sourceDb}.modules")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($modules) {
                foreach ($modules as $module) {
                    try {
                        DB::table('modules')->updateOrInsert(
                            ['id' => $module->id],
                            [
                                'course_id' => $module->course_id,
                                'title' => $module->title,
                                'description' => $module->description ?? '',
                                'sort_order' => $module->sort_order ?? 0,
                                'created_at' => $module->created_at ?? now(),
                                'updated_at' => $module->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate module {$module->id}: " . $e->getMessage());
                    }
                }
            });

        // Lessons
        DB::table("{$this->sourceDb}.lessons")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($lessons) {
                foreach ($lessons as $lesson) {
                    try {
                        DB::table('lessons')->updateOrInsert(
                            ['id' => $lesson->id],
                            [
                                'module_id' => $lesson->module_id,
                                'title' => $lesson->title,
                                'content' => $lesson->content ?? '',
                                'video_url' => $lesson->video_url ?? null,
                                'duration_minutes' => $lesson->duration_minutes ?? 0,
                                'sort_order' => $lesson->sort_order ?? 0,
                                'created_at' => $lesson->created_at ?? now(),
                                'updated_at' => $lesson->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate lesson {$lesson->id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('Courses, modules, and lessons migrated.');
    }

    protected function migrateEnrollments(): void
    {
        $this->command->info('Migrating enrollments...');

        DB::table("{$this->sourceDb}.enrollments")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($enrollments) {
                foreach ($enrollments as $enrollment) {
                    try {
                        DB::table('enrollments')->updateOrInsert(
                            ['id' => $enrollment->id],
                            [
                                'user_id' => $enrollment->user_id,
                                'course_id' => $enrollment->course_id,
                                'status' => $enrollment->status ?? 'active',
                                'enrolled_at' => $enrollment->enrolled_at ?? $enrollment->created_at ?? now(),
                                'completed_at' => $enrollment->completed_at ?? null,
                                'final_grade' => $enrollment->final_grade ?? null,
                                'created_at' => $enrollment->created_at ?? now(),
                                'updated_at' => $enrollment->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate enrollment {$enrollment->id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('Enrollments migrated.');
    }

    protected function migratePayments(): void
    {
        $this->command->info('Migrating payments...');

        DB::table("{$this->sourceDb}.payments")
            ->orderBy('payment_id')
            ->chunk($this->batchSize, function ($payments) {
                foreach ($payments as $payment) {
                    try {
                        DB::table('payments')->updateOrInsert(
                            ['payment_id' => $payment->payment_id],
                            [
                                'user_id' => $payment->user_id,
                                'course_id' => $payment->course_id ?? null,
                                'enrollment_id' => $payment->enrollment_id ?? null,
                                'amount' => $payment->amount ?? 0,
                                'currency' => $payment->currency ?? 'ZMW',
                                'payment_method' => $payment->payment_method ?? 'manual',
                                'payment_status' => $payment->payment_status ?? 'Pending',
                                'transaction_reference' => $payment->transaction_reference ?? null,
                                'paid_at' => $payment->paid_at ?? null,
                                'created_at' => $payment->created_at ?? now(),
                                'updated_at' => $payment->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate payment {$payment->payment_id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('Payments migrated.');
    }

    protected function migrateCertificates(): void
    {
        $this->command->info('Migrating certificates...');

        DB::table("{$this->sourceDb}.certificates")
            ->orderBy('certificate_id')
            ->chunk($this->batchSize, function ($certificates) {
                foreach ($certificates as $cert) {
                    try {
                        DB::table('certificates')->updateOrInsert(
                            ['certificate_id' => $cert->certificate_id],
                            [
                                'certificate_number' => $cert->certificate_number ?? 'CERT-' . strtoupper(uniqid()),
                                'enrollment_id' => $cert->enrollment_id,
                                'user_id' => $cert->user_id ?? null,
                                'course_id' => $cert->course_id ?? null,
                                'final_score' => $cert->final_score ?? null,
                                'issued_at' => $cert->issued_at ?? $cert->created_at ?? now(),
                                'pdf_path' => $cert->pdf_path ?? null,
                                'created_at' => $cert->created_at ?? now(),
                                'updated_at' => $cert->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate certificate {$cert->certificate_id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('Certificates migrated.');
    }

    protected function migrateQuizzesAndAttempts(): void
    {
        $this->command->info('Migrating quizzes and attempts...');

        // Quizzes
        DB::table("{$this->sourceDb}.quizzes")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($quizzes) {
                foreach ($quizzes as $quiz) {
                    try {
                        DB::table('quizzes')->updateOrInsert(
                            ['id' => $quiz->id],
                            [
                                'course_id' => $quiz->course_id,
                                'title' => $quiz->title,
                                'description' => $quiz->description ?? '',
                                'passing_score' => $quiz->passing_score ?? 60,
                                'time_limit' => $quiz->time_limit ?? null,
                                'is_published' => $quiz->is_published ?? false,
                                'created_at' => $quiz->created_at ?? now(),
                                'updated_at' => $quiz->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate quiz {$quiz->id}: " . $e->getMessage());
                    }
                }
            });

        // Questions
        DB::table("{$this->sourceDb}.questions")
            ->orderBy('question_id')
            ->chunk($this->batchSize, function ($questions) {
                foreach ($questions as $question) {
                    try {
                        DB::table('questions')->updateOrInsert(
                            ['question_id' => $question->question_id],
                            [
                                'quiz_id' => $question->quiz_id,
                                'question_text' => $question->question_text,
                                'question_type' => $question->question_type ?? 'multiple_choice',
                                'options' => $question->options ?? null,
                                'correct_answer' => $question->correct_answer ?? null,
                                'points' => $question->points ?? 1,
                                'sort_order' => $question->sort_order ?? 0,
                                'created_at' => $question->created_at ?? now(),
                                'updated_at' => $question->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate question {$question->question_id}: " . $e->getMessage());
                    }
                }
            });

        // Quiz Attempts
        DB::table("{$this->sourceDb}.quiz_attempts")
            ->orderBy('id')
            ->chunk($this->batchSize, function ($attempts) {
                foreach ($attempts as $attempt) {
                    try {
                        DB::table('quiz_attempts')->updateOrInsert(
                            ['id' => $attempt->id],
                            [
                                'quiz_id' => $attempt->quiz_id,
                                'user_id' => $attempt->user_id ?? $attempt->student_id ?? null,
                                'score' => $attempt->score ?? $attempt->total_score ?? 0,
                                'status' => $attempt->status ?? 'Submitted',
                                'started_at' => $attempt->started_at ?? now(),
                                'submitted_at' => $attempt->submitted_at ?? null,
                                'created_at' => $attempt->created_at ?? now(),
                                'updated_at' => $attempt->updated_at ?? now(),
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to migrate quiz attempt {$attempt->id}: " . $e->getMessage());
                    }
                }
            });

        $this->command->info('Quizzes and attempts migrated.');
    }
}
