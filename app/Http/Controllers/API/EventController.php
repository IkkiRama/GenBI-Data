<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Menampilkan semua event secara berurutan berdasarkan tanggal terdekat.
     *
     * Digunakan untuk halaman daftar event pada frontend.
     *
     * @OA\Get(
     *     path="/api/event",
     *     summary="Get all events ordered by date",
     *     tags={"Event"},
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="No events found")
     * )
     */
    public function index(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Mengambil semua event yang belum dihapus dan urut berdasarkan tanggal terdekat
            $events = Event::orderBy('tanggal', 'asc')
                ->withoutTrashed()
                ->get();

            // Cek jika data kosong
            if ($events->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Event tidak ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $events,
                "message" => "Event berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * Menampilkan maksimal 3 event yang akan datang untuk homepage.
     * Jika tidak ada event mendatang → tampilkan 3 event terakhir yang sudah lewat.
     *
     * @OA\Get(
     *     path="/api/event/home",
     *     summary="Get 3 upcoming events for homepage (fallback recent past events)",
     *     tags={"Event"}
     * )
     */
    public function homeEvent(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Ambil event mendatang
            $events = Event::where('tanggal', '>=', now())
                ->orderBy('tanggal', 'asc')
                ->take(3)
                ->withoutTrashed()
                ->get();

            // Jika tidak ada event mendatang → ambil event yang sudah lewat
            if ($events->isEmpty()) {
                $events = Event::where('tanggal', '<', now())
                    ->orderBy('tanggal', 'desc')
                    ->take(3)
                    ->withoutTrashed()
                    ->get();
            }

            return response()->json([
                "success" => true,
                "data" => $events,
                "message" => $events->isEmpty()
                    ? "Tidak ada event tersedia"
                    : "Event berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * Menampilkan 3 event rekomendasi secara acak.
     *
     * @OA\Get(
     *     path="/api/event/rekomendasi",
     *     summary="Get recommended random events",
     *     tags={"Event"}
     * )
     */
    public function rekomendasiEvent(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Pilih 3 event mendatang secara acak
            $events = Event::where('tanggal', '>=', now())
                ->inRandomOrder()
                ->take(3)
                ->withoutTrashed()
                ->get();

            // Jika tidak ada event mendatang → ambil acak semua event
            if ($events->isEmpty()) {
                $events = Event::inRandomOrder()
                    ->take(3)
                    ->withoutTrashed()
                    ->get();
            }

            return response()->json([
                "success" => true,
                "data" => $events,
                "message" => "Event rekomendasi berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * Menampilkan detail satu event berdasarkan slug.
     *
     * Data juga dilengkapi dengan relasi pemateri.
     *
     * @param string $slug
     *
     * @OA\Get(
     *     path="/api/event/{slug}",
     *     summary="Get event detail by slug",
     *     tags={"Event"},
     *     @OA\Parameter(name="slug", in="path", required=true)
     * )
     */
    public function show(string $slug): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Ambil detail event dengan relasi pemateri
            $event = Event::where('slug', $slug)
                ->with('pemateri')
                ->first();

            if (!$event) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Event tidak ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $event,
                "message" => "Event berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }



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
     * Helper: Response Error Standarisasi
     *
     * Mengembalikan response error yang seragam untuk memudahkan debugging.
     */
    private function errorResponse($exception, $headers)
    {
        return response()->json([
            "success" => false,
            "data" => null,
            "message" => "Server error: " . $exception->getMessage()
        ], 500, $headers);
    }
}
