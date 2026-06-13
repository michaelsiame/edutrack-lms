<?php

namespace App\Console\Commands;

use App\Models\Intake;
use Illuminate\Console\Command;

class AdvanceIntakeLifecycle extends Command
{
    protected $signature = 'intakes:advance-lifecycle {--dry-run : Show what would change without saving}';

    protected $description = 'Advance intake statuses on schedule: close enrolment past the deadline, and complete intakes past their end date.';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $dry = $this->option('dry-run');
        $closed = 0;
        $completed = 0;

        // 1) Enrolment window has passed -> mark the running cohort 'in_progress'
        //    (uses application_deadline if set, otherwise the start_date).
        Intake::where('status', 'open')->get()->each(function (Intake $intake) use ($today, $dry, &$closed) {
            $cutoff = $intake->application_deadline ?? $intake->start_date;
            if ($cutoff && $cutoff->lt($today)) {
                $this->line("  open -> in_progress: #{$intake->id} {$intake->name}");
                if (!$dry) {
                    $intake->update(['status' => 'in_progress']);
                }
                $closed++;
            }
        });

        // 2) End date has passed -> mark the cohort 'completed'.
        Intake::whereIn('status', ['open', 'in_progress'])->get()->each(function (Intake $intake) use ($today, $dry, &$completed) {
            if ($intake->end_date && $intake->end_date->lt($today)) {
                $this->line("  -> completed: #{$intake->id} {$intake->name}");
                if (!$dry) {
                    $intake->update(['status' => 'completed']);
                }
                $completed++;
            }
        });

        $this->info(($dry ? '[dry-run] ' : '') . "Intake lifecycle: {$closed} closed for enrolment, {$completed} completed.");

        return self::SUCCESS;
    }
}
