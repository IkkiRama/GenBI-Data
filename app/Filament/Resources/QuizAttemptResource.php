<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizAttemptResource\Pages;
use App\Filament\Resources\QuizAttemptResource\RelationManagers;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizQuizQuestion;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizAttemptResource extends Resource
{
    protected static ?string $model = QuizAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';
    protected static ?string $navigationLabel = 'Pengerjaan Kuis';
    protected static ?string $pluralLabel = 'Pengerjaan Kuis';
    protected static ?string $modelLabel = 'Pengerjaan Kuis';
    protected static ?string $navigationGroup = '🧠 Kuis Nusantara';
    protected static ?string $pluralModelLabel = 'Daftar Pengerjaan Kuis';
    protected static ?string $slug = 'pengerjaan-kuis';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->options(User::all()->pluck('name', 'id')->toArray()) // key=id, value=name
                    ->required()
                    ->searchable(),

                Select::make('quiz_id')
                    ->label('Kuis')
                    ->options(Quiz::pluck('title', 'id'))
                    ->required()
                    ->reactive() // penting: biar perubahan terpropagasi
                    ->searchable()
                    ->afterStateUpdated(fn($state, $set) => $set('answers', [])), // reset repeater jika ganti kuis
                DateTimePicker::make('started_at')
                    ->label('Mulai')
                    ->required()
                    ->required(),
                DateTimePicker::make('finished_at')
                    ->label('Selesai')
                    ->required(),
                TextInput::make('score')
                    ->label('Skor')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('recomendation')
                    ->label('Rekomendasi')
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('answers')
                    ->relationship('answers')
                    ->label('Jawaban User')
                    ->schema([
                        Select::make('quiz_question_id')
                            ->label('Pertanyaan')
                            ->options(function (callable $get) {
                                $quizId = $get('../../quiz_id');
                                if (! $quizId) return QuizQuestion::pluck('question_text', 'id')->toArray();

                                $questionIds = QuizQuizQuestion::where('quiz_id', $quizId)
                                    ->pluck('quiz_question_id')
                                    ->toArray();

                                return QuizQuestion::whereIn('id', $questionIds)
                                    ->pluck('question_text', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(function ($state, $set) {
                                // ketika ganti pertanyaan, reset jawaban & is_correct
                                $set('quiz_answer_id', null);
                                $set('is_correct', false);
                            })
                            ->required(),

                        Select::make('quiz_answer_id')
                            ->label('Jawaban Terpilih')
                            ->options(function (callable $get) {
                                $questionId = $get('quiz_question_id');
                                if (! $questionId) return [];
                                return QuizAnswer::where('quiz_question_id', $questionId)
                                    ->pluck('answer_text', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(function ($state, $set) {
                                // Ambil nilai is_correct dari quiz_answers dan set ke field hidden
                                $isCorrect = QuizAnswer::where('id', $state)->value('is_correct');
                                $set('is_correct', (bool) $isCorrect);
                            })
                            ->required(),

                        // Hidden field: akan di-dehydrate dan ikut tersimpan ke kolom is_correct
                        Hidden::make('is_correct')
                            ->dehydrated()   // pastikan ikut disimpan
                            ->default(false),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->columnSpanFull()
                    ->default([])
                    ->afterStateUpdated(function ($state, callable $set, $get, $record) {
                        // Setelah repeater berubah, hitung ulang skor dan rekomendasi di sini
                        if (!$record) return;

                        // Hitung skor otomatis (jumlah benar)
                        $answers = collect($get('answers') ?? []);
                        $total = max(count($answers), 1); // biar gak divide by zero
                        $correct = $answers->where('is_correct', true)->count();
                        $score = round(($correct / $total) * 100, 2); // jadi persentase, misal 66.67
                        $record->score = $score;

                        // Buat rekomendasi dari jawaban salah
                        $wrongAnswers = $answers->where('is_correct', false);
                        $recomendation = $wrongAnswers->map(function ($a) {
                            $question = \App\Models\QuizQuestion::find($a['quiz_question_id']);
                            $correctAnswer = \App\Models\QuizAnswer::where('quiz_question_id', $a['quiz_question_id'])
                                ->where('is_correct', true)
                                ->value('answer_text');
                            return "- Pertanyaan: {$question?->question_text}\n  Jawaban benar: {$correctAnswer}\n  Penjelasan: {$question?->explanation_correct}";
                        })->implode("\n\n");

                        $record->recomendation = $recomendation;
                        $record->save();
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('quiz.title')->label('Kuis'),
                TextColumn::make('score')->label('Skor'),
                TextColumn::make('recomendation')->label('Rekomendasi')->limit(50),
                TextColumn::make('started_at')->label('Mulai')->dateTime('d M Y H:i'),
                TextColumn::make('finished_at')->label('Selesai')->dateTime('d M Y H:i'),
            ])
            ->defaultSort('started_at', 'desc')
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

    // public static function afterSave(Form $form, QuizAttempt $record): void
    // {
    //     // pastikan data relation sudah up-to-date
    //     $record->loadMissing(['answers.answer', 'answers.question']);

    //     // Hitung skor otomatis (jawaban benar)
    //     $score = $record->answers()->where('is_correct', true)->count();
    //     $record->score = $score;

    //     // Buat rekomendasi otomatis: gabungkan explanation jawaban salah
    //     $wrongAnswers = $record->answers()
    //         ->where('is_correct', false)
    //         ->with(['question', 'answer'])
    //         ->get();

    //     $recomendation = $wrongAnswers->map(function($a){
    //         $qText = $a->question?->question_text ?? '[pertanyaan tidak tersedia]';
    //         $correctText = $a->answer?->answer_text ?? '[jawaban tidak tersedia]';
    //         $explain = $a->question?->explanation_correct ?? '';
    //         return "- Pertanyaan: {$qText}\n  Jawaban benar: {$correctText}\n  Penjelasan: {$explain}";
    //     })->implode("\n\n");

    //     $record->recomendation = $recomendation;
    //     $record->save();

    //     Notification::make()
    //         ->title('Pengerjaan kuis berhasil disimpan')
    //         ->success()
    //         ->send();
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizAttempts::route('/'),
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
