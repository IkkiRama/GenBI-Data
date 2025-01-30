<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SOTMResource\Pages;
use App\Filament\Resources\SOTMResource\RelationManagers;
use App\Models\SOTM;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SOTMResource extends Resource
{
    protected static ?string $model = SOTM::class;

    protected static ?string $navigationIcon = 'heroicon-c-academic-cap';

    protected static ?string $navigationGroup = 'Partial Halaman';

    protected static ?string $navigationLabel = 'SOTM';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jenis')
                    ->options([
                        "deputi" => "Deputi",
                        "staff" => "Staff",
                    ])
                    ->preload()
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->disk('public')
                    ->required()
                    ->image()
                    ->directory('sotm'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->label("Jenis SOTM")
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSOTMS::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
