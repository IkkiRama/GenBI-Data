<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\QuizAnswerFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /* Relasi ke pertanyaan */
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
