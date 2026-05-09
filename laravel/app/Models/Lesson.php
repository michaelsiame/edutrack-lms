<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'content',
        'lesson_type',
        'duration_minutes',
        'display_order',
        'video_url',
        'video_duration',
        'is_preview',
        'is_mandatory',
        'points',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'display_order' => 'integer',
        'video_duration' => 'integer',
        'is_preview' => 'boolean',
        'is_mandatory' => 'boolean',
        'points' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function course()
    {
        return $this->module->course();
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
