<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intake extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'start_date',
        'end_date',
        'application_deadline',
        'learning_deadline',
        'max_students',
        'enrollment_count',
        'price_override',
        'status',
        'is_default',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'application_deadline' => 'date',
        'learning_deadline' => 'date',
        'max_students' => 'integer',
        'enrollment_count' => 'integer',
        'price_override' => 'decimal:2',
        'is_default' => 'boolean',
        'display_order' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['draft', 'open'])
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '>=', now()->subDays(1));
            });
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function getSpotsRemainingAttribute(): ?int
    {
        if ($this->max_students <= 0) {
            return null;
        }
        return max(0, $this->max_students - $this->enrollment_count);
    }

    public function getIsFullAttribute(): bool
    {
        if ($this->max_students <= 0) {
            return false;
        }
        return $this->enrollment_count >= $this->max_students;
    }

    public function getFormattedPriceAttribute(): string
    {
        $price = $this->price_override ?? $this->course?->discount_price ?? $this->course?->price ?? 0;
        return 'ZMW ' . number_format($price, 2);
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->price_override ?? $this->course?->discount_price ?? $this->course?->price ?? 0;
    }

    public function canEnroll(): bool
    {
        if (!in_array($this->status, ['open', 'draft'])) {
            return false;
        }

        if ($this->application_deadline && $this->application_deadline->isPast()) {
            return false;
        }

        if ($this->is_full) {
            return false;
        }

        return true;
    }

    public function incrementEnrollmentCount(): void
    {
        \DB::transaction(function () {
            $fresh = self::lockForUpdate()->find($this->id);
            if ($fresh) {
                $fresh->increment('enrollment_count');
                $fresh->checkCapacity();
            }
        });
    }

    public function decrementEnrollmentCount(): void
    {
        $this->decrement('enrollment_count');
        if ($this->status === 'closed' && !$this->is_full) {
            $this->update(['status' => 'open']);
        }
    }

    public function checkCapacity(): void
    {
        if ($this->max_students > 0 && $this->enrollment_count >= $this->max_students) {
            $this->update(['status' => 'closed']);
        }
    }
}
