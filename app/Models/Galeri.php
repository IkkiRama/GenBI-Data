<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Galeri extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];

    public function image_galeri(): HasMany
    {
        return $this->hasMany(ImageGaleri::class);
    }
}
