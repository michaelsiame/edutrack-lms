<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $primaryKey = 'badge_id';
    public $incrementing = true;

    protected $fillable = [
        'badge_name',
        'description',
        'badge_icon_url',
        'badge_type',
        'criteria',
        'points',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    const UPDATED_AT = null;

    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class, 'badge_id');
    }
}
