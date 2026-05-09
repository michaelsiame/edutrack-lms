<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseInstructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'role',
        'assigned_date',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    const UPDATED_AT = null;

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
