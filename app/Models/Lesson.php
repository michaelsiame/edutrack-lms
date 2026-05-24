<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

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

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function resources()
    {
        return $this->hasMany(LessonResource::class);
    }

    public function versions()
    {
        return $this->hasMany(LessonVersion::class)->orderBy('version_number', 'desc');
    }

    public function latestVersion()
    {
        return $this->hasOne(LessonVersion::class)->latestOfMany('version_number');
    }

    /**
     * Convert a video URL (YouTube, Vimeo) to an embed URL.
     */
    public function embedUrl(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $url = $this->video_url;

        // YouTube: watch?v=, youtu.be/, embed/
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        // If already an embed or other URL, return as-is
        return $url;
    }
}
