<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class QuizController extends Controller
{

    /**
     * Mengatur header CORS untuk mengizinkan akses dari domain yang diizinkan.
     *
     * Method ini memeriksa apakah origin pada request masuk dalam daftar domain
     * yang diperbolehkan pada file .env melalui variabel VITE_CORS_DOMAINS.
     * Jika tidak diizinkan → akses akan ditolak dengan HTTP 403.
     *
     * @param array|null $allowed Daftar domain yang diperbolehkan (opsional).
     * @param string|null $origin Origin yang masuk via request header (opsional).
     * @return array Header yang diperbolehkan untuk CORS.
     */
    private function getCorsHeaders($allowed = null, $origin = null): array
    {
        // Ambil daftar domain yang diperbolehkan (dipisah dengan koma di .env)
        $allowed = $allowed ?? explode(',', $_ENV['VITE_CORS_DOMAINS']);

        // Ambil Origin dari request (lebih aman dibanding $_SERVER['HTTP_ORIGIN'])
        $origin = $origin ?? request()->header('Origin', '');

        // Jika origin tidak ada di daftar allowed → tolak akses
        if (!in_array($origin, $allowed)) {
            $origin = 'null';
        }

        // Jika lolos validasi → kembalikan header
        return [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];
    }

    private function json($data, $status = 200)
    {
        return response()->json($data, $status, $this->getCorsHeaders());
    }


    /**
     * GET: List Quiz
     */
    public function index()
    {

        $now = now();

        $quizzes = Quiz::select(
                'id', 'uuid', 'title', 'description',
                'start_at', 'end_at', 'duration_minutes'
            )
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($quiz) => [
                'id' => $quiz->id,
                'uuid' => $quiz->uuid,
                'jumlah_soal' => $quiz->quizQuizQuestions()->count(),
                'title' => $quiz->title,
                'description' => $quiz->description ?? '',
                'start_at' => optional($quiz->start_at)->toDateTimeString(),
                'end_at' => optional($quiz->end_at)->toDateTimeString(),
                'duration_minutes' => $quiz->duration_minutes,
            ]);

        return $this->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    /**
     * GET: Mulai Quiz
     */
    public function start($uuid)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $quiz = Quiz::where('uuid', $uuid)
            ->with(['quizQuizQuestions.quizQuestion.answers'])
            ->firstOrFail();

        $attempt = QuizAttempt::firstOrCreate(
            [
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'finished_at' => null,
            ],
            ['started_at' => now()]
        );

        $answers = $attempt->answers()
            ->pluck('quiz_answer_id', 'quiz_question_id');

        return $this->json([
            'success' => true,
            'data' => [
                'quiz' => [
                    'uuid' => $quiz->uuid,
                    'title' => $quiz->title,
                    'duration_minutes' => $quiz->duration_minutes,
                    'questions' => $quiz->quizQuizQuestions->map(function ($q) use ($answers) {
                        return [
                            'id' => $q->quizQuestion->id,
                            'question_text' => $q->quizQuestion->question_text,
                            'answers' => $q->quizQuestion->answers->map(fn ($a) => [
                                'id' => $a->id,
                                'answer_text' => $a->answer_text,
                                'selected' => $answers[$q->quizQuestion->id] ?? null,
                            ]),
                        ];
                    }),
                ],
                'attempt' => [
                    'id' => $attempt->id,
                    'started_at' => $attempt->started_at,
                ]
            ]
        ]);
    }

    /**
     * POST: Submit Quiz
     */
    public function submit(Request $request, $uuid)
    {
        $user = Auth::user();

        $quiz = Quiz::where('uuid', $uuid)->firstOrFail();

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->whereNull('finished_at')
            ->firstOrFail();

        $answers = $request->input('answers', []);

        $totalCorrect = 0;
        $answerDetails = [];

        foreach ($answers as $questionId => $answerId) {
            $answer = QuizAnswer::find($answerId);
            $isCorrect = $answer?->is_correct ?? false;

            if ($isCorrect) $totalCorrect++;

            QuizAttemptAnswer::updateOrCreate(
                [
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $questionId,
                ],
                [
                    'quiz_answer_id' => $answerId,
                    'is_correct' => $isCorrect,
                ]
            );

            $answerDetails[] = [
                'question_id' => $questionId,
                'answer_id' => $answerId,
                'is_correct' => $isCorrect,
            ];
        }

        $totalQuestions = $quiz->quizQuizQuestions()->count();
        $score = round(($totalCorrect / $totalQuestions) * 100);

        // Recommendation
        $recommendation = $this->generateAIRecommendation(
            $quiz->title,
            $score,
            $answerDetails
        );

        $attempt->update([
            'finished_at' => now(),
            'score' => $score,
            'recomendation' => $recommendation,
        ]);

        return $this->json([
            'success' => true,
            'data' => [
                'score' => $score,
                'recommendation' => $recommendation,
                'attempt_uuid' => $attempt->uuid
            ]
        ]);
    }

    /**
     * GET: Lihat Attempt
     */
    public function show($uuid, $uuidAttempt)
    {
        $user = Auth::user();

        $quiz = Quiz::where('uuid', $uuid)
            ->with(['quizQuizQuestions.quizQuestion.answers'])
            ->firstOrFail();

        $attempt = QuizAttempt::where('uuid', $uuidAttempt)
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->with(['answers'])
            ->firstOrFail();

        $userAnswers = $attempt->answers
            ->pluck('quiz_answer_id', 'quiz_question_id');

        $questions = $quiz->quizQuizQuestions->map(function ($q) use ($userAnswers) {
            $question = $q->quizQuestion;

            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'answers' => $question->answers->map(function ($a) use ($userAnswers) {
                    return [
                        'id' => $a->id,
                        'answer_text' => $a->answer_text,
                        'is_correct' => $a->is_correct,
                        'selected_by_user' => ($userAnswers[$a->quiz_question_id] ?? null) == $a->id,
                        'answer_explanation' => $a->answer_explanation,
                    ];
                }),
                'explanation_correct' => $question->explanation_correct,
            ];
        });

        return $this->json([
            'success' => true,
            'data' => [
                'quiz' => [
                    'uuid' => $quiz->uuid,
                    'title' => $quiz->title,
                    'duration_minutes' => $quiz->duration_minutes,
                ],
                'attempt' => $attempt,
                'questions' => $questions
            ]
        ]);
    }

    /**
     * Simple Recommendation
     */


    protected function generateAIRecommendation(string $quizTitle, int $score, array $answerDetails): string
    {
        $summary = collect($answerDetails)->map(function ($a) {
            return "Soal {$a['question_id']}: " . ($a['is_correct'] ? "Benar" : "Salah");
        })->join("\n");

        $prompt = <<<EOT
        Kamu adalah mentor belajar.

        User mengerjakan quiz: "{$quizTitle}"
        Skor: {$score}

        Detail jawaban:
        {$summary}

        Tugas kamu:
        - Berikan evaluasi singkat
        - Sebutkan kelemahan user
        - Berikan saran belajar yang konkret
        - Gunakan bahasa Indonesia santai tapi profesional
        - Maksimal 3-4 kalimat
        EOT;

        try {
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Kamu adalah mentor edukasi yang jelas dan to the point.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 150,
                ]);

            if (!$response->successful()) {
                return $this->generateRecommendationFallback($score);
            }

            return $response['choices'][0]['message']['content']
                ?? $this->generateRecommendationFallback($score);

        } catch (\Exception $e) {
            return $this->generateRecommendationFallback($score);
        }
    }

    protected function generateRecommendationFallback($score)
    {
        if ($score === 100) return "Kerja bagus! Semua jawaban benar.";
        if ($score >= 70) return "Hampir sempurna, sedikit lagi!";
        if ($score >= 50) return "Perlu latihan lagi.";
        return "Pelajari ulang materi dan coba lagi.";
    }
}
