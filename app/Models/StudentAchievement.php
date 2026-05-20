<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAchievement extends Model
{
    use HasFactory;

    protected $primaryKey = 'achievement_id';
    public $incrementing = true;

    protected $fillable = [
        'student_id',
        'badge_id',
        'course_id',
        'earned_date',
        'description',
    ];

    protected $casts = [
        'earned_date' => 'date',
    ];

    const CREATED_AT = null;
    const UPDATED_AT = null;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
