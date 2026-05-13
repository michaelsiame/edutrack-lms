<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$studentUser = DB::table('users')->where('email', 'teststudent@edutrack.test')->first();
$instructorUser = DB::table('users')->where('email', 'testinstructor@edutrack.test')->first();
$course = DB::table('courses')->first();

if (!$course) {
    echo "No courses found\n";
    exit;
}

echo "Course: {$course->title} (ID: {$course->id})\n";

// Get the student record
$student = DB::table('students')->where('user_id', $studentUser->id)->first();
if (!$student) {
    echo "No student record found\n";
    exit;
}
echo "Student record ID: {$student->id}\n";

// Enroll test student
$existingEnrollment = DB::table('enrollments')
    ->where('user_id', $studentUser->id)
    ->where('course_id', $course->id)
    ->first();

if (!$existingEnrollment) {
    DB::table('enrollments')->insert([
        'user_id' => $studentUser->id,
        'student_id' => $student->id,
        'course_id' => $course->id,
        'enrollment_status' => 'In Progress',
        'payment_status' => 'completed',
        'progress' => 25,
        'enrolled_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created enrollment for test student\n";
} else {
    echo "Enrollment already exists\n";
}

// Ensure instructor owns the course
$instructor = DB::table('instructors')->where('user_id', $instructorUser->id)->first();
if ($instructor) {
    DB::table('courses')->where('id', $course->id)->update(['instructor_id' => $instructor->id]);
    echo "Assigned course to test instructor (ID: {$instructor->id})\n";
}

// Create a discussion
$existingDiscussion = DB::table('discussions')
    ->where('course_id', $course->id)
    ->first();

if (!$existingDiscussion) {
    $discussionId = DB::table('discussions')->insertGetId([
        'course_id' => $course->id,
        'created_by' => $studentUser->id,
        'title' => 'Welcome to the course!',
        'content' => 'Feel free to ask any questions here.',
        'is_pinned' => true,
        'is_locked' => false,
        'view_count' => 0,
        'reply_count' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created discussion (ID: $discussionId)\n";
} else {
    echo "Discussion already exists\n";
}

// Create a badge
$existingBadge = DB::table('badges')->first();
if (!$existingBadge) {
    DB::table('badges')->insert([
        'badge_name' => 'First Steps',
        'description' => 'Complete your first lesson',
        'badge_icon_url' => 'fas fa-shoe-prints',
        'badge_type' => 'achievement',
        'criteria' => 'complete_first_lesson',
        'points' => 10,
        'is_active' => true,
    ]);
    echo "Created badge\n";
} else {
    echo "Badge already exists\n";
}

// Create a live session (need a lesson first)
$module = DB::table('modules')->where('course_id', $course->id)->first();
if ($module) {
    $lesson = DB::table('lessons')->where('module_id', $module->id)->first();
    if ($lesson) {
        $existingSession = DB::table('live_sessions')->where('lesson_id', $lesson->id)->first();
        if (!$existingSession) {
            DB::table('live_sessions')->insert([
                'lesson_id' => $lesson->id,
                'instructor_id' => $instructor?->id,
                'meeting_room_id' => 'edutrack-test-room',
                'scheduled_start_time' => now()->addDay(),
                'scheduled_end_time' => now()->addDay()->addHour(),
                'status' => 'scheduled',
                'description' => 'Test live session',
                'enable_chat' => true,
                'enable_screen_share' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "Created live session\n";
        } else {
            echo "Live session already exists\n";
        }
    } else {
        echo "No lessons found for course\n";
    }
} else {
    echo "No modules found for course\n";
}

echo "Phase 4 test data setup complete.\n";
