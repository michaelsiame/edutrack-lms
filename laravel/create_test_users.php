<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$password = password_hash('TestPass123!', PASSWORD_BCRYPT);

// Create test users if they don't exist
$users = [
    ['username' => 'testadmin', 'email' => 'testadmin@edutrack.test', 'first_name' => 'Test', 'last_name' => 'Admin', 'role' => 1],
    ['username' => 'testinstructor', 'email' => 'testinstructor@edutrack.test', 'first_name' => 'Test', 'last_name' => 'Instructor', 'role' => 2],
    ['username' => 'teststudent', 'email' => 'teststudent@edutrack.test', 'first_name' => 'Test', 'last_name' => 'Student', 'role' => 4],
];

foreach ($users as $u) {
    $existing = DB::table('users')->where('email', $u['email'])->first();
    if ($existing) {
        DB::table('users')->where('id', $existing->id)->update(['password_hash' => $password, 'status' => 'active']);
        $userId = $existing->id;
        echo "Updated existing user: {$u['username']} (ID: $userId)\n";
    } else {
        $userId = DB::table('users')->insertGetId([
            'username' => $u['username'],
            'email' => $u['email'],
            'password_hash' => $password,
            'first_name' => $u['first_name'],
            'last_name' => $u['last_name'],
            'status' => 'active',
            'email_verified' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created user: {$u['username']} (ID: $userId)\n";
    }

    // Ensure role exists
    $hasRole = DB::table('user_roles')->where('user_id', $userId)->where('role_id', $u['role'])->exists();
    if (!$hasRole) {
        DB::table('user_roles')->insert(['user_id' => $userId, 'role_id' => $u['role']]);
    }

    // Create student record if role is student
    if ($u['role'] == 4) {
        $hasStudent = DB::table('students')->where('user_id', $userId)->exists();
        if (!$hasStudent) {
            DB::table('students')->insert([
                'user_id' => $userId,
                'enrollment_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // Create instructor record if role is instructor
    if ($u['role'] == 2) {
        $hasInstructor = DB::table('instructors')->where('user_id', $userId)->exists();
        if (!$hasInstructor) {
            DB::table('instructors')->insert([
                'user_id' => $userId,
                'bio' => 'Test instructor',
                'is_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

echo "Done. Password for all test users: TestPass123!\n";
