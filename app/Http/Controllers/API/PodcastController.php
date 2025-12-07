<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    /**
     * Mengatur header CORS untuk mengizinkan akses dari domain yang diizinkan.
     *
     * Method ini memeriksa apakah origin pada request masuk dalam daftar domain
     * yang diperbolehkan pada file .env melalui variabel VITE_CORS_DOMAINS.
     * Jika tidak diizinkan → akses akan ditolak dengan HTTP 403.
     *
     * @param array|null $allowed Daftar domain yang diperbolehkan (opsional).
     * @param string|null $origin Origin yang masuk via request header (opsional).
     * @return array Header yang diperbolehkan untuk CORS.
     */
    private function getCorsHeaders($allowed = null, $origin = null): array
    {
        // Ambil daftar domain yang diperbolehkan (dipisah dengan koma di .env)
        $allowed = $allowed ?? explode(',', $_ENV['VITE_CORS_DOMAINS']);

        // Ambil Origin dari request (lebih aman dibanding $_SERVER['HTTP_ORIGIN'])
        $origin = $origin ?? request()->header('Origin', '');

        // Jika origin tidak ada di daftar allowed → tolak akses
        if (!in_array($origin, $allowed)) {
            $origin = 'null';
        }

        // Jika lolos validasi → kembalikan header
        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }


    /**
     * Menampilkan seluruh data podcast yang masih aktif (tidak terhapus secara soft delete).
     *
     * Method ini digunakan untuk kebutuhan penarikan data di bagian aplikasi / API client.
     * Jika tidak ada data ditemukan, akan mengembalikan response 404.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Mengambil semua data podcast yang belum dihapus (soft delete)
            $podcasts = Podcast::withoutTrashed()->get();

            // Jika data kosong → kembalikan pesan bahwa data tidak ditemukan
            if ($podcasts->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => [],
                    "message" => "Tidak ada data podcast."
                ], 404); // Kode 404 artinya: data tidak ditemukan
            }

            // Jika data tersedia → kirim response sukses beserta datanya
            return response()->json([
                "success" => true,
                "data" => $podcasts,
                "message" => "Podcast berhasil ditampilkan."
            ], 200, $headers); // Kode 200 artinya: permintaan berhasil diproses

        } catch (\Exception $e) {
            // Jika terjadi kesalahan pada saat proses (misalnya masalah query database)
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ], 500, $headers); // Kode 500 artinya: kesalahan pada server
        }
    }
}
