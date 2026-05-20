<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionReply extends Model
{
    use HasFactory;

    protected $primaryKey = 'reply_id';
    public $incrementing = true;

    protected $fillable = [
        'discussion_id',
        'parent_reply_id',
        'user_id',
        'content',
        'is_instructor_reply',
        'is_best_answer',
    ];

    protected $casts = [
        'is_instructor_reply' => 'boolean',
        'is_best_answer' => 'boolean',
    ];

    public function discussion()
    {
        return $this->belongsTo(Discussion::class, 'discussion_id');
    }

    public function parentReply()
    {
        return $this->belongsTo(DiscussionReply::class, 'parent_reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function childReplies()
    {
        return $this->hasMany(DiscussionReply::class, 'parent_reply_id');
    }
}
