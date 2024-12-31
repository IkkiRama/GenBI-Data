<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komentar extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ["id"];

    public function artikel(): BelongsTo
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }
}
