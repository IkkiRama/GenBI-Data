<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizAttemptAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\QuizAttemptAnswerFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class);
    }

    public function answer()
    {
        return $this->belongsTo(QuizAnswer::class);
    }
}
