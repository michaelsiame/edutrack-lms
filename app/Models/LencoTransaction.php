<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LencoTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'payment_id',
        'user_id',
        'enrollment_id',
        'course_id',
        'amount',
        'currency',
        'virtual_account_number',
        'virtual_account_bank',
        'virtual_account_name',
        'lenco_account_id',
        'lenco_transaction_id',
        'status',
        'payment_method',
        'phone_number',
        'paid_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
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
