<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriArtikel extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];

    public function artikels(): HasMany
    {
        return $this->hasMany(Artikel::class);
    }
}
