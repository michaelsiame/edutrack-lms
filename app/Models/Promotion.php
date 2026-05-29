<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_uses',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
        'applicable_courses',
        'min_order_amount',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'applicable_courses' => 'array',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: only active promotions that are currently valid.
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope: promotions that have not exceeded max uses.
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('max_uses')
              ->orWhereRaw('used_count < max_uses');
        });
    }

    /**
     * Check if promotion is currently valid.
     */
    public function isValid(): bool
    {
        $now = Carbon::now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if promotion applies to a given course.
     */
    public function appliesToCourse(int $courseId): bool
    {
        if (empty($this->applicable_courses)) {
            return true;
        }

        return in_array($courseId, $this->applicable_courses);
    }

    /**
     * Calculate discounted amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->min_order_amount !== null && $subtotal < $this->min_order_amount) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return round($subtotal * ($this->discount_value / 100), 2);
        }

        return min($this->discount_value, $subtotal);
    }

    /**
     * Format discount for display.
     */
    public function formattedDiscount(): string
    {
        if ($this->discount_type === 'percentage') {
            return (int) $this->discount_value . '% OFF';
        }

        return 'K' . number_format($this->discount_value, 2) . ' OFF';
    }

    /**
     * Check if promotion is expired.
     */
    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if promotion is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at && $this->starts_at->isFuture();
    }
}
