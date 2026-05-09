<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'activity_type',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'created_at' => 'datetime',
    ];

    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
