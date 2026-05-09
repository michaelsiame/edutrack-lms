<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $primaryKey = 'discussion_id';
    public $incrementing = true;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'content',
        'is_pinned',
        'is_locked',
        'view_count',
        'reply_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'view_count' => 'integer',
        'reply_count' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function replies()
    {
        return $this->hasMany(DiscussionReply::class, 'discussion_id');
    }
}
