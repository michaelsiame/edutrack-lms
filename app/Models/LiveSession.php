<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'instructor_id',
        'meeting_room_id',
        'scheduled_start_time',
        'scheduled_end_time',
        'duration_minutes',
        'status',
        'max_participants',
        'description',
        'recording_url',
        'moderator_password',
        'participant_password',
        'allow_recording',
        'auto_start_recording',
        'enable_chat',
        'enable_screen_share',
        'buffer_minutes_before',
        'buffer_minutes_after',
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'max_participants' => 'integer',
        'allow_recording' => 'boolean',
        'auto_start_recording' => 'boolean',
        'enable_chat' => 'boolean',
        'enable_screen_share' => 'boolean',
        'buffer_minutes_before' => 'integer',
        'buffer_minutes_after' => 'integer',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function intake()
    {
        return $this->belongsTo(Intake::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function attendance()
    {
        return $this->hasMany(LiveSessionAttendance::class);
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_start_time > now();
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }
}
