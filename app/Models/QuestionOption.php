<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'option_id';
    public $incrementing = true;

    /**
     * Backwards-compat: the DB column is option_id, but a lot of older code
     * and views reference $option->id. Expose it so both work.
     */
    public function getIdAttribute()
    {
        return $this->attributes['option_id'] ?? null;
    }

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'display_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'display_order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
