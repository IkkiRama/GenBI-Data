<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ["id"];
    protected $appends = ['is_finished', 'status'];

    public function getIsFinishedAttribute()
    {
        return Carbon::now()->isAfter(Carbon::parse($this->tanggal));
    }

    public function getStatusAttribute()
    {
        return $this->is_finished ? 'Event Sudah Berakhir' : 'Pendaftaran Masih Dibuka';
    }

    public function pemateri(): HasMany
    {
        return $this->hasMany(Pemateri::class);
    }
}
