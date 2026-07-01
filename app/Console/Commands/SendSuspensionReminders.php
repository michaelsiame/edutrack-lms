<?php

namespace App\Console\Commands;

use App\Models\EnrollmentPaymentPlan;
use App\Models\Student;
use App\Services\EmailQueueService;
use Illuminate\Console\Command;

class SendSuspensionReminders extends Command
{
    protected $signature = 'payments:suspension-reminders
        {--date= : Suspension date shown in the message, e.g. "1 July"}
        {--enrollment= : Only this enrollment id}
        {--user= : Only this user id}
        {--email= : Only the student with this email address (prod-safe target)}
        {--send : Actually queue/send the emails (otherwise dry-run)}';

    protected $description = 'Email payment-plan students that LMS access will be suspended until they pay.';

    public function handle(EmailQueueService $emailService): int
    {
        $suspensionDate = $this->option('date') ?: '1 July';
        $send = (bool) $this->option('send');

        // Outstanding is derived from total_fee - total_paid (the stored `balance`
        // column can be stale on older records), so reminders always reflect real paid amounts.
        $query = EnrollmentPaymentPlan::with(['user', 'course', 'enrollment'])
            ->whereRaw('COALESCE(total_fee,0) - COALESCE(total_paid,0) > 0')
            ->where('payment_status', '!=', 'paid');

        if ($eid = $this->option('enrollment')) {
            $query->where('enrollment_id', $eid);
        }
        if ($uid = $this->option('user')) {
            $query->where('user_id', $uid);
        }
        if ($email = $this->option('email')) {
            $query->whereHas('user', fn ($q) => $q->where('email', $email));
        }

        $plans = $query->get();

        if ($plans->isEmpty()) {
            $this->warn('No matching payment plans with an outstanding balance.');
            return self::SUCCESS;
        }

        $this->info(($send ? 'SENDING' : 'DRY-RUN — would send') . " {$plans->count()} suspension reminder(s). Suspension date: {$suspensionDate}");
        $this->newLine();

        $sent = 0;
        $skipped = 0;

        foreach ($plans as $plan) {
            $user = $plan->user;
            if (! $user || ! $user->email) {
                $this->line("  <fg=yellow>skip</> plan#{$plan->id}: no user/email");
                $skipped++;
                continue;
            }

            $studentNumber = Student::where('user_id', $user->id)->value('student_number') ?: '—';
            $programme = $plan->course?->title ?? 'your programme';
            $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Student';

            // Use the most reliable "paid" figure: the enrollment's amount_paid is the
            // maintained truth; fall back to the plan's total_paid. This keeps the
            // outstanding correct even if a plan's total_paid column drifted.
            $paid = max((float) ($plan->total_paid ?? 0), (float) ($plan->enrollment?->amount_paid ?? 0));
            $outstanding = max(0, (float) ($plan->total_fee ?? 0) - $paid);

            if ($outstanding <= 0) {
                $this->line("  <fg=yellow>skip</> {$name}: fully paid, nothing outstanding");
                $skipped++;
                continue;
            }

            $this->line(sprintf(
                '  %s  %-26s %-16s %s  (bal %s %.2f)',
                $send ? '<fg=green>send</>' : '<fg=cyan>plan</>',
                $name,
                $studentNumber,
                $user->email,
                $plan->currency ?: 'ZMW',
                $outstanding
            ));

            if (! $send) {
                continue;
            }

            $body = view('emails.payment-suspension-reminder', [
                'studentName' => $name,
                'studentNumber' => $studentNumber,
                'programme' => $programme,
                'suspensionDate' => $suspensionDate,
                'outstanding' => $outstanding,
                'currency' => $plan->currency ?: 'ZMW',
            ])->render();

            $emailService->queue(
                $user->email,
                'Payment Plan Reminder — LMS Access Suspension',
                $body,
                [],
                4
            );
            $sent++;
        }

        $this->newLine();
        $this->info($send ? "Queued {$sent} reminder(s), skipped {$skipped}." : "Dry-run complete. Re-run with --send to dispatch.");

        return self::SUCCESS;
    }
}
