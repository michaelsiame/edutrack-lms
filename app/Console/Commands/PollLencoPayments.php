<?php

namespace App\Console\Commands;

use App\Models\LencoTransaction;
use App\Services\LencoPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollLencoPayments extends Command
{
    protected $signature = 'lenco:poll-payments 
                            {--hours=24 : Only poll transactions created within last N hours}
                            {--limit=50 : Maximum number of transactions to poll per run}';

    protected $description = 'Poll Lenco for pending payment statuses (fallback when webhooks fail)';

    public function handle(LencoPaymentService $service): int
    {
        $hours = (int) $this->option('hours');
        $limit = (int) $this->option('limit');

        $pendingTransactions = LencoTransaction::where('status', 'pending')
            ->where('created_at', '>=', now()->subHours($hours))
            ->whereNotNull('lenco_transaction_id')
            ->limit($limit)
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info('No pending transactions to poll.');
            return self::SUCCESS;
        }

        $this->info("Polling {$pendingTransactions->count()} pending Lenco transaction(s)...");
        $updated = 0;
        $failed = 0;

        foreach ($pendingTransactions as $transaction) {
            $this->info("Polling transaction: {$transaction->lenco_transaction_id} (ref: {$transaction->reference})");

            try {
                $wasUpdated = $service->pollTransaction($transaction);
                if ($wasUpdated) {
                    $updated++;
                    $this->info("  -> Status updated to: {$transaction->fresh()->status}");
                } else {
                    $this->info("  -> Still pending (no change)");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("  -> Poll failed: {$e->getMessage()}");
                Log::error('Lenco polling failed for transaction', [
                    'transaction_id' => $transaction->lenco_transaction_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Small delay to avoid rate limiting
            usleep(200000); // 200ms
        }

        $this->newLine();
        $this->info("Done. Updated: {$updated}, Failed: {$failed}, Unchanged: " . ($pendingTransactions->count() - $updated - $failed));

        return self::SUCCESS;
    }
}
