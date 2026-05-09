<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'specialization',
        'years_experience',
        'education',
        'certifications',
        'rating',
        'total_students',
        'total_courses',
        'is_verified',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'total_students' => 'integer',
        'total_courses' => 'integer',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
