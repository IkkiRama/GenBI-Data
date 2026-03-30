<?php

use App\Http\Controllers\API\ArtikelController;
use App\Http\Controllers\API\DeveloperController;
use App\Http\Controllers\API\GaleriController;
use App\Http\Controllers\API\KategoriArtikelController;
use App\Http\Controllers\API\KontakController;
use App\Http\Controllers\API\PodcastController;
use App\Http\Controllers\API\SOTMController;
use App\Http\Controllers\API\StrukturController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::get('/artikel', [ArtikelController::class, 'index']);
Route::get('/artikel/rekomendasi', [ArtikelController::class, 'rekomendasi']);
Route::get('/artikel/rekomendasi-per-page', [ArtikelController::class, 'getRandomArtikel']);
Route::get('/artikel/homeArtikel', [ArtikelController::class, 'homeArtikel']);
Route::get('/artikel/artikelTerbaruDetailArtikel', [ArtikelController::class, 'artikelTerbaruDetailArtikel']);
Route::get('/artikel/trending-monthly', [ArtikelController::class, 'getTrendingMonthlyArtikel']);
Route::get('/artikel/{slug}', [ArtikelController::class, 'show']);
Route::get('/artikel/kategori/{slug}', [ArtikelController::class, 'byKategori']);

Route::get('/kategori-artikel', [KategoriArtikelController::class, 'index']);
Route::get('/kategori-artikel/{slug}', [KategoriArtikelController::class, 'show']);

Route::get('/struktur', [StrukturController::class, 'index']);
Route::get('/sejarah-kepengurusan', [StrukturController::class, 'sejarahKepengurusan']);
Route::get('/struktur/{periode}', [StrukturController::class, 'sejarahPerKepengurusan']);
Route::get('/struktur/{periode}/{jabatan}', [StrukturController::class, 'show']);

Route::get('/podcast', [PodcastController::class, 'index']);
Route::get('/podcast/{slug}', [PodcastController::class, 'show']);

Route::get('/event', [EventController::class, 'index']);
Route::get('/event/homeEvent', [EventController::class, 'homeEvent']);
Route::get('/event/rekomendasiEvent', [EventController::class, 'rekomendasiEvent']);
Route::get('/event/{slug}', [EventController::class, 'show']);

//
Route::get('/galeri', [GaleriController::class, 'index']);
Route::get('/galeri/{slug}', [GaleriController::class, 'show']);

Route::get('/sotm', [SOTMController::class, 'index']);
Route::get('/sotm/{periode}', [SOTMController::class, 'byPeriode']);


Route::get('/developer', [DeveloperController::class, 'index']);
Route::get('/search', [SearchController::class, 'index']);

Route::get('/kuis', [QuizController::class, 'index']);
Route::get('/kuis/{uuid}', [QuizController::class, 'start']);

Route::post('/kontak', [KontakController::class, 'store']);
Route::post('/komen', [KomentarController::class, 'store']);

Route::post('/auth/google', [AuthController::class, 'googleLogin']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->user()
    ]);
});

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json(['success' => true]);
})->middleware('auth:sanctum');
