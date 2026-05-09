<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'country',
        'postal_code',
        'enrollment_date',
        'total_courses_enrolled',
        'total_courses_completed',
        'total_certificates',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'total_courses_enrolled' => 'integer',
        'total_courses_completed' => 'integer',
        'total_certificates' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrationFees()
    {
        return $this->hasMany(RegistrationFee::class, 'student_id');
    }
}
