<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Instructor;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The course builder: every new course starts with a module that has no lessons yet. The
 * "Add lesson" form borrowed $lesson from the lesson loop, so that page crashed with
 * "Undefined variable $lesson" until the first lesson existed.
 */
class CourseBuilderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_new_course_with_an_empty_module_opens_in_the_builder(): void
    {
        DB::table('roles')->insertOrIgnore(['id' => Role::INSTRUCTOR, 'role_name' => 'Instructor']);
        $user = User::create(['username' => 'buildtest'.uniqid(), 'email' => 'buildtest-'.uniqid().'@example.com',
            'first_name' => 'Build', 'last_name' => 'Test', 'password_hash' => bcrypt('x'), 'status' => 'active', 'email_verified' => true]);
        UserRole::create(['user_id' => $user->id, 'role_id' => Role::INSTRUCTOR]);
        $instructor = Instructor::create(['user_id' => $user->id, 'is_verified' => true]);
        $category = CourseCategory::first() ?? CourseCategory::create(['name' => 'Test category', 'slug' => 'test-category-'.uniqid()]);

        $course = Course::create(['title' => 'Builder test', 'slug' => 'builder-test-'.uniqid(), 'category_id' => $category->id,
            'price' => 100, 'status' => 'draft', 'instructor_id' => $instructor->id]);
        $course->modules()->create(['title' => 'First module', 'display_order' => 1]);

        $this->actingAs($user)->get("/instructor/courses/{$course->id}")
            ->assertOk()->assertSee('First module')->assertSee('Add Lesson');
    }
}
