<?php

namespace App\Http\Controllers\API;

use App\Models\Artikel;
use Illuminate\Http\Request;
use App\Models\KategoriArtikel;
use App\Http\Controllers\Controller;

class KategoriArtikelController extends Controller
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

            $kategoriArtikel = KategoriArtikel::withoutTrashed()->latest()->get();

            if (empty($kategoriArtikel)) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Kategori Artikel Tidak Ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $kategoriArtikel,
                "message" => "Kategori Artikel Berhasil Ditampilkan"
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
    public function show(string $slug)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

            $kategoriArtikel = KategoriArtikel::where("slug", $slug)->firstOrFail();
            $artikel = Artikel::where("kategori_id", $kategoriArtikel->id)->get();

            if (empty($kategoriArtikel)) {
                throw new \Exception("Kategori Artikel tidak ditemukan.");
            }

            return response()->json([
                "success" => true,
                "data" => [
                    "kategori" => $kategoriArtikel,
                    "artikel" => $artikel
                ],
                "message" => "Kategori Artikel ".$slug." Berhasil di Tampilkan"
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
