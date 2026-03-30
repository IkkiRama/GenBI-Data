<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\QuizQuestionFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];

    /* Relasi ke jawaban */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /* Relasi ke kuis melalui pivot */
    // public function quizzes()
    // {
    //     return $this->belongsToMany(
    //         \App\Models\Quiz::class,
    //         'quiz_quiz_questions',
    //         'quiz_question_id',
    //         'quiz_id'
    //     )->withTimestamps();
    // }

    // Tiap pertanyaan bisa muncul di beberapa kuis
    public function quizQuizQuestions()
    {
        return $this->hasMany(QuizQuizQuestion::class);
    }

    // Shortcut buat ambil langsung kuis-kuis yang pakai pertanyaan ini
    public function quizzes()
    {
        return $this->hasManyThrough(
            Quiz::class,
            QuizQuizQuestion::class,
            'quiz_question_id', // Foreign key di pivot
            'id',               // Foreign key di Quiz
            'id',               // Local key di QuizQuestion
            'quiz_id'           // Local key di pivot
        );
    }
}
