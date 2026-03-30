<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuizQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\QuizQuizQuestionFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];


    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function quizQuestion()
    {
        return $this->belongsTo(QuizQuestion::class);
    }
}
