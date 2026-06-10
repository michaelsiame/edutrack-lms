<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\LencoTransaction;
use App\Models\Payment;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\RSA;

class LencoPaymentService
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('app.env') !== 'production';
        $this->apiKey = config('services.lenco.api_key', env('LENCO_API_KEY'));
        $this->secretKey = config('services.lenco.secret_key', env('LENCO_SECRET_KEY'));
        $this->baseUrl = 'https://api.lenco.co';
    }

    /**
     * Verify that Lenco credentials are configured.
     */
    protected function credentialsConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    /**
     * Initialize a mobile money collection request.
     */
    public function initializeMobileMoneyCollection(array $data): array
    {
        if (!$this->credentialsConfigured()) {
            Log::error('Lenco API credentials not configured');
            return [
                'success' => false,
                'error' => 'Payment gateway is not configured. Please contact support.',
            ];
        }

        $operator = $data['operator'] ?? $this->detectMobileOperator($data['phone_number'] ?? '');

        if (empty($operator)) {
            return [
                'success' => false,
                'error' => 'Could not detect mobile network operator. Please provide a valid Zambian phone number.',
            ];
        }

        // Lenco only supports airtel and mtn for Zambia mobile money
        if (!in_array($operator, ['airtel', 'mtn'])) {
            return [
                'success' => false,
                'error' => 'Unsupported mobile network operator. Lenco supports Airtel and MTN for mobile money in Zambia.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/access/v2/collections/mobile-money", [
                'amount' => (string) $data['amount'],
                'currency' => $data['currency'] ?? 'ZMW',
                'reference' => $data['reference'],
                'phone' => $this->normalizePhoneNumber($data['phone_number'] ?? ''),
                'operator' => $operator,
                'country' => $data['country'] ?? 'zm',
                'bearer' => $data['bearer'] ?? 'customer',
                'callbackUrl' => $data['callback_url'] ?? route('lenco.webhook'),
            ]);

            $json = $response->json();

            if ($response->successful() && ($json['status'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $json['data'] ?? $json,
                    'lenco_id' => $json['data']['id'] ?? null,
                    'lenco_reference' => $json['data']['lencoReference'] ?? null,
                    'authorization_url' => null, // Mobile money is pay-offline, no redirect
                ];
            }

            Log::error('Lenco mobile money initialization failed', [
                'response' => $json,
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => $json['message'] ?? 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Lenco mobile money exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Payment service unavailable. Please try again.',
            ];
        }
    }

    /**
     * Initialize a bank transfer collection request.
     */
    public function initializeBankTransferCollection(array $data): array
    {
        if (!$this->credentialsConfigured()) {
            Log::error('Lenco API credentials not configured');
            return [
                'success' => false,
                'error' => 'Payment gateway is not configured. Please contact support.',
            ];
        }

        try {
            $payload = [
                'amount' => (string) $data['amount'],
                'currency' => $data['currency'] ?? 'ZMW',
                'reference' => $data['reference'],
                'email' => $data['email'] ?? '',
                'callbackUrl' => $data['callback_url'] ?? route('lenco.webhook'),
                'redirectUrl' => $data['redirect_url'] ?? null,
                'customer' => [
                    'firstName' => $data['customer_first_name'] ?? ($data['customer_name'] ?? ''),
                    'lastName' => $data['customer_last_name'] ?? '',
                ],
                'bearer' => $data['bearer'] ?? 'customer',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/access/v2/collections/bank-account", $payload);

            $json = $response->json();

            if ($response->successful() && ($json['status'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $json['data'] ?? $json,
                    'lenco_id' => $json['data']['id'] ?? null,
                    'lenco_reference' => $json['data']['lencoReference'] ?? null,
                    'authorization_url' => $this->extractAuthUrl($json),
                ];
            }

            Log::error('Lenco bank transfer initialization failed', [
                'response' => $json,
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => $json['message'] ?? 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Lenco bank transfer exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Payment service unavailable. Please try again.',
            ];
        }
    }

    /**
     * Initialize a card collection request (JWE encrypted).
     */
    public function initializeCardCollection(array $data): array
    {
        if (!$this->credentialsConfigured()) {
            Log::error('Lenco API credentials not configured');
            return [
                'success' => false,
                'error' => 'Payment gateway is not configured. Please contact support.',
            ];
        }

        // Card collections require card details which we don't collect in our checkout flow.
        // This method is available for future use but currently returns an error.
        Log::warning('Lenco card collection attempted but card details not available in checkout flow');
        return [
            'success' => false,
            'error' => 'Card payments require direct card entry. Please use Mobile Money or Bank Transfer instead.',
        ];
    }

    /**
     * Get Lenco's RSA encryption key for JWE payload encryption.
     */
    protected function getEncryptionKey(): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get("{$this->baseUrl}/access/v2/encryption-key");

            if ($response->successful()) {
                $json = $response->json();
                return $json['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to fetch Lenco encryption key', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Encrypt payload using JWE compact serialization with RSA-OAEP-256 + A256GCM.
     */
    protected function encryptPayload(string $payload, array $jwk): ?string
    {
        try {
            $kid = $jwk['kid'] ?? '';

            if (empty($kid) || empty($jwk['n']) || empty($jwk['e'])) {
                return null;
            }

            // Load RSA public key from JWK
            $rsa = RSA::loadPublicKey(json_encode($jwk));
            $rsa = $rsa->withPadding(RSA::ENCRYPTION_OAEP);
            $rsa = $rsa->withHash('sha256');
            $rsa = $rsa->withMGFHash('sha256');

            // Generate AES-256-GCM key and IV
            $cek = random_bytes(32);
            $iv = random_bytes(12);

            // Encrypt payload with AES-256-GCM
            $aad = $this->base64urlEncode(json_encode([
                'alg' => 'RSA-OAEP-256',
                'enc' => 'A256GCM',
                'cty' => 'application/json',
                'kid' => $kid,
            ]));

            $tag = '';
            $ciphertext = openssl_encrypt($payload, 'aes-256-gcm', $cek, OPENSSL_RAW_DATA, $iv, $tag, $aad, 16);

            if ($ciphertext === false) {
                return null;
            }

            // Encrypt CEK with RSA-OAEP-256
            $encryptedKey = $rsa->encrypt($cek);

            // Build JWE compact serialization
            return implode('.', [
                $aad,
                $this->base64urlEncode($encryptedKey),
                $this->base64urlEncode($iv),
                $this->base64urlEncode($ciphertext),
                $this->base64urlEncode($tag),
            ]);
        } catch (\Exception $e) {
            Log::error('JWE encryption failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Base64url encode without padding.
     */
    protected function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Verify a payment by Lenco collection reference.
     */
    public function verifyPayment(string $reference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get("{$this->baseUrl}/access/v2/collections/status/{$reference}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Unable to verify payment',
            ];
        } catch (\Exception $e) {
            Log::error('Lenco verify exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Verification service unavailable',
            ];
        }
    }

    /**
     * Process a webhook payload from Lenco.
     */
    public function processWebhook(array $payload): bool
    {
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];
        $transactionId = $data['id'] ?? $data['transactionReference'] ?? $data['lencoReference'] ?? null;

        if (!$event || !$transactionId) {
            Log::warning('Invalid Lenco webhook payload', $payload);
            return false;
        }

        // Find transaction by Lenco ID or our reference
        $transaction = LencoTransaction::where('lenco_transaction_id', $transactionId)
            ->orWhere('reference', $data['reference'] ?? $data['clientReference'] ?? $transactionId)
            ->first();

        if (!$transaction) {
            Log::warning('Lenco transaction not found', ['transaction_id' => $transactionId]);
            return false;
        }

        $status = $this->mapLencoStatus($event, $data, $transaction->status);

        // Update transaction
        $metadata = $transaction->metadata ?? [];
        $metadata['last_webhook'] = $payload;
        $metadata['last_webhook_at'] = now()->toIso8601String();

        $transaction->update([
            'status' => $status,
            'metadata' => $metadata,
            'paid_at' => $status === 'completed' ? now() : $transaction->paid_at,
        ]);

        // Update related payment if exists
        if ($transaction->payment) {
            $this->updatePaymentStatus($transaction->payment, $status);
        }

        // Update related registration fee if exists (via reference match)
        $this->syncRegistrationFee($transaction->reference, $status);

        return true;
    }

    /**
     * Poll a pending transaction to check its current status.
     */
    public function pollTransaction(LencoTransaction $transaction): bool
    {
        if (empty($transaction->reference)) {
            Log::warning('Cannot poll transaction without reference', ['id' => $transaction->id]);
            return false;
        }

        $result = $this->verifyPayment($transaction->reference);

        if (!$result['success']) {
            Log::warning('Lenco poll failed', [
                'reference' => $transaction->reference,
                'error' => $result['error'] ?? 'Unknown',
            ]);
            return false;
        }

        $data = $result['data']['data'] ?? $result['data'] ?? [];
        $mappedStatus = $this->mapRawStatus($data['status'] ?? 'pending');

        if ($mappedStatus === $transaction->status) {
            return false;
        }

        $metadata = $transaction->metadata ?? [];
        $metadata['last_poll'] = $data;
        $metadata['last_poll_at'] = now()->toIso8601String();

        $transaction->update([
            'status' => $mappedStatus,
            'metadata' => $metadata,
            'paid_at' => $mappedStatus === 'completed' ? now() : $transaction->paid_at,
        ]);

        if ($transaction->payment) {
            $this->updatePaymentStatus($transaction->payment, $mappedStatus);
        }

        $this->syncRegistrationFee($transaction->reference, $mappedStatus);

        Log::info('Lenco transaction status updated via polling', [
            'reference' => $transaction->reference,
            'old_status' => $transaction->getOriginal('status'),
            'new_status' => $mappedStatus,
        ]);

        return true;
    }

    /**
     * Map Lenco webhook event to our status.
     */
    protected function mapLencoStatus(string $event, array $data, string $fallback): string
    {
        $eventMap = [
            'transaction.successful' => 'completed',
            'virtual-account.transaction' => 'completed',
            'virtual-account.transaction.settled' => 'completed',
            'collection.successful' => 'completed',
            'transaction.failed' => 'failed',
            'collection.failed' => 'failed',
            'transaction.cancelled' => 'cancelled',
            'collection.cancelled' => 'cancelled',
        ];

        $status = $eventMap[$event] ?? $fallback;

        // Override with explicit status from data if present
        if (isset($data['status'])) {
            $dataStatus = strtolower($data['status']);
            if (in_array($dataStatus, ['successful', 'success', 'completed'])) {
                $status = 'completed';
            } elseif (in_array($dataStatus, ['failed', 'failure'])) {
                $status = 'failed';
            } elseif ($dataStatus === 'pending') {
                $status = 'pending';
            } elseif ($dataStatus === 'pay-offline') {
                $status = 'pending';
            }
        }

        return $status;
    }

    /**
     * Map raw Lenco status string to our status.
     */
    protected function mapRawStatus(string $status): string
    {
        return match (strtolower($status)) {
            'successful', 'success', 'completed' => 'completed',
            'failed', 'failure', 'declined' => 'failed',
            'cancelled' => 'cancelled',
            'pay-offline', '3ds-auth-required' => 'pending',
            default => 'pending',
        };
    }

    /**
     * Update payment record status.
     */
    protected function updatePaymentStatus(Payment $payment, string $status): void
    {
        $wasCompleted = $payment->isCompleted();

        $paymentStatus = match ($status) {
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => $payment->payment_status,
        };

        $payment->update([
            'payment_status' => $paymentStatus,
            'payment_date' => $status === 'completed' ? now() : $payment->payment_date,
        ]);

        if ($payment->enrollment) {
            $this->updateEnrollmentPaymentStatus($payment->enrollment);
        }

        // Generate invoice and send receipt when payment first completes
        if ($status === 'completed' && !$wasCompleted) {
            if ($payment->promotion_id) {
                \App\Models\Promotion::where('id', $payment->promotion_id)->increment('used_count');
            }

            try {
                $invoiceService = app(InvoiceService::class);
                $invoiceService->generateInvoice($payment);
            } catch (\Exception $e) {
                Log::error('Failed to generate invoice for completed payment', [
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
                Log::error('Failed to send payment receipt email', [
                    'payment_id' => $payment->payment_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Sync registration fee status by reference.
     */
    protected function syncRegistrationFee(?string $reference, string $status): void
    {
        if (empty($reference)) {
            return;
        }

        $fee = RegistrationFee::where('reference', $reference)
            ->where('payment_status', '!=', 'completed')
            ->first();

        if (!$fee) {
            return;
        }

        $feeStatus = match ($status) {
            'completed' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            default => $fee->payment_status,
        };

        $fee->update([
            'payment_status' => $feeStatus,
            'verified_at' => $status === 'completed' ? now() : $fee->verified_at,
        ]);

        // Also sync the corresponding Payment record
        $payment = Payment::where('transaction_id', $reference)
            ->where('payment_type', 'registration')
            ->first();

        if ($payment) {
            $paymentStatus = match ($status) {
                'completed' => 'Completed',
                'failed' => 'Failed',
                'cancelled' => 'Cancelled',
                default => $payment->payment_status,
            };

            $payment->update([
                'payment_status' => $paymentStatus,
                'payment_date' => $status === 'completed' ? now() : $payment->payment_date,
            ]);
        }

        Log::info('Registration fee status synced', [
            'reference' => $reference,
            'status' => $feeStatus,
        ]);
    }

    /**
     * Update enrollment payment status based on total payments.
     */
    protected function updateEnrollmentPaymentStatus(Enrollment $enrollment): void
    {
        $coursePrice = $enrollment->effectivePrice();

        $totalPaid = Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $totalDiscount = Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('discount_amount');

        $percentagePaid = $coursePrice > 0 ? (($totalPaid + $totalDiscount) / $coursePrice) * 100 : 100;

        $enrollmentStatus = $enrollment->enrollment_status;
        if ($percentagePaid >= 30 && $enrollmentStatus === 'Enrolled') {
            $enrollmentStatus = 'In Progress';
        }

        $certificateBlocked = ($totalPaid + $totalDiscount) < $coursePrice;
        $paymentStatus = ($totalPaid + $totalDiscount) >= $coursePrice ? 'completed' : 'pending';

        $enrollment->update([
            'amount_paid' => $totalPaid,
            'payment_status' => $paymentStatus,
            'enrollment_status' => $enrollmentStatus,
            'certificate_blocked' => $certificateBlocked,
        ]);

        $paymentPlan = $enrollment->paymentPlan;
        if ($paymentPlan) {
            $paymentPlan->update([
                'total_paid' => $totalPaid,
                'payment_status' => $paymentStatus,
            ]);
        }
    }

    /**
     * Validate webhook signature.
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $secrets = array_filter([
            config('services.lenco.webhook_secret'),
            $this->secretKey,
        ]);

        foreach ($secrets as $secret) {
            if (hash_equals(hash_hmac('sha256', $payload, $secret), $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract authorization URL from Lenco response (handles multiple field names).
     */
    protected function extractAuthUrl(array $json): ?string
    {
        $data = $json['data'] ?? $json;
        $meta = $json['meta'] ?? [];

        // Check meta.authorization.redirect for 3DS card payments
        if (!empty($meta['authorization']['redirect'])) {
            return $meta['authorization']['redirect'];
        }

        // Check meta.authorization.url as fallback
        if (!empty($meta['authorization']['url'])) {
            return $meta['authorization']['url'];
        }

        $possibleKeys = [
            'authorizationUrl',
            'authorization_url',
            'checkoutUrl',
            'checkout_url',
            'paymentUrl',
            'payment_url',
            'url',
        ];

        foreach ($possibleKeys as $key) {
            if (!empty($data[$key]) && is_string($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    /**
     * Extract Lenco transaction/collection ID from response.
     */
    protected function extractLencoId(array $json): ?string
    {
        $data = $json['data'] ?? $json;

        return $data['id']
            ?? $data['transactionId']
            ?? $data['transaction_id']
            ?? $data['reference']
            ?? $data['collectionId']
            ?? $data['collection_id']
            ?? null;
    }

    /**
     * Detect mobile network operator from Zambian phone number.
     */
    protected function detectMobileOperator(string $phone): ?string
    {
        $normalized = $this->normalizePhoneNumber($phone);

        if (empty($normalized)) {
            return null;
        }

        // Zambia prefixes (without country code)
        // MTN: 076, 096
        // Airtel: 057, 077, 097
        // Zamtel: 075, 095 (not supported by Lenco)
        $prefix = substr($normalized, 3, 2); // Skip 260

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
    protected function normalizePhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return '';
        }

        // If starts with 0, replace with 260
        if (str_starts_with($phone, '0')) {
            $phone = '260' . substr($phone, 1);
        }

        // If doesn't start with 260, prepend it
        if (!str_starts_with($phone, '260')) {
            $phone = '260' . $phone;
        }

        return $phone;
    }
}
