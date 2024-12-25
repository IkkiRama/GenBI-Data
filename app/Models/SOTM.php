<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SOTM extends Model
{
    use SoftDeletes;
    protected $guarded = ["id"];
    protected $table = "sotms";
}