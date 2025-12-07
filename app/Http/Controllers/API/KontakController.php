<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Kontak;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KontakController extends Controller
{
    /**
     * Mengatur header CORS untuk mengizinkan akses dari domain frontend tertentu.
     *
     * Method ini memeriksa apakah origin yang mengakses API termasuk ke dalam daftar
     * domain yang diizinkan. Jika tidak, maka akan diblokir dengan status 403.
     *
     * @return array Header CORS yang diizinkan.
     */
    private function corsHeaders(): array
    {
        // Mengambil daftar domain yang diizinkan dari file .env (berbentuk string lalu dipecah menjadi array)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);

        // Mendapatkan origin dari header request yang masuk
        $origin = request()->header('Origin');

        // Jika origin tidak terdaftar dalam domain yang diizinkan, maka request ditolak
        if (!in_array($origin, $allowedDomains)) {
            abort(403, 'Origin tidak diizinkan.');
        }

        // Jika origin valid → kembalikan daftar header CORS yang diperbolehkan
        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }

    /**
     * Menyimpan data kontak yang dikirimkan dari form pada sisi frontend.
     *
     * Validasi dilakukan terlebih dahulu untuk memastikan data sesuai.
     * Jika sukses → data akan disimpan ke database dan dikembalikan response berhasil.
     * Jika gagal → akan dikembalikan response error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse Response JSON dengan status keberhasilan atau kegagalan.
     */
    public function store(Request $request): JsonResponse
    {
        // Mengambil header CORS yang sudah divalidasi domainnya
        $headers = $this->corsHeaders();

        // Validasi data input dari request
        $validated = $request->validate([
            'nama'  => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'judul' => 'required|string|min:3|max:255',
            'pesan' => 'required|string|min:5',
        ]);

        try {
            // Menyimpan data kontak ke database menggunakan mass assignment
            $contact = Kontak::create($validated);

            // Return response sukses beserta data yang tersimpan
            return response()->json([
                'success' => true,
                'message' => 'Kontak berhasil disimpan.',
                'data'    => $contact,
            ], 201, $headers); // Status 201 artinya "Created"

        } catch (\Exception $e) {
            // Menangani error jika terjadi kegagalan pada saat menyimpan data
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data kontak.',
                'error'   => $e->getMessage(), // Untuk debugging (opsional bisa dihilangkan pada production)
            ], 500, $headers); // Status 500 artinya "Server Error"
        }
    }
}

