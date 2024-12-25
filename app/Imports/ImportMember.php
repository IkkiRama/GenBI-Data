<?php

namespace App\Imports;

use App\Models\Member;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportMember implements ToModel
{
    public function model(array $row)
    {
        return new Member([
            'struktur_id' => $row[0],
            'nama' => $row[1],
            'universitas' => $row[2],
            'bidang' => $row[3],
            'departemen' => $row[4],
        ]);
    }
}
