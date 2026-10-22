<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\Payment;
use App\Models\RegistrationFee;
use App\Models\Student;
use App\Models\User;
use App\Services\PaymentVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The registration fee is paid by Lipila mobile money (or a bank deposit that
 * finance approves). Enrollment checks registration_fees, so whatever settles
 * a registration payment must settle its fee too.
 */
class RegistrationFeePaymentTest extends TestCase
{
    use DatabaseTransactions;

    private const STATUS_URL = 'https://lipila.test/api/v1/collections/check-status*';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.lipila.api_key' => 'test-key',
            'services.lipila.webhook_secret' => '',
            'services.lipila.base_url' => 'https://lipila.test',
        ]);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->user = User::create([
            'username' => 'regfee_' . uniqid(),
            'email' => 'regfee+' . uniqid() . '@example.com',
            'password_hash' => bcrypt('secret-password'),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'phone' => '0971000001',
            'status' => 'active',
        ]);

        EmailTemplate::query()->where('template_type', 'Payment')->delete();
        EmailTemplate::create([
            'template_name' => 'payment_confirmation',
            'template_type' => 'Payment',
            'subject' => 'Payment received',
            'body' => 'Hi {{first_name}}, we received {{currency}} {{amount}} for {{course_title}}.',
        ]);
    }

    /** A pending mobile money fee exactly as RegistrationFeeController::store leaves it. */
    private function pendingMobileMoneyFee(): array
    {
        $reference = 'REG-' . $this->user->id . '-' . time() . '-TEST' . random_int(10, 99);

        $payment = Payment::create([
            'student_id' => $this->studentId(),
            'amount' => 150,
            'currency' => 'ZMW',
            'payment_type' => 'registration',
            'payment_status' => 'Pending',
            'transaction_id' => $reference,
            'phone_number' => '260971000001',
        ]);

        $fee = RegistrationFee::create([
            'user_id' => $this->user->id,
            'amount' => 150,
            'currency' => 'ZMW',
            'payment_status' => 'pending',
            'payment_method' => 'mobile_money',
            'reference' => $reference,
            'phone_number' => '260971000001',
        ]);

        return [$payment, $fee];
    }

    private function studentId(): int
    {
        return ($this->user->student ?? Student::create([
            'user_id' => $this->user->id,
            'student_number' => 'TEST-' . $this->user->id,
            'enrollment_date' => now(),
        ]))->id;
    }

    private function receiptsSentTo(string $email): int
    {
        return DB::table('email_queue')->where('recipient', $email)->count();
    }

    public function test_pay_button_no_longer_disables_itself_before_the_form_submits(): void
    {
        $html = $this->actingAs($this->user)->get('/registration-fee')->assertOk()->getContent();

        $this->assertStringNotContainsString('this.disabled = true', $html);
        $this->assertStringContainsString('data-loading-text="Processing..."', $html);
    }

    public function test_a_new_user_without_a_student_record_can_start_a_mobile_money_payment(): void
    {
        $this->assertNull($this->user->student);
        Http::fake(['https://lipila.test/api/v1/collections/mobile-money' => Http::response([
            'identifier' => 'LP-NEW', 'status' => 'Pending',
        ])]);

        $this->actingAs($this->user)->post('/registration-fee', [
            'payment_method' => 'mobile_money',
            'phone_number' => '0771216339',
        ])->assertRedirect(route('registration-fee.show'));

        $fee = RegistrationFee::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNotNull($fee);
        $this->assertSame('pending', $fee->payment_status);
        $this->assertNotNull($this->user->fresh()->student);
        $this->assertSame($this->user->fresh()->student->id,
            Payment::where('transaction_id', $fee->reference)->value('student_id'));

        // The number the student typed is the one charged.
        Http::assertSent(fn ($request) => str_contains($request->url(), '/collections/mobile-money')
            && str_ends_with((string) $request['accountNumber'], '771216339'));
    }

    public function test_wrong_pin_marks_the_fee_failed_and_offers_the_form_again(): void
    {
        [$payment, $fee] = $this->pendingMobileMoneyFee();
        Http::fake([self::STATUS_URL => Http::response([
            'status' => 'Failed', 'type' => 'Collection', 'message' => 'Incorrect pin has been entered.',
        ])]);

        $this->actingAs($this->user)->get('/registration-fee/check')
            ->assertRedirect(route('registration-fee.show'))
            ->assertSessionHas('error');

        $this->assertSame('Failed', $payment->fresh()->payment_status);
        $this->assertSame('failed', $fee->fresh()->payment_status);
        $this->assertStringContainsString('Incorrect pin', (string) $fee->fresh()->notes);

        // Right after the check the flash message explains it; the form is back.
        $html = $this->actingAs($this->user)->get('/registration-fee')->assertOk()->getContent();
        $this->assertStringContainsString('did not go through', $html);
        $this->assertStringContainsString('id="registration-fee-form"', $html);
        $this->assertStringNotContainsString('Payment In Progress', $html);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/collections/check-status')
            && $request['referenceId'] === $payment->transaction_id);
    }

    public function test_opening_the_page_settles_a_failed_payment_without_clicking_check(): void
    {
        [, $fee] = $this->pendingMobileMoneyFee();
        Http::fake([self::STATUS_URL => Http::response(['status' => 'Declined', 'type' => 'Collection'])]);

        $this->actingAs($this->user)->get('/registration-fee')
            ->assertOk()
            ->assertSee('Your last payment did not go through');

        $this->assertSame('failed', $fee->fresh()->payment_status);
    }

    public function test_successful_webhook_unlocks_enrollment_and_emails_a_receipt(): void
    {
        [$payment, $fee] = $this->pendingMobileMoneyFee();
        Http::fake([self::STATUS_URL => Http::response([
            'status' => 'Successful', 'type' => 'Collection', 'identifier' => 'LP-123',
        ])]);

        $this->postJson('/lipila/webhook', [
            'referenceId' => $payment->transaction_id,
            'status' => 'Successful',
            'type' => 'Collection',
        ])->assertOk();

        $this->assertSame('Completed', $payment->fresh()->payment_status);
        $this->assertSame('completed', $fee->fresh()->payment_status);
        $this->assertNotNull($fee->fresh()->verified_at);
        $this->assertTrue(RegistrationFee::where('user_id', $this->user->id)->where('payment_status', 'completed')->exists());

        $this->assertSame(1, $this->receiptsSentTo($this->user->email));
        $this->assertStringContainsString('the registration fee',
            DB::table('email_queue')->where('recipient', $this->user->email)->value('body'));

        // A repeated webhook settles nothing twice and sends no second receipt.
        $this->postJson('/lipila/webhook', [
            'referenceId' => $payment->transaction_id, 'status' => 'Successful', 'type' => 'Collection',
        ])->assertOk();
        $this->assertSame(1, $this->receiptsSentTo($this->user->email));
    }

    public function test_webhook_claiming_success_is_ignored_when_lipila_says_pending(): void
    {
        [$payment, $fee] = $this->pendingMobileMoneyFee();
        Http::fake([self::STATUS_URL => Http::response(['status' => 'Pending', 'type' => 'Collection'])]);

        $this->postJson('/lipila/webhook', [
            'referenceId' => $payment->transaction_id, 'status' => 'Successful', 'type' => 'Collection',
        ])->assertStatus(422);

        $this->assertSame('Pending', $payment->fresh()->payment_status);
        $this->assertSame('pending', $fee->fresh()->payment_status);
        $this->assertSame(0, $this->receiptsSentTo($this->user->email));
    }

    public function test_webhook_for_a_failed_payment_frees_the_student_to_retry(): void
    {
        [$payment, $fee] = $this->pendingMobileMoneyFee();
        Http::fake([self::STATUS_URL => Http::response(['status' => 'Failed', 'type' => 'Collection'])]);

        $this->postJson('/lipila/webhook', [
            'referenceId' => $payment->transaction_id, 'status' => 'Failed', 'type' => 'Collection',
        ])->assertOk();

        $this->assertSame('failed', $fee->fresh()->payment_status);
    }

    public function test_finance_approving_a_bank_deposit_marks_the_fee_paid_and_emails_a_receipt(): void
    {
        $reference = 'REG-MANUAL-' . $this->user->id . '-' . time() . '-TEST';
        $payment = Payment::create([
            'student_id' => $this->studentId(),
            'amount' => 150, 'currency' => 'ZMW', 'payment_type' => 'registration',
            'payment_status' => 'Pending', 'transaction_id' => $reference,
        ]);
        $fee = RegistrationFee::create([
            'user_id' => $this->user->id, 'amount' => 150, 'currency' => 'ZMW',
            'payment_status' => 'pending', 'payment_method' => 'bank_deposit', 'reference' => $reference,
            'bank_reference' => 'SLIP-1', 'deposit_date' => now()->toDateString(),
        ]);

        app(PaymentVerificationService::class)->verifyPayment($payment);

        $this->assertSame('completed', $fee->fresh()->payment_status);
        $this->assertSame(1, $this->receiptsSentTo($this->user->email));
    }
}
