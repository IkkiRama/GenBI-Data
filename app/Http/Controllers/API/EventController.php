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
        $headers = $this->getCorsHeaders();

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
        $headers = $this->getCorsHeaders();

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
        $headers = $this->getCorsHeaders();

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
        $headers = $this->getCorsHeaders();

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
     * Helper untuk validasi CORS Header.
     */
    private function getCorsHeaders()
    {
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $requestOrigin  = $_SERVER['HTTP_ORIGIN'] ?? '';

        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => in_array($requestOrigin, $allowedDomains)
                ? $requestOrigin
                : 'null',
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
