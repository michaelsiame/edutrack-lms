<?php

namespace App\Console\Commands;

use App\Services\EmailQueueService;
use Illuminate\Console\Command;

class ProcessEmails extends Command
{
    protected $signature = 'email:process {--limit=50 : Maximum emails to process}';
    protected $description = 'Process pending emails in the queue';

    public function handle(EmailQueueService $emailService): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Processing up to {$limit} pending emails...");

        $sent = $emailService->processQueue($limit);

        $this->info("Sent {$sent} emails.");
        return self::SUCCESS;
    }
}
