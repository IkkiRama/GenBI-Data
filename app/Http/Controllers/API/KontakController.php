<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Kontak;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KontakController extends Controller
{
    private function corsHeaders(): array
    {
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = request()->header('Origin');

        if (!in_array($origin, $allowedDomains)) {
            abort(403, 'Origin tidak diizinkan.');
        }

        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $headers = $this->corsHeaders();

        $validated = $request->validate([
            'nama'  => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'judul' => 'required|string|min:3|max:255',
            'pesan' => 'required|string|min:5',
        ]);

        try {
            $contact = Kontak::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Kontak berhasil disimpan.',
                'data'    => $contact,
            ], 201, $headers);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data kontak.',
                'error'   => $e->getMessage(),
            ], 500, $headers);
        }
    }
}
