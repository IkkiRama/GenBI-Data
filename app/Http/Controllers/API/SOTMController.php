public function index()
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
        $sotm = SOTM::withoutTrashed()->get();

        if ($sotm->isEmpty()) {
            return response()->json([
                "success" => false,
                "data" => [],
                "message" => "Tidak ada data SOTM."
            ], 404, $headers);
        }

        return response()->json([
            "success" => true,
            "data" => $sotm,
            "message" => "SOTM berhasil ditampilkan."
        ], 200, $headers);

    } catch (\Exception $e) {
        return response()->json([
            "success" => false,
            "data" => null,
            "message" => "Error: " . $e->getMessage()
        ], 500, $headers);
    }
}
