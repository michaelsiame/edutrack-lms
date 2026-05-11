<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'description',
        'quiz_type',
        'time_limit_minutes',
        'max_attempts',
        'passing_score',
        'randomize_questions',
        'show_correct_answers',
        'available_from',
        'available_until',
        'is_published',
    ];

    protected $casts = [
        'time_limit_minutes' => 'integer',
        'max_attempts' => 'integer',
        'passing_score' => 'decimal:2',
        'randomize_questions' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_published' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_questions', 'quiz_id', 'question_id')
            ->withPivot('display_order')
            ->withTimestamps()
            ->orderBy('quiz_questions.display_order');
    }
}
