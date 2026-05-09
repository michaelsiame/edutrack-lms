<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LencoWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'lenco_webhook_logs';

    protected $fillable = [
        'event_type',
        'lenco_transaction_id',
        'payload',
        'ip_address',
        'processed',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
    ];
}
