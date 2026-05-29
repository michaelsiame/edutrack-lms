<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSessionAttendance extends Model
{
    use HasFactory;

    protected $table = 'live_session_attendance';

    protected $fillable = [
        'live_session_id',
        'user_id',
        'joined_at',
        'left_at',
        'duration_seconds',
        'is_moderator',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'duration_seconds' => 'integer',
        'is_moderator' => 'boolean',
    ];

    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
