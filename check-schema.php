<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

// Check payments table FKs
echo "=== payments table columns ===\n";
$cols = Schema::getColumns('payments');
foreach ($cols as $c) {
    echo $c['name'] . ' (' . $c['type_name'] . ')' . "\n";
}

echo "\n=== students table columns ===\n";
$cols = Schema::getColumns('students');
foreach ($cols as $c) {
    echo $c['name'] . ' (' . $c['type_name'] . ')' . "\n";
}

echo "\n=== users table columns (relevant) ===\n";
$cols = Schema::getColumns('users');
foreach ($cols as $c) {
    echo $c['name'] . ' (' . $c['type_name'] . ')' . "\n";
}

echo "\n=== registration_fees columns ===\n";
$cols = Schema::getColumns('registration_fees');
foreach ($cols as $c) {
    echo $c['name'] . ' (' . $c['type_name'] . ')' . "\n";
}

echo "\n=== Check FK on payments ===\n";
$fks = Schema::getForeignKeys('payments');
foreach ($fks as $fk) {
    echo "FK: " . $fk['name'] . " -> " . $fk['foreign_table'] . "(" . implode(', ', $fk['foreign_columns']) . ")" . "\n";
    echo "  Local cols: " . implode(', ', $fk['columns']) . "\n";
}

echo "\n=== Sample data ===\n";
$student = App\Models\Student::first();
if ($student) {
    echo "First student: id=" . $student->id . ", user_id=" . $student->user_id . "\n";
} else {
    echo "No students found\n";
}

$user = App\Models\User::find(88);
if ($user) {
    echo "User 88: id=" . $user->id . ", has student? " . ($user->student ? 'yes (id=' . $user->student->id . ')' : 'no') . "\n";
}
