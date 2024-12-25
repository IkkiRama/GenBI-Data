<?php

namespace App\Filament\Resources\SOTMResource\Pages;

use App\Filament\Resources\SOTMResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSOTMS extends ListRecords
{
    protected static string $resource = SOTMResource::class;

    protected static ?string $title = "SOTM";

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
