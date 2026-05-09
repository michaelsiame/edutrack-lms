<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\LencoTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->baseUrl = $this->isSandbox
            ? 'https://sandbox-api.lenco.co'
            : 'https://api.lenco.co';
    }

    /**
     * Initialize a payment request.
     */
    public function initializePayment(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v1/payments", [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'ZMW',
                'description' => $data['description'] ?? 'Course Payment',
                'callbackUrl' => $data['callback_url'] ?? route('lenco.webhook'),
                'reference' => $data['reference'],
                'customer' => [
                    'email' => $data['email'] ?? '',
                    'phoneNumber' => $data['phone_number'] ?? '',
                    'name' => $data['customer_name'] ?? '',
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Lenco payment initialization failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Lenco payment exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Payment service unavailable. Please try again.',
            ];
        }
    }

    /**
     * Verify a payment by transaction ID.
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get("{$this->baseUrl}/v1/payments/{$transactionId}");

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
     * Process a webhook payload.
     */
    public function processWebhook(array $payload): bool
    {
        $event = $payload['event'] ?? null;
        $transactionId = $payload['data']['id'] ?? null;

        if (!$event || !$transactionId) {
            Log::warning('Invalid Lenco webhook payload', $payload);
            return false;
        }

        $transaction = LencoTransaction::where('lenco_transaction_id', $transactionId)->first();

        if (!$transaction) {
            Log::warning('Lenco transaction not found', ['transaction_id' => $transactionId]);
            return false;
        }

        $status = match ($event) {
            'payment.success' => 'completed',
            'payment.failed' => 'failed',
            'payment.cancelled' => 'cancelled',
            default => $transaction->status,
        };

        $transaction->update([
            'status' => $status,
            'lenco_response' => $payload,
            'processed_at' => now(),
        ]);

        // Update related payment
        if ($transaction->payment) {
            $paymentStatus = match ($status) {
                'completed' => 'Completed',
                'failed' => 'Failed',
                'cancelled' => 'Cancelled',
                default => $transaction->payment->payment_status,
            };

            $transaction->payment->update([
                'payment_status' => $paymentStatus,
                'payment_date' => now(),
            ]);

            // Update enrollment payment status
            if ($transaction->payment->enrollment) {
                $this->updateEnrollmentPaymentStatus($transaction->payment->enrollment);
            }
        }

        return true;
    }

    /**
     * Update enrollment payment status based on total payments.
     */
    protected function updateEnrollmentPaymentStatus(Enrollment $enrollment): void
    {
        $totalPaid = Payment::where('enrollment_id', $enrollment->id)
            ->where('payment_status', 'Completed')
            ->sum('amount');

        $enrollment->update([
            'amount_paid' => $totalPaid,
            'payment_status' => $totalPaid >= $enrollment->course->price ? 'completed' : 'pending',
        ]);
    }

    /**
     * Validate webhook signature.
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, $this->secretKey);
        return hash_equals($expected, $signature);
    }
}
