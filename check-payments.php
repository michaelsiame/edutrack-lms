<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Total payments: ' . App\Models\Payment::count() . "\n";
echo 'Payments with student_id not in users: ' . App\Models\Payment::whereNotIn('student_id', App\Models\User::pluck('id'))->count() . "\n";
echo 'Payments with student_id not in students: ' . App\Models\Payment::whereNotIn('student_id', App\Models\Student::pluck('id'))->count() . "\n";

$bad = App\Models\Payment::whereNotIn('student_id', App\Models\Student::pluck('id'))->first();
if ($bad) {
    echo "Example bad payment: id={$bad->payment_id}, student_id={$bad->student_id}\n";
    $student = App\Models\Student::where('user_id', $bad->student_id)->first();
    if ($student) {
        echo "  -> Should be student_id={$student->id} (user_id={$bad->student_id})\n";
    }
}
