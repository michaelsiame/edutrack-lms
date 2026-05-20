<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LencoTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'lenco_transaction_id',
        'payment_id',
        'enrollment_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'phone_number',
        'lenco_response',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'lenco_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
