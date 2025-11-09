<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Struktur;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StrukturController extends Controller
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
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            // Penentuan periode sekarang
            if ($currentMonth < 4) {
                $previousYear = $currentYear - 1;
                $periode = "$previousYear-$currentYear";
            } else {
                $nextYear = $currentYear + 1;
                $periode = "$currentYear-$nextYear";
            }

            // Ambil struktur berdasarkan periode sekarang
            $struktur = Struktur::withoutTrashed()->where("periode", $periode)->latest()->get();

            // Jika kosong, ambil dari periode tahun sebelumnya
            if ($struktur->isEmpty()) {
                if ($currentMonth < 4) {
                    $periodeFallback = ($previousYear - 1) . "-$previousYear";
                } else {
                    $periodeFallback = ($currentYear - 1) . "-$currentYear";
                }

                $struktur = Struktur::withoutTrashed()->where("periode", $periodeFallback)->latest()->get();

                if ($struktur->isEmpty()) {
                    return response()->json([
                        "success" => false,
                        "data" => null,
                        "message" => "Struktur Tidak Ditemukan"
                    ], 404, $headers);
                }

                return response()->json([
                    "success" => true,
                    "data" => $struktur,
                    "message" => "Struktur Ditampilkan dari Periode Sebelumnya"
                ], 200, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $struktur,
                "message" => "Struktur Berhasil Ditampilkan"
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
    public function show(string $periode, string $jabatan)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

            $struktur = Struktur::where([
                "periode" => $periode,
                "jabatan" => $jabatan
            ])->firstOrFail();

            $member = Member::where("struktur_id", $struktur->id)->get();

            if (empty($struktur)) {
                throw new \Exception("Struktur tidak ditemukan.");
            }

            return response()->json([
                "success" => true,
                "data" => [
                    "struktur" => $struktur,
                    "member" => $member
                ],
                "message" => "Struktur Berhasil di Tampilkan"
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
     * Menampilkan kepengurusan GenBI Purwokerto per periode
     */
    public function sejarahPerKepengurusan(string $periode)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try {

            $struktur = Struktur::where("periode", $periode)->get();

            if (empty($struktur)) {
                return response()->json([
                    "success" => false,
                    "data" => null,
                    "message" => "Struktur Tidak Ditemukan"
                ], 404, $headers);
            }

            return response()->json([
                "success" => true,
                "data" => $struktur,
                "message" => "Struktur Berhasil Ditampilkan"
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
     * Menampilkan seluruh kepengurusan GenBI Purwokerto
     */
    public function sejarahKepengurusan()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Powered-By' => 'Rifki Romadhan',
            'X-Content-Language' => 'id',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        try{

            $data = Struktur::select('periode')->distinct()->get();

            if (empty($data)) {
                throw new \Exception("Struktur tidak ditemukan.");
            }

            $dataKepengurusan = $data->map(function ($item) {
                return [
                    'nama' => "Kepengurusan GenBI Purwokerto {$item->periode}",
                    'periode' => $item->periode,
                ];
            });

            return response()->json([
                "success" => true,
                "data" => $dataKepengurusan,
                "message" => "Struktur Berhasil di Tampilkan"
            ], 200, $headers);


        }catch(\Exception $e){
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
