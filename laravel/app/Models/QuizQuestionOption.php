<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestionOption extends Model
{
    use HasFactory;

    protected $table = 'quiz_question_options';

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

    const UPDATED_AT = null;

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
