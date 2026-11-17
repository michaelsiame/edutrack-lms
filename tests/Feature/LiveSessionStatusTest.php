<?php

namespace Tests\Feature;

use App\Models\LiveSession;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// A scheduled class is live while its time is on: nothing ever sets status to 'live', and students
// only get a Join button for a live session.
class LiveSessionStatusTest extends TestCase
{
    private function liveSession(string $status, int $startsInMinutes, int $minutes = 60): LiveSession
    {
        return new LiveSession([
            'status' => $status,
            'scheduled_start_time' => Carbon::now()->addMinutes($startsInMinutes),
            'scheduled_end_time' => Carbon::now()->addMinutes($startsInMinutes + $minutes),
        ]);
    }

    #[Test]
    public function a_scheduled_class_is_live_during_its_time(): void
    {
        $s = $this->liveSession('scheduled', -5);
        $this->assertTrue($s->isLive());
        $this->assertFalse($s->isUpcoming());
    }

    #[Test]
    public function a_scheduled_class_is_upcoming_before_and_not_live_after(): void
    {
        $this->assertFalse($this->liveSession('scheduled', 30)->isLive());
        $this->assertTrue($this->liveSession('scheduled', 30)->isUpcoming());
        $this->assertFalse($this->liveSession('scheduled', -120)->isLive());
    }

    #[Test]
    public function a_cancelled_class_is_never_live_and_a_live_one_stops_at_its_end(): void
    {
        $this->assertFalse($this->liveSession('cancelled', -5)->isLive());
        $this->assertTrue($this->liveSession('live', -5)->isLive());
        $this->assertFalse($this->liveSession('live', -120)->isLive());
    }
}
