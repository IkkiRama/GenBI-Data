<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');


Route::get('/auth-google-redirect', [AuthController::class, 'redirectToGoogle'])->name("login");
Route::get('/auth-google-callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/logout', [AuthController::class, 'logout']);
