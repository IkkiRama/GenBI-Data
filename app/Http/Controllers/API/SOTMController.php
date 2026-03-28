<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SOTM;
use Illuminate\Http\Request;

class SOTMController extends Controller
{

    public function index()
    {
        $allowedDomains = explode(',', env('VITE_CORS_DOMAINS'));
        $origin = request()->headers->get('Origin');

        if (!in_array($origin, $allowedDomains)) {
            $origin = 'null';
        }

        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Content-Type' => 'application/json',
        ];

        try {
            // Tentuin periode sekarang & tahun lalu
            $currentYear = date('Y');
            $currentPeriode = $currentYear . '-' . ($currentYear + 1);
            $lastPeriode = ($currentYear - 1) . '-' . $currentYear;

            // 1️⃣ Coba ambil periode sekarang
            $sotm = SOTM::where('periode', $currentPeriode)->get();

            // 2️⃣ Kalau kosong → ambil tahun lalu
            if ($sotm->isEmpty()) {
                $sotm = SOTM::where('periode', $lastPeriode)->get();
            }

            // 3️⃣ Kalau masih kosong → ambil periode terakhir di DB
            if ($sotm->isEmpty()) {
                $latestPeriode = SOTM::select('periode')
                    ->orderByDesc('periode')
                    ->first();

                if ($latestPeriode) {
                    $sotm = SOTM::where('periode', $latestPeriode->periode)->get();
                }
            }

            // ❌ Kalau tetap kosong
            if ($sotm->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => [],
                    "message" => "Tidak ada data SOTM."
                ], 404, $headers);
            }

            // ✅ Transform image biar langsung bisa dipakai frontend
            $sotm->transform(function ($item) {
                $item->image_url = asset('storage/' . $item->image);
                return $item;
            });

            return response()->json([
                "success" => true,
                "data" => $sotm,
                "message" => "SOTM berhasil ditampilkan."
            ], 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Error: " . $e->getMessage()
            ], 500, $headers);
        }
    }

    public function byPeriode($periode)
    {
        try {
            $data = SOTM::query()
                ->where('periode', $periode)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data SOTM',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
