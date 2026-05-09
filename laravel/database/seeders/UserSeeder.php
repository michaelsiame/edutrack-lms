<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@edutrack.com',
                'password_hash' => Hash::make('password123'),
                'first_name' => 'System',
                'last_name' => 'Admin',
                'phone' => '+260770000001',
                'status' => 'active',
                'email_verified' => true,
            ],
            [
                'id' => 2,
                'username' => 'instructor',
                'email' => 'instructor@edutrack.com',
                'password_hash' => Hash::make('password123'),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+260770000002',
                'status' => 'active',
                'email_verified' => true,
            ],
            [
                'id' => 3,
                'username' => 'finance',
                'email' => 'finance@edutrack.com',
                'password_hash' => Hash::make('password123'),
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '+260770000003',
                'status' => 'active',
                'email_verified' => true,
            ],
            [
                'id' => 4,
                'username' => 'student',
                'email' => 'student@edutrack.com',
                'password_hash' => Hash::make('password123'),
                'first_name' => 'Michael',
                'last_name' => 'Banda',
                'phone' => '+260770000004',
                'status' => 'active',
                'email_verified' => true,
            ],
            [
                'id' => 5,
                'username' => 'student2',
                'email' => 'student2@edutrack.com',
                'password_hash' => Hash::make('password123'),
                'first_name' => 'Sarah',
                'last_name' => 'Mulenga',
                'phone' => '+260770000005',
                'status' => 'active',
                'email_verified' => true,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(['id' => $user['id']], $user);
        }

        // Assign roles
        $userRoles = [
            ['user_id' => 1, 'role_id' => 1],
            ['user_id' => 2, 'role_id' => 2],
            ['user_id' => 3, 'role_id' => 3],
            ['user_id' => 4, 'role_id' => 4],
            ['user_id' => 5, 'role_id' => 4],
        ];

        foreach ($userRoles as $role) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $role['user_id'], 'role_id' => $role['role_id']],
                $role
            );
        }

        // Create user profiles
        $profiles = [
            ['user_id' => 1, 'date_of_birth' => '1990-01-01', 'gender' => 'male', 'city' => 'Kalomo', 'country' => 'Zambia'],
            ['user_id' => 2, 'date_of_birth' => '1985-05-15', 'gender' => 'male', 'city' => 'Lusaka', 'country' => 'Zambia'],
            ['user_id' => 3, 'date_of_birth' => '1988-08-20', 'gender' => 'female', 'city' => 'Livingstone', 'country' => 'Zambia'],
            ['user_id' => 4, 'date_of_birth' => '2000-03-10', 'gender' => 'male', 'city' => 'Kalomo', 'country' => 'Zambia'],
            ['user_id' => 5, 'date_of_birth' => '2002-07-25', 'gender' => 'female', 'city' => 'Choma', 'country' => 'Zambia'],
        ];

        foreach ($profiles as $profile) {
            DB::table('user_profiles')->updateOrInsert(['user_id' => $profile['user_id']], $profile);
        }
    }
}
