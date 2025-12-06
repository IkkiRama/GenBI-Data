<?php

namespace App\Http\Controllers\API;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class KategoriArtikelController extends Controller
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

        $kategoriArtikel = KategoriArtikel::withoutTrashed()
            ->orderBy('created_at', 'desc')
            ->get();

        if ($kategoriArtikel->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Kategori artikel tidak ditemukan"
            ], 404, $headers);
        }

        return response()->json([
            "success" => true,
            "data" => $kategoriArtikel,
            "message" => "Kategori artikel berhasil ditampilkan"
        ], 200, $headers);
    }

    public function show(string $slug): JsonResponse
    {
        $headers = $this->corsHeaders();

        try {
            $kategori = KategoriArtikel::where("slug", $slug)
                ->withoutTrashed()
                ->firstOrFail();

            $artikel = Artikel::where("kategori_id", $kategori->id)
                ->withoutTrashed()
                ->select('id', 'judul', 'slug', 'thumbnail', 'created_at')
                ->latest()
                ->get();

            return response()->json([
                "success" => true,
                "data" => [
                    "kategori" => $kategori,
                    "artikel" => $artikel
                ],
                "message" => "Kategori artikel {$slug} berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Kategori artikel tidak ditemukan"
            ], 404, $headers);
        }
    }
}
