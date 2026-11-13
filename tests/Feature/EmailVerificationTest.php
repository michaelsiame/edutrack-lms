<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * A student who signs up with email and password must be able to verify the
 * address and log in. The token used to be dropped by mass assignment, so the
 * link in the email never matched and login refused the account forever.
 */
class EmailVerificationTest extends TestCase
{
    use DatabaseTransactions;

    private function register(): User
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // The test schema starts with no roles; registration gives every new user the Student role
        \Illuminate\Support\Facades\DB::table('roles')->insertOrIgnore(['id' => \App\Models\Role::STUDENT, 'role_name' => 'Student']);
        $this->post('/register', [
            'first_name' => 'Test', 'last_name' => 'Student', 'username' => 'verifytest'.uniqid(),
            'email' => 'verify-'.uniqid().'@example.com', 'phone' => '0970000000',
            'password' => 'Learn2026x', 'password_confirmation' => 'Learn2026x',
        ])->assertRedirect(route('verification.notice'));

        return User::latest('id')->firstOrFail();
    }

    public function test_registration_stores_a_verification_token(): void
    {
        $user = $this->register();

        $this->assertSame(64, strlen((string) $user->email_verification_token));
        $this->assertTrue($user->email_verification_expires->isFuture());
        $this->assertFalse($user->email_verified);
    }

    public function test_the_link_verifies_the_account_and_login_then_works(): void
    {
        $user = $this->register();

        $this->get('/verify-email/'.$user->email_verification_token)->assertRedirect(route('login'));
        $user->refresh();
        $this->assertTrue($user->email_verified);
        $this->assertNull($user->email_verification_token);

        $this->post('/login', ['email' => $user->email, 'password' => 'Learn2026x'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
