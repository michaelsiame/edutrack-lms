<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_description',
        'icon_url',
        'parent_category_id',
        'display_order',
        'is_active',
        'color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
