<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Struktur;

class StrukturController extends Controller
{
    /**
     * Menampilkan struktur aktif berdasarkan periode saat ini
     * - Periode aktif: April - Maret tahun berikutnya
     * - Jika data periode aktif kosong → fallback ke periode sebelumnya
     */
    public function index()
    {
        try {
            // Ambil tahun & bulan sekarang
            $currentYear = now()->year;
            $currentMonth = now()->month;

            /**
             * Penentuan periode:
             * - Jika bulan < April → masih masuk periode tahun sebelumnya
             *   contoh: Jan 2025 → periode: 2024-2025
             * - Jika bulan >= April → masuk periode tahun berjalan
             *   contoh: Juli 2025 → periode: 2025-2026
             */
            if ($currentMonth < 4) {
                $period = ($currentYear - 1) . "-$currentYear";
            } else {
                $period = "$currentYear-" . ($currentYear + 1);
            }

            // Cek data struktur periode aktif
            $struktur = Struktur::withoutTrashed()
                ->where("periode", $period)
                ->latest()
                ->get();

            // Jika kosong → fallback ke periode sebelumnya
            if ($struktur->isEmpty()) {

                /**
                 * Periode fallback disesuaikan:
                 * - Jan - Maret → fallback -1 periode
                 * - April - Desember → fallback ke tahun berjalan sebelumnya
                 */
                $fallbackPeriod = ($currentMonth < 4)
                    ? (($currentYear - 2) . "-" . ($currentYear - 1))
                    : (($currentYear - 1) . "-" . $currentYear);

                $struktur = Struktur::withoutTrashed()
                    ->where("periode", $fallbackPeriod)
                    ->latest()
                    ->get();

                // Jika tetap tidak ada → kirim 404
                if ($struktur->isEmpty()) {
                    return response()->json([
                        "success" => false,
                        "data" => [],
                        "message" => "Struktur tidak ditemukan"
                    ], 404);
                }

                return response()->json([
                    "success" => true,
                    "data" => $struktur,
                    "message" => "Struktur ditampilkan dari periode sebelumnya"
                ]);
            }

            return response()->json([
                "success" => true,
                "data" => $struktur,
                "message" => "Struktur berhasil ditampilkan"
            ]);

        } catch (\Exception $e) {
            // Server error handling
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail struktur dan member berdasarkan periode & jabatan
     * - Digunakan untuk halaman detail masing-masing divisi/jabatan
     */
    public function show(string $periode, string $jabatan)
    {
        try {
            // compact() sama dengan ["periode" => $periode, "jabatan" => $jabatan]
            $struktur = Struktur::where(compact("periode", "jabatan"))->firstOrFail();

            // Ambil orang yang mengisi struktur tersebut
            $member = Member::where("struktur_id", $struktur->id)->get();

            return response()->json([
                "success" => true,
                "data" => [
                    "struktur" => $struktur,
                    "member" => $member
                ],
                "message" => "Detail struktur berhasil ditampilkan"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "data" => null,
                "message" => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Menampilkan struktur pada periode tertentu
     * - Dipakai untuk halaman sejarah per kepengurusan
     */
    public function sejarahPerKepengurusan(string $periode)
    {
        try {
            $struktur = Struktur::where("periode", $periode)->get();

            if ($struktur->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => [],
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            return response()->json([
                "success" => true,
                "data" => $struktur,
                "message" => "Struktur berhasil ditampilkan"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan semua periode yang pernah ada
     * - Untuk dropdown atau daftar riwayat kepengurusan
     */
    public function sejarahKepengurusan()
    {
        try {
            // Ambil hanya kolom periode, tanpa duplikasi
            $periodes = Struktur::select('periode')->distinct()->pluck('periode');

            if ($periodes->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "data" => [],
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            // Format data agar langsung siap pakai di frontend
            $data = $periodes->map(fn($p) => [
                'nama' => "Kepengurusan GenBI Purwokerto $p",
                'periode' => $p
            ]);

            return response()->json([
                "success" => true,
                "data" => $data,
                "message" => "Daftar periode berhasil ditampilkan"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }


}
