<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'category',
        'display_order',
        'is_featured',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_featured', true)->orderBy('display_order');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
