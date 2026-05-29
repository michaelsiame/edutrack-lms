<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\LencoWebhookLog;
use App\Services\LencoPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LencoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $rawPayload = $request->getContent();
        $signature = $request->header('X-Lenco-Signature');

        // Log the webhook
        $log = LencoWebhookLog::create([
            'event_type' => $payload['event'] ?? 'unknown',
            'lenco_transaction_id' => $payload['data']['id'] ?? $payload['data']['transactionReference'] ?? null,
            'payload' => $payload,
            'signature' => $signature,
            'ip_address' => $request->ip(),
            'processed' => false,
        ]);

        try {
            $service = app(LencoPaymentService::class);

            // Require signature in production
            if (app()->environment('production') && empty($signature)) {
                $log->update([
                    'signature_valid' => false,
                    'error_message' => 'Missing webhook signature',
                ]);

                return response()->json(['error' => 'Missing signature'], 401);
            }

            // Validate signature if provided
            if ($signature) {
                $isValid = $service->validateWebhookSignature($rawPayload, $signature);

                $log->update(['signature_valid' => $isValid]);

                if (!$isValid) {
                    $log->update([
                        'processed' => false,
                        'error_message' => 'Invalid webhook signature',
                    ]);

                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            // Process the webhook
            $processed = $service->processWebhook($payload);

            $log->update([
                'processed' => $processed,
                'error_message' => $processed ? null : 'Processing failed',
            ]);

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Lenco webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            $log->update([
                'processed' => false,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
