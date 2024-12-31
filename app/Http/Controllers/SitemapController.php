<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Event;
use App\Models\Galeri;
use App\Models\KategoriArtikel;
use App\Models\Struktur;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $artikel = Artikel::latest()->get();
        $event = Event::latest()->get();
        $galeri = Galeri::latest()->get();
        $kategoriArtikel = KategoriArtikel::latest()->get();
        $struktur = Struktur::latest()->whereNotIn("jabatan", [
            "Presiden Komisariat UNSOED",
            "Presiden Komisariat UMP",
            "Presiden Komisariat UIN",
        ])->get();
        $dataPerPeriodeStruktur = Struktur::select('periode')->distinct()->get();

        return response()->view("sitemap",
            compact(
                ["artikel", "event", "galeri", "kategoriArtikel", "struktur", "dataPerPeriodeStruktur"]
            )
        )->header("Content-Type", "text/xml");
    }
}
