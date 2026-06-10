<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    const UPDATED_AT = null;

    public const SUPER_ADMIN = 1;
    public const ADMIN = 2;
    public const INSTRUCTOR = 3;
    public const STUDENT = 4;
    public const FINANCE = 6;

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
