<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'category_id',
        'instructor_id',
        'level',
        'language',
        'thumbnail_url',
        'video_intro_url',
        'start_date',
        'end_date',
        'price',
        'discount_price',
        'duration_weeks',
        'total_hours',
        'max_students',
        'enrollment_count',
        'status',
        'is_featured',
        'is_template',
        'template_source_id',
        'rating',
        'total_reviews',
        'prerequisites',
        'learning_outcomes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'rating' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_featured' => 'boolean',
        'enrollment_count' => 'integer',
        'total_reviews' => 'integer',
        'max_students' => 'integer',
        'duration_weeks' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('display_order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function liveSessions()
    {
        $lessonIds = $this->lessons()->pluck('lessons.id');
        return LiveSession::whereIn('lesson_id', $lessonIds);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class, 'course_id');
    }

    public function intakes()
    {
        return $this->hasMany(Intake::class)->orderBy('display_order');
    }

    public function defaultIntake()
    {
        return $this->hasOne(Intake::class)->where('is_default', true);
    }

    public function hasMultipleIntakes(): bool
    {
        return $this->intakes()->where('is_default', false)->exists();
    }

    public function currentIntakes()
    {
        return $this->intakes()
            ->whereIn('status', ['open', 'draft'])
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                  ->orWhere('application_deadline', '>=', now()->subDay());
            })
            ->orderBy('start_date');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'ZMW ' . number_format($this->price, 2);
    }

    /**
     * Get the properly resolved thumbnail image URL.
     * Handles both external URLs and local storage paths.
     */
    public function getThumbnailImageUrlAttribute(): ?string
    {
        if (empty($this->thumbnail_url)) {
            return null;
        }

        // If it's already a full URL, use it directly
        if (filter_var($this->thumbnail_url, FILTER_VALIDATE_URL)) {
            return $this->thumbnail_url;
        }

        // Otherwise it's a local storage path (e.g. courses/thumbnails/filename.jpg)
        return asset('storage/' . $this->thumbnail_url);
    }
}
