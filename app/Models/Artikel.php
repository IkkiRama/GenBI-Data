<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artikel extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "author_id");
    }

    public function kategori_artikel(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, "kategori_id");
    }
}
