<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';
    public $incrementing = true;

    protected $fillable = [
        'payment_id',
        'transaction_type',
        'amount',
        'currency',
        'gateway_response',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
    ];

    const UPDATED_AT = null;

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
