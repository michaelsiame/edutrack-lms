<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_id',
        'course_id',
        'enrolled_at',
        'start_date',
        'progress',
        'final_grade',
        'enrollment_status',
        'payment_status',
        'amount_paid',
        'completion_date',
        'certificate_issued',
        'certificate_blocked',
        'last_accessed',
        'total_time_spent',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'progress' => 'decimal:2',
        'final_grade' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'certificate_issued' => 'boolean',
        'certificate_blocked' => 'boolean',
        'last_accessed' => 'datetime',
        'total_time_spent' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function paymentPlan()
    {
        return $this->hasOne(EnrollmentPaymentPlan::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('enrollment_status', ['Enrolled', 'In Progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', 'Completed');
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'completed' && $this->amount_paid >= $this->course->price;
    }
}
