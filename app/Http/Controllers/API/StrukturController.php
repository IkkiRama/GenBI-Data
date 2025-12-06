public function index()
{
    try {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Tentukan periode aktif
        if ($currentMonth < 4) {
            $period = ($currentYear - 1) . "-$currentYear";
        } else {
            $period = "$currentYear-" . ($currentYear + 1);
        }

        // Ambil berdasarkan periode aktif
        $struktur = Struktur::withoutTrashed()
            ->where("periode", $period)
            ->latest()
            ->get();

        // Jika tidak ada → fallback ke periode sebelumnya
        if ($struktur->isEmpty()) {
            $fallbackPeriod = ($currentMonth < 4)
                ? (($currentYear - 2) . "-" . ($currentYear - 1))
                : (($currentYear - 1) . "-" . $currentYear);

            $struktur = Struktur::withoutTrashed()
                ->where("periode", $fallbackPeriod)
                ->latest()
                ->get();

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
        return response()->json([
            "success" => false,
            "data" => null,
            "message" => $e->getMessage()
        ], 500);
    }
}

public function show(string $periode, string $jabatan)
{
    try {
        $struktur = Struktur::where(compact("periode", "jabatan"))->firstOrFail();

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

public function sejarahKepengurusan()
{
    try {
        $periodes = Struktur::select('periode')->distinct()->pluck('periode');

        if ($periodes->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Data tidak ditemukan"
            ], 404);
        }

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
