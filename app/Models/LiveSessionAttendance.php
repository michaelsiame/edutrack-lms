<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSessionAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_session_id',
        'student_id',
        'joined_at',
        'left_at',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function liveSession()
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
