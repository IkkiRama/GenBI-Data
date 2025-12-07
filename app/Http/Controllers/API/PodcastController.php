<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
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
            ], 200); // Kode 200 artinya: permintaan berhasil diproses

        } catch (\Exception $e) {
            // Jika terjadi kesalahan pada saat proses (misalnya masalah query database)
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ], 500); // Kode 500 artinya: kesalahan pada server
        }
    }
}
