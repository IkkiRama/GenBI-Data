<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pemateri extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ["id"];

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
