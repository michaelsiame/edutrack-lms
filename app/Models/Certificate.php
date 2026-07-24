<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $primaryKey = 'certificate_id';
    public $incrementing = true;

    // The certificates table has only created_at (no updated_at column on the
    // live schema), so disable Eloquent's updated_at management.
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_id',
        'certificate_number',
        'issued_date',
        'verification_code',
        'final_score',
        'issued_at',
        'is_verified',
        'expiry_date',
        'classification',
        'graduation_ceremony_date',
        'intake_name',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'issued_at' => 'datetime',
        'final_score' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', $this->verification_code);
    }
}
