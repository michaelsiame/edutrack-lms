<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use App\Models\LiveSessionAttendance;
use Illuminate\Console\Command;

class EndExpiredSessions extends Command
{
    protected $signature = 'sessions:end-expired';
    protected $description = 'Mark live sessions as ended and finalize attendance records';

    public function handle(): int
    {
        $this->info('Checking for expired live sessions...');

        // Find sessions that are live but past their scheduled end time
        $expiredSessions = LiveSession::where('status', 'live')
            ->where('scheduled_end_time', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredSessions as $session) {
            // Finalize attendance for anyone still marked as present
            $openAttendance = LiveSessionAttendance::where('live_session_id', $session->id)
                ->whereNull('left_at')
                ->get();

            foreach ($openAttendance as $attendance) {
                $attendance->left_at = now();
                $joinedAt = $attendance->joined_at;
                if ($joinedAt) {
                    $durationSeconds = now()->diffInSeconds($joinedAt);
                    $attendance->duration_seconds = ($attendance->duration_seconds ?? 0) + max(0, $durationSeconds);
                }
                $attendance->save();
            }

            $session->update(['status' => 'ended']);
            $count++;
        }

        // Also mark scheduled sessions that completely missed their window as ended/cancelled
        $missedSessions = LiveSession::where('status', 'scheduled')
            ->where('scheduled_end_time', '<', now()->subHours(2))
            ->get();

        foreach ($missedSessions as $session) {
            $session->update(['status' => 'ended']);
            $count++;
        }

        $this->info("Processed {$count} expired sessions.");
        return self::SUCCESS;
    }
}
