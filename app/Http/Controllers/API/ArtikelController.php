<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/artikel",
     *     summary="Get all published articles (paginated)",
     *     tags={"Artikel"},
     *     @OA\Response(
     *          response=200,
     *          description="Success"
     *      ),
     *     @OA\Response(response=404, description="No articles found")
     * )
     */
    public function index() : JsonResponse
    {
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            $artikel = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->with('kategori_artikel','user')
                ->where('is_published', 1)
                ->latest()
                ->paginate(7);

            if ($artikel->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Artikel tidak ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $artikel,
                "message" => "Artikel berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/artikel/rekomendasi",
     *     summary="Get 4 random recommended articles",
     *     tags={"Artikel"}
     * )
     */
    public function rekomendasi(): JsonResponse
    {
        $headers = $this->getCorsHeaders();

        try {
            $artikels = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->inRandomOrder()
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $artikels,
                'message' => 'Rekomendasi artikel berhasil ditampilkan'
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/artikel/home",
     *     summary="Get latest 4 published articles for homepage",
     *     tags={"Artikel"}
     * )
     */
    public function homeArtikel(): JsonResponse
    {
        $headers = $this->getCorsHeaders();

        try {
            $artikels = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->with('kategori_artikel','user')
                ->where('is_published', 1)
                ->latest()
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $artikels,
                'message' => 'Artikel terbaru berhasil diambil.'
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/artikel/random",
     *     summary="Get 3 random published articles",
     *     tags={"Artikel"}
     * )
     */
    public function getRandomArtikel(): JsonResponse
    {
        $headers = $this->getCorsHeaders();

        try {
            $trendingArtikel = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->where('is_published', true)
                ->inRandomOrder()
                ->with('kategori_artikel','user')
                ->limit(3)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $trendingArtikel,
                'message' => 'Artikel random berhasil ditampilkan'
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/artikel/trending",
     *     summary="Get top 10 trending articles monthly or fallback yearly/all time",
     *     tags={"Artikel"}
     * )
     */
    public function getTrendingMonthlyArtikel(): JsonResponse
    {
        $headers = $this->getCorsHeaders();

        try {
            $now = Carbon::now();
            $oneMonthAgo = $now->clone()->subMonth();
            $year = $now->year;

            // 1️⃣ Trending 1 bulan terakhir
            $artikel = Artikel::trendingSince($oneMonthAgo)->limit(10)->get();
            $message = 'Trending bulan ini';

            // 2️⃣ Fallback ke trending tahun berjalan
            if ($artikel->isEmpty()) {
                $artikel = Artikel::trendingThisYear($year)->limit(10)->get();
                $message = 'Trending tahun ini';
            }

            // 3️⃣ Fallback ke all-time trending
            if ($artikel->isEmpty()) {
                $artikel = Artikel::trendingAll()->limit(10)->get();
                $message = 'Trending sepanjang masa';
            }

            return response()->json([
                'success' => true,
                'data' => $artikel,
                'message' => $message
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/artikel/{slug}",
     *     summary="Show article detail",
     *     tags={"Artikel"},
     *     @OA\Parameter(
     *         name="slug", in="path", required=true
     *     )
     * )
     */
    public function show($slug): JsonResponse
    {
        $headers = $this->getCorsHeaders();

        try {
            $artikel = Artikel::where('slug', $slug)
                ->where('is_published', 1)
                ->withoutTrashed()
                ->with('komentar','kategori_artikel','user')
                ->firstOrFail();

            $artikel->increment('views');

            return response()->json([
                "success" => true,
                "data" => $artikel,
                "message" => "Detail artikel berhasil ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {
            return $this->errorResponse($e, $headers);
        }
    }


    /** Helper Method */
    private function getCorsHeaders($allowed = null, $origin = null)
    {
        $allowed = $allowed ?? explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $origin ?? ($_SERVER['HTTP_ORIGIN'] ?? '');

        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => in_array($origin, $allowed) ? $origin : 'null',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }

    private function errorResponse($e, $headers, $status = 500)
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Server Error: '.$e->getMessage()
        ], $status, $headers);
    }
}
