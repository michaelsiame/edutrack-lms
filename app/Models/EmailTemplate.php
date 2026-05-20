<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $primaryKey = 'template_id';
    public $incrementing = true;

    protected $fillable = [
        'template_name',
        'subject',
        'body',
        'template_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
