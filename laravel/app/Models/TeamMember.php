<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $table = 'team_members';

    protected $fillable = [
        'user_id',
        'name',
        'position',
        'qualifications',
        'image_url',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
