<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use Illuminate\Http\Request;

class KomentarController extends Controller
{
     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'allowed_origins' => ['http://genbi.test'],
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'supports_credentials' => true,
        ];

        $validated = $request->validate([
            'artikel_id'  => 'required|integer|numeric',
            'nama'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'komentar' => 'required|string',
        ]);

        // Simpan data ke dalam database
        $contact = Komentar::create($validated);

        // Kembalikan response
        return response()->json([
            'success' => true,
            'message' => 'Kontak berhasil disimpan.',
            'data'    => $contact,
        ], 201, $headers);
    }
}
