<?php

namespace App\Filament\Resources\SOTMResource\Pages;

use App\Filament\Resources\SOTMResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSOTM extends EditRecord
{
    protected static string $resource = SOTMResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
