<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Struktur extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
