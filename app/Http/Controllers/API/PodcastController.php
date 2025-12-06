public function index()
{
    try {
        $podcasts = Podcast::withoutTrashed()->get();

        if ($podcasts->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Tidak ada data podcast."
            ], 404);
        }

        return response()->json([
            "success" => true,
            "data" => $podcasts,
            "message" => "Podcast berhasil ditampilkan."
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "success" => false,
            "data" => null,
            "message" => "Error: " . $e->getMessage()
        ], 500);
    }
}
