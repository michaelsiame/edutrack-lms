<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process email queue every 5 minutes
        $schedule->command('queue:work --stop-when-empty')->everyFiveMinutes();

        // Session reminders daily at 8am CAT
        $schedule->command('reminders:send')->dailyAt('08:00');

        // End expired live sessions every 15 minutes
        $schedule->command('sessions:end-expired')->everyFifteenMinutes();

        // Poll Lenco for pending payments every 10 minutes (webhook fallback)
        $schedule->command('lenco:poll-payments --hours=48 --limit=50')->everyTenMinutes();

        // Advance intake statuses daily (close enrolment past deadline, complete past end date)
        $schedule->command('intakes:advance-lifecycle')->dailyAt('00:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
