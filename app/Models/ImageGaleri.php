<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImageGaleri extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];

    public function galeri(): BelongsTo
    {
        return $this->belongsTo(Galeri::class);
    }
}
