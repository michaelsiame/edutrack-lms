<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'payment_id';
    public $incrementing = true;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrollment_id',
        'payment_plan_id',
        'amount',
        'currency',
        'payment_method_id',
        'payment_type',
        'recorded_by',
        'payment_status',
        'transaction_id',
        'phone_number',
        'notes',
        'payment_date',
        'promotion_id',
        'discount_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'payment_id', 'payment_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'Completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'Pending');
    }

    public function isCompleted(): bool
    {
        return $this->payment_status === 'Completed';
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'payment_method_id');
    }

    public function getPaymentMethodAttribute(): ?string
    {
        return $this->paymentMethod?->method_name ?? 'Online';
    }
}
