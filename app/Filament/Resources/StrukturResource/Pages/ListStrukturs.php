<?php

namespace App\Filament\Resources\StrukturResource\Pages;

use Filament\Actions;
use Illuminate\View\View;
use Filament\Actions\CreateAction;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\StrukturResource;
use App\Imports\ImportMember;
use App\Imports\ImportStruktur;

class ListStrukturs extends ListRecords
{
    protected static string $resource = StrukturResource::class;
    public $file = '';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader() : ?View {
        $data = CreateAction::make();
        return view("filament.custom.upload-file", compact(["data"]));
    }

    function save() {
        if ($this->file !== '') {
            Excel::import(new ImportStruktur, $this->file);
        }
    }

    function uploadMember() {
        if ($this->file !== '') {
            Excel::import(new ImportMember, $this->file);
        }
    }
}
