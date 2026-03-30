<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use Illuminate\Http\JsonResponse;

class DeveloperController extends Controller
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
        $allowedDomains = explode(',', env('VITE_CORS_DOMAINS'));
        $origin = request()->headers->get('Origin');

        if (!in_array($origin, $allowedDomains)) {
            $origin = 'null';
        }

        return [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Content-Type' => 'application/json',
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
        $developer = Developer::withoutTrashed()
            ->get();

        // Jika tidak ada data → tampilkan pesan kosong
        if ($developer->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Tidak ada data developer yang tersedia"
            ], 404, $headers);
        }

        // Jika data ada → tampilkan response sukses
        return response()->json([
            "success" => true,
            "data" => $developer,
            "message" => "Data developer berhasil ditampilkan"
        ], 200, $headers);
    }

}
