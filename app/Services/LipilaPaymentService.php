<?php

namespace App\Services;

use App\Models\Payment;
use App\Services\PaymentVerificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LipilaPaymentService
{
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.lipila.api_key', env('LIPILA_API_KEY'));
        $this->webhookSecret = config('services.lipila.webhook_secret', env('LIPILA_WEBHOOK_SECRET'));
        $this->baseUrl = rtrim(config('services.lipila.base_url', 'https://blz.lipila.io'), '/');
    }

    /**
     * Verify that Lipila credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Initialize a Lipila mobile money collection request.
     */
    public function initiateMobileMoneyCollection(array $data): array
    {
        if (!$this->isConfigured()) {
            Log::error('Lipila API credentials not configured');

            return [
                'success' => false,
                'error' => 'Payment gateway is not configured. Please contact support.',
            ];
        }

        $phoneNumber = $this->normalizePhoneNumber($data['phone_number'] ?? '');

        if (empty($phoneNumber)) {
            return [
                'success' => false,
                'error' => 'A valid Zambian phone number is required for mobile money payment.',
            ];
        }

        $operator = $this->detectMobileOperator($phoneNumber);

        if (empty($operator)) {
            return [
                'success' => false,
                'error' => 'Could not detect mobile network operator. Please provide a valid Zambian phone number.',
            ];
        }

        $callbackUrl = $data['callback_url'] ?? route('lipila.webhook');

        // Per Lipila docs, callbackUrl is a BODY field (not a header).
        $payload = [
            'callbackUrl' => $callbackUrl,
            'referenceId' => $data['reference'],
            'amount' => (float) $data['amount'],
            'narration' => $data['narration'] ?? 'Edutrack: course payment',
            'accountNumber' => $phoneNumber,
            'currency' => $data['currency'] ?? 'ZMW',
            'email' => $data['email'] ?? '',
        ];

        try {
            Log::info('Lipila mobile money collection request', [
                'reference' => $data['reference'],
                'amount' => $payload['amount'],
                'account_number' => $phoneNumber,
            ]);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/collections/mobile-money", $payload);

            $json = $response->json();

            Log::info('Lipila mobile money collection response', [
                'reference' => $data['reference'],
                'status' => $response->status(),
                'response' => $json,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'reference' => $data['reference'],
                    'identifier' => $json['identifier'] ?? null,
                    'status' => $json['status'] ?? 'Pending',
                    'raw' => $json,
                ];
            }

            Log::error('Lipila mobile money initialization failed', [
                'response' => $json,
                'raw_body' => $response->body(),
                'status' => $response->status(),
                'api_key_prefix' => substr((string) $this->apiKey, 0, 8),
                'base_url' => $this->baseUrl,
            ]);

            return [
                'success' => false,
                'error' => $json['message'] ?? 'Payment initialization failed. Please try again.',
                'raw' => $json,
            ];
        } catch (\Exception $e) {
            Log::error('Lipila mobile money exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Payment service unavailable. Please try again.',
            ];
        }
    }

    /** Lipila statuses that mean the money arrived. */
    private const SUCCESS_STATUSES = ['successful', 'success', 'completed', 'complete', 'paid'];

    /** Lipila statuses that mean "still in flight". Anything else it reports is final. */
    private const PENDING_STATUSES = ['pending', 'processing', 'submitted', 'queued', 'inprogress', 'in_progress', 'initiated'];

    /**
     * Map a Lipila status to 'successful', 'pending' or 'failed'. Lipila reports
     * more statuses than it documents (Cancelled, Declined, Expired, Timeout ...);
     * all of those are final failures.
     */
    public static function normalizeStatus(?string $status): ?string
    {
        $s = strtolower(trim((string) $status));
        if ($s === '') {
            return null;
        }
        if (in_array($s, self::SUCCESS_STATUSES, true)) {
            return 'successful';
        }

        return in_array($s, self::PENDING_STATUSES, true) ? 'pending' : 'failed';
    }

    /**
     * Ask Lipila for a collection's status by our reference ID.
     * Endpoint: GET /api/v1/collections/check-status?referenceId=...
     */
    public function verifyPayment(string $referenceId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Payment gateway is not configured.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'accept' => 'application/json',
            ])->timeout(30)->get("{$this->baseUrl}/api/v1/collections/check-status", [
                'referenceId' => $referenceId,
            ]);

            $json = $response->json();

            Log::info('Lipila collection status response', [
                'reference' => $referenceId,
                'status' => $response->status(),
                'response' => $json,
            ]);

            if ($response->successful() && is_array($json)) {
                return [
                    'success' => true,
                    'data' => $json,
                    'status' => self::normalizeStatus($json['status'] ?? null),
                    'message' => $json['message'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Unable to verify payment',
                'status_code' => $response->status(),
                'raw' => $json,
            ];
        } catch (\Exception $e) {
            Log::error('Lipila verify exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Verification service unavailable',
            ];
        }
    }

    /**
     * Actively poll a pending payment and sync its state. Used by the
     * front-end status endpoint so the "processing" page resolves even if
     * the webhook is delayed or never arrives. Returns a lowercase status:
     * 'completed' | 'failed' | 'pending'.
     */
    public function pollAndSync(Payment $payment): string
    {
        if ($payment->isCompleted()) {
            return 'completed';
        }

        if (in_array(strtolower((string) $payment->payment_status), ['failed', 'cancelled'], true)) {
            return 'failed';
        }

        if (!$this->isConfigured() || empty($payment->transaction_id)) {
            return 'pending';
        }

        $result = $this->verifyPayment($payment->transaction_id);

        if (empty($result['success'])) {
            return 'pending';
        }

        if ($result['status'] === 'successful') {
            $this->markPaymentCompleted($payment, $result['data']);

            return 'completed';
        }

        if ($result['status'] === 'failed') {
            $this->markPaymentFailed($payment, $result['message'] ?? null);

            return 'failed';
        }

        return 'pending';
    }

    /**
     * Process a webhook payload from Lipila. The payload is only a hint: the
     * status is always confirmed with Lipila before a payment is settled.
     * Returns false when nothing could be settled, so Lipila retries.
     */
    public function processWebhook(array $payload): bool
    {
        $referenceId = $payload['referenceId'] ?? null;
        $identifier = $payload['identifier'] ?? null;

        if (empty($referenceId) && empty($identifier)) {
            Log::warning('Invalid Lipila webhook payload', $payload);

            return false;
        }

        // Lipila is inconsistent about which field carries our reference and
        // which its own, so try both.
        $candidates = array_values(array_filter([$referenceId, $identifier]));
        $payment = Payment::whereIn('transaction_id', $candidates)
            ->orWhereIn('lipila_reference', $candidates)
            ->latest('created_at')
            ->first();

        if (!$payment) {
            Log::warning('Lipila payment not found for reference', ['reference_id' => $referenceId, 'identifier' => $identifier]);

            return false;
        }

        $state = $this->pollAndSync($payment);

        Log::info('Lipila webhook processed', [
            'reference_id' => $payment->transaction_id,
            'webhook_status' => $payload['status'] ?? null,
            'settled_as' => $state,
        ]);

        return $state !== 'pending';
    }

    /**
     * Mark a payment as completed and sync enrollment state.
     */
    protected function markPaymentCompleted(Payment $payment, array $payload): bool
    {
        $wasCompleted = $payment->isCompleted();

        $payment->update([
            'payment_status' => 'Completed',
            'payment_date' => $payment->payment_date ?? now(),
            'lipila_reference' => $payload['identifier'] ?? $payment->lipila_reference,
            'phone_number' => $payload['accountNumber'] ?? $payment->phone_number,
        ]);

        $verificationService = app(PaymentVerificationService::class);

        if ($payment->enrollment) {
            $verificationService->recalculateEnrollmentState($payment->enrollment);
        }

        // A registration payment unlocks enrollment through its RegistrationFee.
        $verificationService->syncRegistrationFee($payment);

        // Delegate promotion usage, invoice generation and receipt notification
        // to the shared completion path so Lipila completions behave identically
        // to Lenco completions and manual/admin verifications.
        if (!$wasCompleted) {
            $verificationService->runCompletionSideEffects($payment);
        }

        Log::info('Lipila payment marked completed', [
            'payment_id' => $payment->payment_id,
            'reference_id' => $payment->transaction_id,
            'lipila_reference' => $payment->lipila_reference,
        ]);

        return true;
    }

    /**
     * Mark a payment as failed (wrong PIN, cancelled, expired ...) so the
     * student is offered the payment form again.
     */
    protected function markPaymentFailed(Payment $payment, ?string $reason = null): void
    {
        $payment->update(['payment_status' => 'Failed']);

        app(PaymentVerificationService::class)
            ->syncRegistrationFee($payment, $reason ? 'Mobile money failed: ' . $reason : null);

        Log::info('Lipila payment marked failed', [
            'payment_id' => $payment->payment_id,
            'reference_id' => $payment->transaction_id,
            'reason' => $reason,
        ]);
    }

    /**
     * Validate webhook signature if Lipila provides one.
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.lipila.webhook_secret');

        if (empty($secret) || empty($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }

    /**
     * Detect mobile network operator from Zambian phone number.
     */
    protected function detectMobileOperator(string $phone): ?string
    {
        $normalized = $this->normalizePhoneNumber($phone);

        if (empty($normalized) || strlen($normalized) < 5) {
            return null;
        }

        $prefix = substr($normalized, 3, 2);

        $mtnPrefixes = ['76', '96'];
        $airtelPrefixes = ['57', '77', '97'];
        $zamtelPrefixes = ['75', '95'];

        if (in_array($prefix, $mtnPrefixes)) {
            return 'mtn';
        }

        if (in_array($prefix, $airtelPrefixes)) {
            return 'airtel';
        }

        if (in_array($prefix, $zamtelPrefixes)) {
            return 'zamtel';
        }

        return null;
    }

    /**
     * Normalize phone number to MSISDN format (260XXXXXXXXX).
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return '';
        }

        // If starts with 0, replace with 260
        if (str_starts_with($phone, '0')) {
            $phone = '260' . substr($phone, 1);
        }

        // If starts with 2609/2607/2605 but not 260, prepend 260
        if (!str_starts_with($phone, '260')) {
            $phone = '260' . $phone;
        }

        return $phone;
    }
}
