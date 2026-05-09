<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id',
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
            1 => 'Admin',
            2 => 'Instructor',
            3 => 'Finance',
            4 => 'Student',
            default => 'Unknown',
        };
    }
}
