<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'display_order',
        'duration_minutes',
        'is_published',
        'unlock_date',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'duration_minutes' => 'integer',
        'is_published' => 'boolean',
        'unlock_date' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('display_order');
    }
}
