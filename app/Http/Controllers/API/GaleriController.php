<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GaleriController extends Controller
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

    public function index(): JsonResponse
    {
        $headers = $this->corsHeaders();

        $galeri = Galeri::select('title','waktu','slug','thumbnail','tempat','deskripsi')
            ->orderBy('waktu', 'desc')
            ->withoutTrashed()
            ->get();

        if ($galeri->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Tidak ada galeri tersedia"
            ], 404, $headers);
        }

        return response()->json([
            "success" => true,
            "data" => $galeri,
            "message" => "Galeri berhasil ditampilkan"
        ], 200, $headers);
    }

    public function show(string $slug): JsonResponse
    {
        $headers = $this->corsHeaders();

        try {
            $galeri = Galeri::select('id','title','waktu','slug','thumbnail','tempat','deskripsi')
                ->where('slug', $slug)
                ->with('image_galeri')
                ->firstOrFail();

            return response()->json([
                "success" => true,
                "data" => $galeri,
                "message" => "Galeri {$slug} berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Galeri tidak ditemukan"
            ], 404, $headers);
        }
    }
}
