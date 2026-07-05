<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Promotion;
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
        $this->baseUrl = rtrim(config('services.lipila.base_url', 'https://api.lipila.dev'), '/');
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

        $payload = [
            'referenceId' => $data['reference'],
            'amount' => (float) $data['amount'],
            'narration' => $data['narration'] ?? 'Edutrack: course payment',
            'accountNumber' => $phoneNumber,
            'currency' => $data['currency'] ?? 'ZMW',
            'email' => $data['email'] ?? '',
        ];

        $callbackUrl = $data['callback_url'] ?? route('lipila.webhook');

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
                'callbackUrl' => $callbackUrl,
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
                'status' => $response->status(),
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

    /**
     * Verify a Lipila collection by our reference ID.
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
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ])->get("{$this->baseUrl}/api/v1/collections/status/{$referenceId}");

            $json = $response->json();

            Log::info('Lipila collection status response', [
                'reference' => $referenceId,
                'status' => $response->status(),
                'response' => $json,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $json,
                    'status' => $json['status'] ?? null,
                ];
            }

            if ($response->status() === 404) {
                Log::warning('Lipila collection status endpoint returned 404', [
                    'reference' => $referenceId,
                    'response' => $json,
                ]);

                return [
                    'success' => false,
                    'error' => 'Status endpoint not found',
                    'status_code' => 404,
                ];
            }

            return [
                'success' => false,
                'error' => 'Unable to verify payment',
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
     * Process a webhook payload from Lipila.
     */
    public function processWebhook(array $payload): bool
    {
        $referenceId = $payload['referenceId'] ?? null;
        $status = $payload['status'] ?? null;
        $type = $payload['type'] ?? null;

        if (empty($referenceId) || empty($status) || empty($type)) {
            Log::warning('Invalid Lipila webhook payload', $payload);

            return false;
        }

        $payment = Payment::where('transaction_id', $referenceId)->first();

        if (!$payment) {
            Log::warning('Lipila payment not found for reference', ['reference_id' => $referenceId]);

            return false;
        }

        // Defence-in-depth: re-verify the collection status server-side before marking paid.
        $canTrustWebhook = false;
        $verificationResult = $this->isConfigured() ? $this->verifyPayment($referenceId) : ['success' => false];

        if ($verificationResult['success'] && !empty($verificationResult['data'])) {
            $verifiedStatus = $verificationResult['data']['status'] ?? null;
            $verifiedType = $verificationResult['data']['type'] ?? null;
            $isVerifiedSuccessful = strcasecmp($verifiedStatus, 'Successful') === 0
                && strcasecmp($verifiedType, 'Collection') === 0;

            if ($isVerifiedSuccessful) {
                $canTrustWebhook = true;
            } else {
                Log::warning('Lipila webhook failed server-side verification', [
                    'reference_id' => $referenceId,
                    'webhook_status' => $status,
                    'verified_status' => $verifiedStatus,
                    'verified_type' => $verifiedType,
                ]);

                return false;
            }
        } elseif (($verificationResult['status_code'] ?? 0) === 404) {
            Log::warning('Lipila status endpoint 404; falling back to signed webhook', [
                'reference_id' => $referenceId,
            ]);
            $canTrustWebhook = true;
        } elseif (!$this->isConfigured()) {
            $canTrustWebhook = true;
        } else {
            Log::warning('Lipila status verification unavailable; falling back to signed webhook', [
                'reference_id' => $referenceId,
                'error' => $verificationResult['error'] ?? 'Unknown',
            ]);
            $canTrustWebhook = true;
        }

        $isSuccessfulCollection = strcasecmp($status, 'Successful') === 0
            && strcasecmp($type, 'Collection') === 0;

        if (!$isSuccessfulCollection) {
            Log::info('Lipila webhook not a successful collection', [
                'reference_id' => $referenceId,
                'status' => $status,
                'type' => $type,
            ]);

            return true;
        }

        if (!$canTrustWebhook) {
            Log::warning('Lipila webhook accepted but not independently verified', [
                'reference_id' => $referenceId,
            ]);
        }

        return $this->markPaymentCompleted($payment, $payload);
    }

    /**
     * Mark a payment as completed and sync enrollment state.
     */
    protected function markPaymentCompleted(Payment $payment, array $payload): bool
    {
        $wasCompleted = $payment->isCompleted();

        $payment->update([
            'payment_status' => 'Completed',
            'payment_date' => now(),
            'lipila_reference' => $payload['identifier'] ?? $payment->lipila_reference,
            'phone_number' => $payload['accountNumber'] ?? $payment->phone_number,
        ]);

        $verificationService = app(PaymentVerificationService::class);

        if ($payment->enrollment) {
            $verificationService->recalculateEnrollmentState($payment->enrollment);
        }

        if (!$wasCompleted) {
            if ($payment->promotion_id) {
                Promotion::where('id', $payment->promotion_id)->increment('used_count');
            }

            try {
                $invoiceService = app(InvoiceService::class);
                $invoiceService->generateInvoice($payment);
            } catch (\Exception $e) {
                Log::error('Failed to generate invoice for completed Lipila payment', [
                    'payment_id' => $payment->payment_id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $emailService = app(\App\Services\EmailQueueService::class);
                $user = $payment->enrollment?->user ?? $payment->student?->user;
                $userId = $user?->id ?? $payment->student?->user_id;

                if ($user && $user->email) {
                    $emailService->sendTemplated($user->email, 'Payment', [
                        'name' => $user->full_name,
                        'course' => $payment->course?->title,
                        'amount' => number_format($payment->amount, 2),
                        'date' => $payment->payment_date?->format('F d, Y'),
                    ]);
                }

                if ($userId) {
                    $emailService->sendNotification($userId, 'Payment Received', "Your payment of ZMW {$payment->amount} for {$payment->course?->title} has been received.", 'payment');
                }
            } catch (\Exception $e) {
                Log::error('Failed to send payment receipt email for Lipila payment', [
                    'payment_id' => $payment->payment_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Lipila payment marked completed', [
            'payment_id' => $payment->payment_id,
            'reference_id' => $payment->transaction_id,
            'lipila_reference' => $payment->lipila_reference,
        ]);

        return true;
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
