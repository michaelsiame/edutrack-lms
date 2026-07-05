<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\LipilaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LipilaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $rawPayload = $request->getContent();
        $signature = $request->header('X-Lipila-Signature');

        Log::info('Lipila webhook received', [
            'ip' => $request->ip(),
            'signature' => $signature,
            'payload' => $payload,
        ]);

        try {
            $service = app(LipilaPaymentService::class);

            // Lipila currently does not sign webhooks; rely on the server-side
            // status re-verify inside processWebhook() rather than the body alone.
            // If Lipila adds signatures in the future, validate them here.
            if ($signature && !$service->validateWebhookSignature($rawPayload, $signature)) {
                Log::warning('Lipila webhook invalid signature', [
                    'ip' => $request->ip(),
                    'signature' => $signature,
                ]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $processed = $service->processWebhook($payload);

            if (!$processed) {
                return response()->json(['error' => 'Processing failed'], 422);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Lipila webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
}
