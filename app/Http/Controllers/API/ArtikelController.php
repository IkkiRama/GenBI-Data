<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\KategoriArtikel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{

    /**
     * Menampilkan daftar artikel yang sudah dipublikasikan (paginate).
     * - Menampilkan 7 artikel per halaman
     * - Hanya artikel published
     * - Termasuk relasi kategori & user
     */
    public function index(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Ambil artikel terbaru, hanya data penting
            $artikel = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed() // Tidak ambil yang sudah soft-delete
                ->with([
                    'kategori_artikel:id,nama,slug',   // ambil hanya id, nama, slug
                    'user:id,name'                     // ambil hanya id, name
                ]) // join relasi
                ->where('is_published', 1) // hanya publish
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
     * Mengambil 4 artikel secara acak (rekomendasi) yang sudah dipublikasikan
     */
    public function rekomendasi(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Random 4 artikel
            $artikels = Artikel::select('title','views','slug','thumbnail','excerpt','published_at')
                ->withoutTrashed()
                ->where('is_published', true)
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
     * Mengambil 4 artikel terbaru untuk halaman home
     */
    public function homeArtikel(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Artikel terbaru, max 4
            $artikels = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->with([
                    'kategori_artikel:id,nama,slug',   // ambil hanya id, nama, slug
                    'user:id,name'                     // ambil hanya id, name
                ])
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
     * Mengambil 4 artikel terbaru untuk halaman home
     */
    public function artikelTerbaruDetailArtikel(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Artikel terbaru, max 3
            $artikels = Artikel::select('title','views','slug','thumbnail','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->with([
                    'kategori_artikel:id,nama,slug',   // ambil hanya id, nama, slug
                    'user:id,name'                     // ambil hanya id, name
                ])
                ->where('is_published', 1)
                ->latest()
                ->limit(3)
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
     * Mengambil 3 artikel acak yang sudah dipublikasikan
     */
    public function getRandomArtikel(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Random artikel
            $trendingArtikel = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->where('is_published', true)
                ->inRandomOrder()
                ->with([
                    'kategori_artikel:id,nama,slug',   // ambil hanya id, nama, slug
                    'user:id,name'                     // ambil hanya id, name
                ])
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
     * Mendapatkan top 10 trending artikel:
     * - Prioritas trending bulan ini
     * - Jika kosong → trending tahun ini
     * - Jika tetap kosong → trending sepanjang masa
     */
    public function getTrendingMonthlyArtikel(): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            $now = Carbon::now();
            $oneMonthAgo = $now->clone()->subMonth();
            $year = $now->year;

            // 🔥 Query dasar untuk reusable
            $baseQuery = Artikel::select('title','views','slug','thumbnail','excerpt','published_at','author_id','kategori_id')
                ->withoutTrashed()
                ->with([
                    'kategori_artikel:id,nama,slug',   // ambil hanya id, nama, slug
                    'user:id,name'                     // ambil hanya id, name
                ])
                ->where('is_published', 1)
                ->orderBy('views', 'desc');

            // 1️⃣ Trending 1 bulan terakhir
            $artikel = (clone $baseQuery)
                ->where('created_at', '>=', $oneMonthAgo)
                ->limit(10)
                ->get();

            $message = 'Trending bulan ini';

            // 2️⃣ Backup trending tahun ini
            if ($artikel->isEmpty()) {
                $artikel = (clone $baseQuery)
                    ->whereYear('created_at', $year)
                    ->limit(10)
                    ->get();

                $message = 'Trending tahun ini';
            }

            // 3️⃣ Backup trending sepanjang masa
            if ($artikel->isEmpty()) {
                $artikel = (clone $baseQuery)
                    ->limit(10)
                    ->get();

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
     * Menampilkan detail artikel berdasarkan slug
     * + Menambah jumlah views setiap kali dikunjungi
     */
    public function show($slug): JsonResponse
    {
        // Ambil domain asal request (untuk keperluan CORS)
        $allowedDomains = explode(',', $_ENV['VITE_CORS_DOMAINS']);
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Set header CORS
        $headers = $this->getCorsHeaders($allowedDomains, $origin);

        try {
            // Ambil detail artikel & relasinya
            $artikel = Artikel::where('slug', $slug)
                ->where('is_published', 1)
                ->withoutTrashed()
                ->with([
                    'komentar:artikel_id,nama,email,komentar',
                    'kategori_artikel:id,nama,slug',   // ambil hanya id, nama, slug
                    'user:id,name,deskripsi,email,foto'                     // ambil hanya id, name
                ]) // join relasi
                ->firstOrFail();

            // Tambahkan jumlah view untuk tracking
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


    public function byKategori(Request $request, $slug)
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
            // ambil kategori
            $kategori = KategoriArtikel::where('slug', $slug)->first();

            if (!$kategori) {
                return response()->json([
                    "success" => false,
                    "message" => "Kategori tidak ditemukan",
                    "data" => null
                ], 404, $headers);
            }

            // query artikel
            $query = Artikel::where('kategori_id', $kategori->id)
                ->where('is_published', true);

            // 🔍 search
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('keyword', 'like', '%' . $request->search . '%');
                });
            }

            // 🔥 filter keyword (tag style)
            if ($request->keyword) {
                $query->where('keyword', 'like', '%' . $request->keyword . '%');
            }

            // sorting terbaru
            $query->orderBy('published_at', 'desc');

            // pagination (infinite scroll)
            $artikels = $query->paginate(6);

            // 🔥 inject badge NEW (<= 7 hari)
            $artikels->getCollection()->transform(function ($item) {
                $item->is_new = now()->diffInDays($item->published_at) <= 7;
                return $item;
            });

            return response()->json([
                "success" => true,
                "kategori" => $kategori,
                "data" => $artikels->items(),
                "meta" => [
                    "current_page" => $artikels->currentPage(),
                    "last_page" => $artikels->lastPage(),
                    "has_more" => $artikels->hasMorePages()
                ]
            ], 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "data" => null
            ], 500, $headers);
        }
    }


    /**
     * Helper respon error standar server
     */
    private function errorResponse($e, $headers, $status = 500)
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Server Error: '.$e->getMessage()
        ], $status, $headers);
    }
}
