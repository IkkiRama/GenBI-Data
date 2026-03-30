<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Developer extends Model
{
    use HasFactory, SoftDeletes;

    // Nama tabel (opsional kalau pakai konvensi Laravel)
    protected $table = 'developers';

    // Field yang boleh diisi mass assignment
    protected $fillable = [
        'nama',
        'role',
        'deskripsi_role',
        'periode',
        'sosmed_ig',
        'sosmed_wa',
        'image',
        'email',
    ];

    // Soft delete otomatis handle deleted_at
    protected $dates = ['deleted_at'];
}
