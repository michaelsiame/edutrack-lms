<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'role_name' => 'Admin', 'description' => 'Full system access', 'permissions' => json_encode(['*'])],
            ['id' => 2, 'role_name' => 'Instructor', 'description' => 'Course creation and management', 'permissions' => json_encode(['courses.*', 'lessons.*', 'quizzes.*', 'assignments.*'])],
            ['id' => 3, 'role_name' => 'Finance', 'description' => 'Payment and financial reports', 'permissions' => json_encode(['payments.*', 'reports.*'])],
            ['id' => 4, 'role_name' => 'Student', 'description' => 'Course enrollment and learning', 'permissions' => json_encode(['courses.view', 'enrollments.*'])],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['id' => $role['id']], $role);
        }
    }
}
