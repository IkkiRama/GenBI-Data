<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use Illuminate\Http\Request;

class KomentarController extends Controller
{
     /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'artikel_id' => 'required|exists:artikels,id',
                'komentar'   => 'required|string',
            ]);

            $komentar = Komentar::create([
                'artikel_id' => $validated['artikel_id'],
                'user_id'    => auth()->id(),
                'komentar'   => $validated['komentar'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dikirim',
                'data' => [
                    'id' => $komentar->id,
                    'komentar' => $komentar->komentar,
                    'created_at' => $komentar->created_at->format('Y-m-d H:i'),
                    'user' => [
                        'id' => auth()->user()->id,
                        'name' => auth()->user()->name,
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Server error',
            ], 500);
        }
    }
}
