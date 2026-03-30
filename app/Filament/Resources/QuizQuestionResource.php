<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizQuestionResource\Pages;
use App\Filament\Resources\QuizQuestionResource\RelationManagers;
use App\Models\QuizQuestion;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizQuestionResource extends Resource
{
    protected static ?string $model = QuizQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Pertanyaan Kuis';
    protected static ?string $pluralLabel = 'Pertanyaan Kuis';
    protected static ?string $modelLabel = 'Pertanyaan Kuis';
    protected static ?string $navigationGroup = '🧠 Kuis Nusantara';
    protected static ?string $pluralModelLabel = 'Daftar Pertanyaan Kuis';
    protected static ?string $slug = 'pertanyaan-kuis';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('question_text')
                    ->label('Teks Pertanyaan')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('explanation_correct')
                    ->label('Penjelasan Jika Benar')
                    ->rows(2)
                    ->columnSpanFull(),

                Repeater::make('answers')
                    ->relationship('answers')
                    ->label('Jawaban')
                    ->schema([
                        TextInput::make('answer_text')
                            ->label('Jawaban')
                            ->required(),

                        Textarea::make('answer_explanation')
                            ->label('Penjelasan Jawaban')
                            ->rows(2),

                        Toggle::make('is_correct')
                            ->label('Jawaban Benar?')
                            ->inline(false),
                    ])
                    ->default([])
                    ->columns(1)
                    ->collapsible()
                    ->columnSpanFull()
                    ->grid(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question_text')
                    ->label('Pertanyaan')
                    ->limit(50),
                TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label('Jumlah Jawaban'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color("info"),
                Tables\Actions\EditAction::make()
                    ->color("warning"),
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
            'index' => Pages\ListQuizQuestions::route('/'),
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
