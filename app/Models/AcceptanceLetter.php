<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptanceLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'reference_no',
        'student_name',
        'course_title',
        'mode',
        'duration',
        'commencement_date',
        'fee_snapshot',
        'issued_date',
        'signed_by',
    ];

    protected $casts = [
        'commencement_date' => 'date',
        'issued_date' => 'date',
        'fee_snapshot' => 'array',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
