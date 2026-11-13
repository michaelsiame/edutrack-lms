<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Payments hang off the student record. A student's user id and student id are
 * different numbers, and "My Payments" used to look payments up by the user id,
 * so every student saw an empty history and K0 paid.
 */
class StudentPaymentsPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_student_sees_their_own_payments_and_total(): void
    {
        DB::table('roles')->insertOrIgnore(['id' => Role::STUDENT, 'role_name' => 'Student']);
        $user = User::create(['username' => 'paytest'.uniqid(), 'email' => 'paytest-'.uniqid().'@example.com',
            'first_name' => 'Pay', 'last_name' => 'Test', 'password_hash' => bcrypt('x'), 'status' => 'active', 'email_verified' => true]);
        UserRole::create(['user_id' => $user->id, 'role_id' => Role::STUDENT]);
        // Push the student id away from the user id, as it is for real students
        $student = Student::create(['user_id' => $user->id, 'student_number' => 'T'.uniqid(), 'enrollment_date' => now()]);
        if ($student->id === $user->id) {
            $student->update(['user_id' => null]);
            $student = Student::create(['user_id' => $user->id, 'student_number' => 'T'.uniqid(), 'enrollment_date' => now()]);
        }
        $this->assertNotSame($user->id, $student->id);

        Payment::create(['student_id' => $student->id, 'amount' => 150, 'currency' => 'ZMW', 'payment_type' => 'registration',
            'payment_status' => 'Completed', 'transaction_id' => 'TEST-'.uniqid()]);

        $this->assertSame(1, $user->payments()->count());
        $this->assertEquals(150, $user->payments()->where('payment_status', 'Completed')->sum('amount'));

        $this->actingAs($user)->get('/student/payments')->assertOk()->assertDontSee('No Payments Found');
    }
}
