<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StrukturResource\Pages;
use App\Filament\Resources\StrukturResource\RelationManagers;
use App\Filament\Resources\StrukturResource\RelationManagers\MembersRelationManager;
use App\Models\Struktur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StrukturResource extends Resource
{
    protected static ?string $model = Struktur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Partial Halaman';

    protected static ?string $navigationLabel = 'Struktur';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_lengkap')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        "president" => "Presiden",
                        "secretary" => "Sekretaris",
                        "treasure" => "Bendahara",
                        "deputy" => "Deputi",
                    ]),
                Forms\Components\TextInput::make('jabatan')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Tulis jabatannya, Misal : Presiden UMP'),
                Forms\Components\TextInput::make('periode')
                    ->maxLength(255),
                Forms\Components\Select::make('universitas')
                    ->options([
                        "Universitas Jenderal Soedirman" => "UNSOED",
                        "Universitas Muhammadiyah Purwokerto" => "UMP",
                        "Universitas Islam Negeri Prof. K.H. Saifuddin Zuhri" => "UIN",
                    ])
                    ->preload()
                    ->required(),
                Forms\Components\FileUpload::make('foto')
                    ->disk('public')
                    ->required()
                    ->image()
                    ->directory('struktur'),
                Forms\Components\Textarea::make('quote')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('periode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('universitas')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('foto'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            MembersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStrukturs::route('/'),
            'edit' => Pages\EditStruktur::route('/{record}/edit'),
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
