<?php

namespace App\Imports;

use App\Models\Struktur;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportStruktur implements ToModel
{
    public function model(array $row)
    {
        return new Struktur([
            'nama_lengkap' => $row[0],
            'type' => $row[1],
            'jabatan' => $row[2],
            'periode' => $row[3],
            'universitas' => $row[4],
            'quote' => $row[5],
            'foto' => $row[6],
        ]);
    }
}
