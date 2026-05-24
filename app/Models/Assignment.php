<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'description',
        'instructions',
        'max_points',
        'passing_points',
        'due_date',
        'allow_late_submission',
        'late_penalty_percent',
        'max_file_size_mb',
        'allowed_file_types',
    ];

    protected $casts = [
        'max_points' => 'integer',
        'passing_points' => 'integer',
        'due_date' => 'datetime',
        'allow_late_submission' => 'boolean',
        'late_penalty_percent' => 'decimal:2',
        'max_file_size_mb' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
