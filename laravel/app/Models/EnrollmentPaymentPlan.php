<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentPaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'course_id',
        'total_fee',
        'total_paid',
        'currency',
        'payment_status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'completed';
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_fee - $this->total_paid;
    }
}
