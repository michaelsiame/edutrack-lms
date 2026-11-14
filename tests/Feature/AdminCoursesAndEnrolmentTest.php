<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admins could not create a course (no button, and a web address to make up), and enrolling someone into a
 * course with no intakes failed with "Please choose a valid intake for this course."
 */
class AdminCoursesAndEnrolmentTest extends TestCase
{
    use DatabaseTransactions;

    private function person(int $role, string $name): User
    {
        DB::table('roles')->insertOrIgnore(['id' => $role, 'role_name' => $name]);
        $user = User::create(['username' => strtolower($name).uniqid(), 'email' => strtolower($name).'-'.uniqid().'@example.com',
            'first_name' => $name, 'last_name' => 'Test', 'password_hash' => bcrypt('x'), 'status' => 'active', 'email_verified' => true]);
        UserRole::create(['user_id' => $user->id, 'role_id' => $role]);

        return $user;
    }

    public function test_an_admin_creates_a_course_and_its_web_address_comes_from_the_title(): void
    {
        $admin = $this->person(Role::ADMIN, 'Admin');
        $instructor = Instructor::create(['user_id' => $this->person(Role::INSTRUCTOR, 'Teacher')->id, 'is_verified' => true]);
        $category = CourseCategory::first() ?? CourseCategory::create(['name' => 'Test category', 'slug' => 'test-category-'.uniqid()]);
        $title = 'Admin made course '.uniqid();

        $this->actingAs($admin)->get('/admin/courses')->assertOk()->assertSee('New course');
        $this->actingAs($admin)->post('/admin/courses', ['title' => $title, 'category_id' => $category->id,
            'instructor_id' => $instructor->id, 'price' => 500, 'status' => 'draft'])->assertRedirect();

        $course = Course::where('title', $title)->firstOrFail();
        $this->assertSame(\Illuminate\Support\Str::slug($title), $course->slug);
    }

    public function test_an_admin_enrols_someone_into_a_course_with_no_intakes(): void
    {
        $admin = $this->person(Role::ADMIN, 'Admin');
        $student = $this->person(Role::STUDENT, 'Learner');
        $instructor = Instructor::create(['user_id' => $this->person(Role::INSTRUCTOR, 'Teacher')->id, 'is_verified' => true]);
        $category = CourseCategory::first() ?? CourseCategory::create(['name' => 'Test category', 'slug' => 'test-category-'.uniqid()]);
        $course = Course::create(['title' => 'No intakes yet', 'slug' => 'no-intakes-'.uniqid(), 'category_id' => $category->id,
            'price' => 0, 'status' => 'published', 'instructor_id' => $instructor->id]);

        $this->actingAs($admin)->post('/admin/enrollments', ['user_id' => $student->id, 'course_id' => $course->id,
            'mode' => 'in_person', 'funding_source' => 'self'])->assertSessionMissing('error');

        $enrolment = Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $this->assertSame('Open Enrollment', $enrolment->intake?->name);
    }
}
