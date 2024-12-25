<?php

namespace App\Filament\Resources\StrukturResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('universitas')
                    ->options([
                        "Universitas Jenderal Soedirman" => "UNSOED",
                        "Universitas Muhammadiyah Purwokerto" => "UMP",
                        "Universitas Islam Negeri Prof. K.H. Saifuddin Zuhri" => "UIN",
                    ])
                    ->preload()
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('bidang')
                    ->options([
                        "bph" => "BPH",
                        "ekonomi" => "EKONOMI",
                        "pendidikan" => "PENDIDIKAN",
                        "lingkungan" => "LINGKUNGAN",
                        "kesehatan" => "KESEHATAN",
                        "medeks" => "MEDEKS",
                    ]),
                Forms\Components\TextInput::make('departemen')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama'),
                Tables\Columns\TextColumn::make('universitas'),
                Tables\Columns\TextColumn::make('bidang'),
                Tables\Columns\TextColumn::make('departemen'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
