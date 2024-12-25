<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

            $artikel = Artikel::select('title','views', "slug", "thumbnail", "excerpt", "published_at", "author_id", "kategori_id")->withoutTrashed()->with("kategori_artikel", "user")->where("is_published", 1)->latest()->paginate(7);

            if (empty($artikel)) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Artikel Tidak Ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $artikel,
                "message" => "Artikel Berhasil Ditampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi Kesalahan pada Server: " . $e->getMessage()
            ], 500, $headers);

        }

    }

    /**
     * Randomly recommends 4 articles.
     */
    public function rekomendasi(): JsonResponse
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {
            // Mengambil 3 artikel secara acak
            $artikels = Artikel::select('title','views', "slug", "thumbnail", "excerpt", "published_at", "author_id", "kategori_id")->withoutTrashed()->inRandomOrder()->limit(4)->get();

            return response()->json([
                'success' => true,
                'data' => $artikels,
                'message' => 'Artikel berhasil diambil secara acak.'
            ], 200, $headers);
        } catch (\Exception $e) {
            // Tangani error
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500, $headers);
        }
    }

    /**
     * Retrieve the 4 most recent articles for the home page.
     */
    public function homeArtikel(): JsonResponse
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {
            // Mengambil 4 artikel terbaru berdasarkan created_at
            $artikels = Artikel::select('title','views', "slug", "thumbnail", "excerpt", "published_at", "author_id", "kategori_id")
            ->withoutTrashed()
            ->with("kategori_artikel", "user")
            ->where("is_published", 1)
            ->orderBy('created_at', 'desc')
            ->limit(4)->get();

            return response()->json([
                'success' => true,
                'data' => $artikels,
                'message' => '4 artikel terbaru berhasil diambil.'
            ], 200, $headers);
        } catch (\Exception $e) {
            // Tangani error
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500, $headers);
        }
    }

    public function getRandomArtikel() : JsonResponse {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        // Hitung tanggal satu bulan ke belakang
        $oneMonthAgo = Carbon::now()->subMonth();

        try{

            // Ambil artikel dengan views tertinggi selama satu bulan terakhir
            $trendingArtikel = Artikel::
                select('title','views', "slug", "thumbnail", "excerpt", "published_at", "author_id", "kategori_id")
                ->withoutTrashed()
                ->where('is_published', true)
                ->inRandomOrder()
                ->where('published_at', '>=', $oneMonthAgo) // Artikel yang diterbitkan dalam 1 bulan terakhir
                ->with("kategori_artikel", "user")
                ->limit(3) // Batasi jumlah artikel
                ->get();

            return response()->json([
                'success' => true,
                'data' => $trendingArtikel,
                'message' => 'Artikel Trending Bulanan Berhasil Ditampilkan',
            ], 200, $headers);

        }catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi Kesalahan pada Server: " . $e->getMessage()
            ], 500, $headers);

        }

    }

    public function getTrendingMonthlyArtikel() : JsonResponse {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        // Hitung tanggal satu bulan ke belakang
        $oneMonthAgo = Carbon::now()->subMonth();

        try{

            // Ambil artikel dengan views tertinggi selama satu bulan terakhir
            $trendingArtikel = Artikel::where('is_published', true)
                ->withoutTrashed()
                ->where('published_at', '>=', $oneMonthAgo) // Artikel yang diterbitkan dalam 1 bulan terakhir
                ->orderBy('views', 'desc') // Urutkan berdasarkan views tertinggi
                ->limit(10) // Batasi jumlah artikel
                ->get();

            return response()->json([
                'success' => true,
                'data' => $trendingArtikel,
                'message' => 'Artikel Trending Bulanan Berhasil Ditampilkan',
            ], 200, $headers);

        }catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Terjadi Kesalahan pada Server: " . $e->getMessage()
            ], 500, $headers);

        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }



    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

            $artikel = Artikel::where([
                ["slug", $slug],
                ["is_published", 1]
            ])->withoutTrashed()->with("kategori_artikel", "user")->firstOrFail();

            if (empty($artikel)) {
                throw new \Exception("Artikel tidak ditemukan.");
            }

            $newViews = $artikel->views + 1;
            $artikel->update([
                "views" => $newViews
            ]);

            return response()->json([
                "success" => true,
                "data" => $artikel,
                "message" => "Artikel ".$slug." Berhasil di Tampilkan"
            ], 200, $headers);

        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "data" => null,
                "message" => "Error: " . $e->getMessage()
            ], 500, $headers);

        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
