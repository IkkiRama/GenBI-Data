<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// use Intervention\Image\Facades\Image;
// use Intervention\Image\Laravel\Facades\Image;

Route::get('/test-intervention', function () {
    $image = Image::canvas(800, 600, '#ff0000'); // Gambar merah ukuran 800x600
    $image->text('Hello, World!', 400, 300, function ($font) {
        $font->size(50);
        $font->color('#ffffff');
        $font->align('center');
        $font->valign('middle');
    });
    return $image->response('webp'); // Tampilkan dalam format WEBP
});
