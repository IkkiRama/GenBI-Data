<?php

namespace App\Http\Controllers\API;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class KategoriArtikelController extends Controller
{
    /**
     * Mengatur header CORS untuk mengizinkan akses dari domain yang diizinkan.
     *
     * Method ini memeriksa apakah domain frontend yang melakukan request
     * termasuk dalam daftar domain yang diperbolehkan pada file .env.
     * Jika tidak sesuai → akses akan ditolak dengan status 403.
     *
     * @return array Header yang diperbolehkan untuk CORS.
     */
    private function corsHeaders(): array
    {
        // Mengambil daftar domain yang diizinkan (dipisah menjadi array)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);

        // Mengambil origin dari request header
        $origin = request()->header('Origin');

        // Jika origin tidak diizinkan → tolak akses
        if (!in_array($origin, $allowedDomains)) {
            $origin = 'null';
        }

        // Jika origin valid → kembalikan daftar header yang boleh digunakan
        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }

    /**
     * Menampilkan daftar seluruh kategori artikel yang masih aktif (tidak soft delete).
     *
     * Digunakan untuk kebutuhan dropdown kategori, daftar kategori di frontend,
     * serta filter artikel berdasarkan kategori.
     *
     * @return \Illuminate\Http\JsonResponse Response JSON berisi daftar kategori atau pesan error.
     */
    public function index(): JsonResponse
    {
        // Mengambil header CORS yang sudah divalidasi
        $headers = $this->corsHeaders();

        // Mengambil kategori yang belum dihapus dan urutkan terbaru
        $kategoriArtikel = KategoriArtikel::withoutTrashed()
            ->orderBy('created_at', 'desc')
            ->get();

        // Jika kategori kosong → kirim pesan data tidak ditemukan
        if ($kategoriArtikel->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Kategori artikel tidak ditemukan"
            ], 404, $headers);
        }

        // Jika data tersedia → kirim response sukses
        return response()->json([
            "success" => true,
            "data" => $kategoriArtikel,
            "message" => "Kategori artikel berhasil ditampilkan"
        ], 200, $headers);
    }

    /**
     * Menampilkan detail sebuah kategori berdasarkan slug,
     * sekaligus menampilkan daftar artikel yang ada dalam kategori tersebut.
     *
     * Jika kategori tidak ditemukan atau slug salah → akan dikembalikan response error.
     *
     * @param string $slug Slug kategori artikel.
     * @return \Illuminate\Http\JsonResponse Response JSON detail kategori & daftar artikel di dalamnya.
     */
    public function show(string $slug): JsonResponse
    {
        $headers = $this->corsHeaders();

        try {
            // Mencari kategori berdasarkan slug
            $kategori = KategoriArtikel::where("slug", $slug)
                ->withoutTrashed()
                ->firstOrFail();

            // Mengambil semua artikel yang terkait dengan kategori tersebut
            $artikel = Artikel::where("kategori_id", $kategori->id)
                ->withoutTrashed()
                ->select('id', 'judul', 'slug', 'thumbnail', 'created_at')
                ->latest() // urutkan terbaru
                ->get();

            // Kirim response sukses + data kategori dan artikel
            return response()->json([
                "success" => true,
                "data" => [
                    "kategori" => $kategori,
                    "artikel" => $artikel
                ],
                "message" => "Kategori artikel {$slug} berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception) {

            // Jika slug tidak valid atau data kategori tidak ditemukan
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Kategori artikel tidak ditemukan"
            ], 404, $headers);
        }
    }
}

