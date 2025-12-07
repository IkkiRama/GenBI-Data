<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SOTM;
use Illuminate\Http\Request;

class SOTMController extends Controller
{

    public function index()
    {
        /**
         * Ambil daftar domain yang diizinkan untuk akses API dari file .env
         * Bisa berbentuk string dipisahkan koma → lalu kita jadikan array
         * Contoh ENV: VITE_CORS_DOMAINS="https://domain.com,https://localhost:3000"
         */
        $allowedDomains = explode(',', env('VITE_CORS_DOMAINS'));

        /**
         * Ambil origin dari request header → domain frontend yang request API
         */
        $origin = request()->headers->get('Origin');

        /**
         * Validasi apakah origin tersebut ada di daftar domain yang diizinkan
         * Jika tidak cocok, set origin ke 'null' agar request tetap ditolak oleh CORS
         */
        if (!in_array($origin, $allowedDomains)) {
            $origin = 'null';
        }

        /**
         * Header CORS yang akan kita sertakan dalam response
         * - Mengizinkan domain tertentu (security)
         * - Menentukan allowed headers
         * - Menentukan respon berupa JSON
         */
        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Content-Type' => 'application/json',
        ];

        try {
            /**
             * Ambil semua data SOTM yang belum di soft-delete
             */
            $sotm = SOTM::withoutTrashed()->get();

            /**
             * Jika tidak ada data, berikan response 404 not found
             */
            if ($sotm->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => [],
                    "message" => "Tidak ada data SOTM."
                ], 404, $headers);
            }

            /**
             * Jika data ada → kirimkan respons sukses dengan data
             */
            return response()->json([
                "success" => true,
                "data" => $sotm,
                "message" => "SOTM berhasil ditampilkan."
            ], 200, $headers);

        } catch (\Exception $e) {

            /**
             * Jika ada error server yang tidak terduga
             * Kirim status 500 → Internal Server Error
             */
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Error: " . $e->getMessage()
            ], 500, $headers);
        }
    }
}
