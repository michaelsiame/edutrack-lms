<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
    use HasFactory;

    protected $table = 'email_queue';

    protected $fillable = [
        'recipient',
        'subject',
        'body',
        'attachments',
        'status',
        'attempts',
        'priority',
        'scheduled_at',
        'sent_at',
        'last_attempt',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'priority' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'last_attempt' => 'datetime',
        'created_at' => 'datetime',
    ];

    const UPDATED_AT = null;

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
