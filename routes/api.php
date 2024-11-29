<?php

use App\Http\Controllers\API\ArtikelController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GaleriController;
use App\Http\Controllers\API\KategoriArtikelController;
use App\Http\Controllers\API\KontakController;
use App\Http\Controllers\API\PodcastController;
use App\Http\Controllers\API\SOTMController;
use App\Http\Controllers\API\StrukturController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(["middleware" => "auth:sanctum"], function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('user', UserController::class);
});


Route::get('/artikel', [ArtikelController::class, 'index']);
Route::get('/artikel/{slug}', [ArtikelController::class, 'show']);

Route::get('/kategori-artikel', [KategoriArtikelController::class, 'index']);
Route::get('/kategori-artikel/{slug}', [KategoriArtikelController::class, 'show']);

Route::get('/struktur', [StrukturController::class, 'index']);
Route::get('/struktur/{id}', [StrukturController::class, 'show']);

Route::get('/podcast', [PodcastController::class, 'index']);


//
Route::get('/galeri', [GaleriController::class, 'index']);
Route::get('/galeri/{id}', [GaleriController::class, 'show']);

Route::get('/sotm', [SOTMController::class, 'index']);

Route::post('/kontak', [KontakController::class, 'store']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/dashboard', [AuthController::class, 'index']);



