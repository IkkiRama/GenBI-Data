<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Kuis';
    protected static ?string $pluralLabel = 'Kuis';
    protected static ?string $modelLabel = 'Kuis';
    protected static ?string $navigationGroup = '🧠 Kuis Nusantara';
    protected static ?string $pluralModelLabel = 'Daftar Kuis';
    protected static ?string $slug = 'kuis';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Informasi Dasar')
                    ->description('Isi detail dasar tentang kuis.')
                    ->schema([
                        TextInput::make('uuid')
                        ->label('UUID')
                        ->disabled()
                        ->dehydrated(false)
                        ->visibleOn('edit')
                        ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Judul Kuis')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan Waktu')
                    ->description('Atur durasi dan jadwal aktif kuis.')
                    ->schema([
                        DateTimePicker::make('start_at')
                            ->label('Mulai Tanggal')
                            ->seconds(false),

                        DateTimePicker::make('end_at')
                            ->label('Selesai Tanggal')
                            ->seconds(false),

                        TextInput::make('duration_minutes')
                            ->label('Durasi (menit)')
                            ->numeric()
                            ->default(10)
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Daftar Pertanyaan')
                    ->description('Pilih pertanyaan untuk dimasukkan ke kuis ini.')
                    ->schema([
                        Repeater::make('quizQuizQuestions')
                            ->relationship('quizQuizQuestions') // langsung ke pivot model
                            ->label('List Pertanyaan')
                            ->schema([
                                Select::make('quiz_question_id')
                                    ->label('Pilih Pertanyaan')
                                    ->options(\App\Models\QuizQuestion::pluck('question_text', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Textarea::make('question_text')->label('Pertanyaan')->required(),
                                        Textarea::make('explanation_correct')->label('Penjelasan Jika Benar'),
                                    ])
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->createOptionAction(function ($action) {
                                        $action
                                            ->modalHeading('Tambah Pertanyaan Baru')
                                            ->modalButton('Simpan Pertanyaan');
                                        return $action;
                                    }),
                            ])
                            ->default([])
                            ->columns(1)
                            ->collapsible()
                            ->grid(1)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('duration_minutes')->label('Durasi (menit)'),
                TextColumn::make('start_at')->label('Mulai')->dateTime('d M Y H:i'),
                TextColumn::make('end_at')->label('Selesai')->dateTime('d M Y H:i'),
                TextColumn::make('quiz_questions_count')
                    ->label('Jumlah Pertanyaan')
                    ->default(0),
                TextColumn::make('created_at')->label('Dibuat')->since(),
            ])
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
            RelationManagers\QuizQuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
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
