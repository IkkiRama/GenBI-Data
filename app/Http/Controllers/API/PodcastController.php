<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
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

            $podcast = Podcast::withoutTrashed()->get();

            if (empty($podcast)) {
                throw new \Exception("Struktur tidak ditemukan.");
            }

            return response()->json([
                "success" => true,
                "data" => $podcast,
                "message" => "Podcast Berhasil di Tampilkan"
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
    public function show(string $id)
    {
        //
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
