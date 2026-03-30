<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class QuizAttempt extends Model
{
    /** @use HasFactory<\Database\Factories\QuizAttemptFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /* Relasi ke user */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* Relasi ke kuis */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /* Relasi ke jawaban attempt */
    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    protected static function booted()
    {
        static::creating(function ($quiz) {
            if (empty($quiz->uuid)) {
                $quiz->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
