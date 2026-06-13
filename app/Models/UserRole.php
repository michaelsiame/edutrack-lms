<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_id',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'role_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role_id) {
            1 => 'Super Admin',
            2 => 'Admin',
            3 => 'Instructor',
            4 => 'Student',
            5 => 'Content Creator',
            6 => 'Finance',
            default => 'Unknown',
        };
    }
}
