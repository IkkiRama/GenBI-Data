<?php

namespace App\Filament\Resources\KategoriArtikelResource\Pages;

use App\Filament\Resources\KategoriArtikelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKategoriArtikel extends EditRecord
{
    protected static string $resource = KategoriArtikelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
