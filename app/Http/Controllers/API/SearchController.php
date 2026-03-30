<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Artikel;
use App\Models\Podcast;
use App\Models\Quiz;
use App\Models\Galeri;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->q);

        if (!$q) {
            return response()->json([
                'artikels' => [],
                'podcasts' => [],
                'quizzes' => [],
                'galeris' => []
            ]);
        }

        // limit biar ringan
        $limit = 5;

        // ======================
        // ARTIKEL
        // ======================
        $artikels = Artikel::query()
            ->select('id', 'title', 'slug', 'thumbnail', 'excerpt')
            ->where('is_published', 1)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                      ->orWhere('keyword', 'like', "%$q%")
                      ->orWhere('excerpt', 'like', "%$q%");
            })
            ->orderByRaw("
                (title LIKE ?) DESC,
                (keyword LIKE ?) DESC
            ", ["%$q%", "%$q%"])
            ->limit($limit)
            ->get();

        // ======================
        // PODCAST
        // ======================
        $podcasts = Podcast::query()
            ->select('id', 'title', 'slug', 'thumbnail')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%");
            })
            ->limit($limit)
            ->get();

        // ======================
        // QUIZ
        // ======================
        $quizzes = Quiz::query()
            ->select('id', 'uuid', 'title')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%");
            })
            ->limit($limit)
            ->get();

        // ======================
        // GALERI
        // ======================
        $galeris = Galeri::query()
            ->select('id', 'title', 'slug', 'thumbnail')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                      ->orWhere('deskripsi', 'like', "%$q%");
            })
            ->limit($limit)
            ->get();

        return response()->json([
            'artikels' => $artikels,
            'podcasts' => $podcasts,
            'quizzes' => $quizzes,
            'galeris' => $galeris
        ]);
    }
}
