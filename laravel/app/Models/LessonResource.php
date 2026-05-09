<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Model
{
    use HasFactory;

    protected $table = 'lesson_resources';

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'resource_type',
        'file_url',
        'file_size_kb',
        'download_count',
    ];

    protected $casts = [
        'file_size_kb' => 'integer',
        'download_count' => 'integer',
    ];

    const UPDATED_AT = null;

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
