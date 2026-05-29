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
        'signature',
        'signature_valid',
        'processed',
        'error_message',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'signature_valid' => 'boolean',
    ];
}
