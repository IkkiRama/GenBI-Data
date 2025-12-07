<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GaleriController extends Controller
{
    /**
     * Mengatur header CORS untuk memastikan hanya domain yang diizinkan yang dapat
     * mengakses endpoint API ini.
     *
     * Jika origin tidak terdaftar pada .env → request akan ditolak dengan status 403.
     *
     * @return array Daftar header CORS yang diperbolehkan
     */
    private function corsHeaders(): array
    {
        // Mengambil daftar domain yang diizinkan dari konfigurasi environment
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);

        // Mengambil origin dari header request client
        $origin = request()->header('Origin');

        // Jika origin tidak cocok → tolak akses
        if (!in_array($origin, $allowedDomains)) {
            abort(403, 'Origin tidak diizinkan.');
        }

        // Jika valid → berikan akses
        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }

    /**
     * Menampilkan seluruh data galeri yang tersedia (tidak soft delete).
     *
     * Data ditampilkan diurutkan berdasarkan waktu terbaru.
     *
     * @return JsonResponse Response JSON daftar galeri atau pesan error jika kosong.
     */
    public function index(): JsonResponse
    {
        $headers = $this->corsHeaders();

        // Mengambil daftar galeri yang aktif dengan field yang diperlukan
        $galeri = Galeri::select('title','waktu','slug','thumbnail','tempat','deskripsi')
            ->orderBy('waktu', 'desc')
            ->withoutTrashed()
            ->get();

        // Jika tidak ada data → tampilkan pesan kosong
        if ($galeri->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Tidak ada galeri tersedia"
            ], 404, $headers);
        }

        // Jika data ada → tampilkan response sukses
        return response()->json([
            "success" => true,
            "data" => $galeri,
            "message" => "Galeri berhasil ditampilkan"
        ], 200, $headers);
    }

    /**
     * Menampilkan detail galeri berdasarkan slug.
     *
     * Data galeri akan dilengkapi dengan relasi gambar-gambar yang terhubung (image_galeri).
     *
     * @param string $slug Slug unik galeri
     * @return JsonResponse Response JSON detail galeri atau pesan error jika tidak ditemukan
     */
    public function show(string $slug): JsonResponse
    {
        $headers = $this->corsHeaders();

        try {
            // Mencari data galeri berdasarkan slug + relasi gambar
            $galeri = Galeri::select('id','title','waktu','slug','thumbnail','tempat','deskripsi')
                ->where('slug', $slug)
                ->with('image_galeri') // mengambil relasi gambar terkait galeri
                ->firstOrFail();

            // Jika ditemukan → kembalikan data
            return response()->json([
                "success" => true,
                "data" => $galeri,
                "message" => "Galeri {$slug} berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception) {
            // Jika slug tidak ditemukan → kembalikan error 404
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Galeri tidak ditemukan"
            ], 404, $headers);
        }
    }
}
