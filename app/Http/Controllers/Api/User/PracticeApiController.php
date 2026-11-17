<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use App\Support\AnswerNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticeApiController extends BaseApiController
{
    /**
     * Get user's practice sessions history
     */
    public function history(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if (! $userId) {
            return $this->sendError('Unauthenticated. Silakan login terlebih dahulu.', [], 401);
        }

        $perPage = (int) $request->input('per_page', 15);

        $query = PracticeSession::where('status', 'completed')
            ->with(['package:id,title,bidang,level', 'user:id,name'])
            ->where('user_id', $userId);

        $sessions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse($sessions, 'Riwayat pengerjaan tugas berhasil dimuat.');
    }

    /**
     * Get user's practice statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        $sessions = $userId ? PracticeSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('package:id,title')
            ->get() : collect();

        $totalAttempts = $sessions->count();
        $bestScore = (float) ($sessions->max('total_score') ?? 0);
        $averageScore = round((float) ($sessions->avg('total_score') ?? 0), 1);
        $totalQuestions = (int) $sessions->sum('total_question');
        $correctAnswers = (int) $sessions->sum('correct_answer');
        $accuracy = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;

        $sessionsByPackage = $sessions->groupBy('package_id')->map(function ($group) {
            $first = $group->first();
            return [
                'package_id' => $first->package_id,
                'package_title' => $first->package ? $first->package->title : 'Paket Dihapus',
                'attempts' => $group->count(),
                'avg_score' => round((float) $group->avg('total_score'), 1),
                'best_score' => round((float) $group->max('total_score'), 1),
            ];
        })->values();

        return $this->sendResponse([
            'overview' => [
                'total_attempts' => $totalAttempts,
                'best_score' => $bestScore,
                'average_score' => $averageScore,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'accuracy' => $accuracy,
            ],
            'by_package' => $sessionsByPackage,
        ], 'Statistik pengerjaan tugas berhasil dimuat.');
    }

    /**
     * Start or continue a practice session
     */
    public function start(Request $request, Package $package): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        if (! $userId) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        if (! $package->is_active) {
            return $this->sendError('Paket ini belum aktif!', [], 400);
        }

        $user = Auth::user() ?? User::find($userId);
        if ($user && $user->role === 'user') {
            $access = $package->matchesStudent($user);
            if (! $access['allowed']) {
                return $this->sendError($access['reason'], [], 403);
            }
        }

        if ($package->schedule_status === 'expired') {
            return $this->sendError('Jadwal pengerjaan paket ini telah berakhir!', [], 400);
        }

        if ($package->schedule_status === 'upcoming') {
            return $this->sendError('Paket ini belum bisa dikerjakan. Silakan tunggu jadwal mulai.', [], 400);
        }

        // Cek apakah sudah pernah menyelesaikan (1-attempt restriction)
        $existingSession = PracticeSession::where('user_id', $userId)
            ->where('package_id', $package->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if ($existingSession) {
            return $this->sendError('Tugas PR pekan ini hanya dapat dikerjakan 1 kali dan kamu sudah menyelesaikannya.', [
                'session_id' => $existingSession->id,
            ], 400);
        }

        $cardId = $request->input('card_id');

        // Sesi in_progress sebelumnya dilanjutkan
        $inProgress = PracticeSession::where('user_id', $userId)
            ->where('package_id', $package->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        $session = $inProgress;
        if (! $session) {
            $questions = $package->questionsForAttempt($cardId);
            $session = PracticeSession::create([
                'user_id' => $userId,
                'package_id' => $package->id,
                'card_id' => $cardId,
                'status' => 'in_progress',
                'started_at' => now(),
                'total_question' => count($questions),
                'correct_answer' => 0,
                'wrong_answer' => 0,
                'unanswered' => count($questions),
                'total_score' => 0,
            ]);
        }

        $questionsForAttempt = $package->questionsForAttempt($session->card_id ?: $cardId);

        // Sanitize questions: Sembunyikan kunci jawaban dan pembahasan dari siswa
        $sanitizedQuestions = array_map(function ($q) {
            return [
                'id' => $q['id'] ?? null,
                'card_id' => $q['card_id'] ?? null,
                'question' => $q['question'] ?? '',
                'options' => $q['options'] ?? [],
                'type' => $q['type'] ?? 'pilihan_ganda',
                'image' => $q['image'] ?? null,
            ];
        }, $questionsForAttempt);

        return $this->sendResponse([
            'session' => [
                'id' => $session->id,
                'package_id' => $session->package_id,
                'card_id' => $session->card_id,
                'status' => $session->status,
                'started_at' => $session->started_at,
                'total_question' => count($sanitizedQuestions),
            ],
            'package' => [
                'id' => $package->id,
                'title' => $package->title,
                'description' => $package->description,
                'bidang' => $package->bidang,
                'level' => $package->level,
            ],
            'questions' => $sanitizedQuestions,
        ], 'Sesi latihan berhasil dimulai.', 201);
    }

    /**
     * Submit answers for practice session
     */
    public function submit(Request $request, PracticeSession $session): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if (! $userId || (int) $session->user_id !== (int) $userId) {
            return $this->sendError('Akses ditolak!', [], 403);
        }

        if ($session->status === 'completed') {
            return $this->sendError('Tugas ini sudah pernah kamu kumpulkan. Nilai akhir tidak dapat diubah.', [], 400);
        }

        $package = $session->package;
        if (! $package) {
            return $this->sendError('Paket soal tidak ditemukan.', [], 404);
        }

        $allQuestions = collect($package->questions ?? []);
        if ($session->card_id && $allQuestions->contains('card_id', $session->card_id)) {
            $questions = $allQuestions->where('card_id', $session->card_id)->values()->all();
        } else {
            $questions = $allQuestions->values()->all();
        }

        $answers = $request->input('answers', []);

        $correct = 0;
        $wrong = 0;
        $unanswered = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            // Bisa array asosiatif indeks atau array objek [{question_index/id: ..., answer: ...}]
            $userAnswer = null;
            if (isset($answers[$index])) {
                $userAnswer = is_array($answers[$index]) && isset($answers[$index]['answer'])
                    ? $answers[$index]['answer']
                    : $answers[$index];
            } elseif (isset($question['id']) && isset($answers[$question['id']])) {
                $userAnswer = is_array($answers[$question['id']]) && isset($answers[$question['id']]['answer'])
                    ? $answers[$question['id']]['answer']
                    : $answers[$question['id']];
            }

            $questionType = $question['type'] ?? 'pilihan_ganda';

            if (in_array($questionType, ['isian_singkat', 'essay'], true)) {
                $isCorrect = AnswerNormalizer::isMatch(
                    $userAnswer,
                    $question['correct_answer'] ?? '',
                    $question['accepted_answers'] ?? []
                );
            } else {
                $isCorrect = ($userAnswer !== null && $userAnswer !== '')
                    && (string) $userAnswer === (string) ($question['correct_answer'] ?? '');
            }

            if ($userAnswer === null || $userAnswer === '') {
                $unanswered++;
            } elseif ($isCorrect) {
                $correct++;
            } else {
                $wrong++;
            }

            $results[] = [
                'question' => $question['question'] ?? '',
                'options' => $question['options'] ?? [],
                'type' => $questionType,
                'correct_answer' => $question['correct_answer'] ?? null,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'image' => $question['image'] ?? null,
                'explanation' => $question['explanation'] ?? '',
            ];
        }

        $totalCount = count($questions);
        $totalScore = $totalCount > 0 ? round(($correct / $totalCount) * 100, 2) : 0;
        $durationSeconds = (int) $request->input('duration_seconds', 0);

        $session->update([
            'correct_answer' => $correct,
            'wrong_answer' => $wrong,
            'unanswered' => $unanswered,
            'total_score' => $totalScore,
            'duration_seconds' => $durationSeconds,
            'finished_at' => now(),
            'status' => 'completed',
            'answers' => $results,
        ]);

        // Kirim laporan hasil belajar PR ke Web Induk
        $currentUser = Auth::user() ?? User::find($userId);
        if ($currentUser && !empty($currentUser->email)) {
            try {
                \App\Services\ParentWeeklyReportService::report(
                    $currentUser->email,
                    (int) round($totalScore),
                    true,
                    null
                );
            } catch (\Throwable $e) {
                // Ignored
            }
        }

        $showAnswerKey = true;
        $showExplanation = false;
        $showScore = $package->canShowScore();

        return $this->sendResponse([
            'session' => [
                'id' => $session->id,
                'package_id' => $session->package_id,
                'status' => $session->status,
                'total_question' => $totalCount,
                'correct_answer' => $correct,
                'wrong_answer' => $wrong,
                'unanswered' => $unanswered,
                'total_score' => $showScore ? $totalScore : null,
                'duration_seconds' => $durationSeconds,
                'finished_at' => $session->finished_at,
            ],
            'results' => array_map(function ($item) use ($showAnswerKey) {
                $itemCopy = $item;
                unset($itemCopy['explanation']);
                if (! $showAnswerKey) {
                    unset($itemCopy['correct_answer']);
                }
                return $itemCopy;
            }, $results),
            'settings' => [
                'show_score' => $showScore,
                'show_answer_key' => $showAnswerKey,
                'show_explanation' => $showExplanation,
            ],
        ], 'Tugas PR berhasil dikumpulkan.');
    }

    /**
     * Get practice session detail / review
     */
    public function show(PracticeSession $session, Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        if ($userId && (int) $session->user_id !== (int) $userId && Auth::user()?->role !== 'admin') {
            return $this->sendError('Akses ditolak!', [], 403);
        }

        $package = $session->package;
        $showAnswerKey = $package ? $package->canShowAnswerKey() : true;
        $showExplanation = $package ? $package->canShowExplanation() : true;
        $showScore = $package ? $package->canShowScore() : true;

        $answers = collect($session->answers ?? [])->map(function ($item) use ($showAnswerKey, $showExplanation) {
            $data = [
                'question' => $item['question'] ?? '',
                'options' => $item['options'] ?? [],
                'type' => $item['type'] ?? 'pilihan_ganda',
                'user_answer' => $item['user_answer'] ?? null,
                'is_correct' => $item['is_correct'] ?? false,
                'image' => $item['image'] ?? null,
            ];

            if ($showAnswerKey) {
                $data['correct_answer'] = $item['correct_answer'] ?? null;
            }

            if ($showExplanation) {
                $data['explanation'] = $item['explanation'] ?? '';
            }

            return $data;
        });

        return $this->sendResponse([
            'session' => [
                'id' => $session->id,
                'package_id' => $session->package_id,
                'package_title' => $package ? $package->title : '',
                'card_id' => $session->card_id,
                'total_question' => $session->total_question,
                'correct_answer' => $session->correct_answer,
                'wrong_answer' => $session->wrong_answer,
                'unanswered' => $session->unanswered,
                'total_score' => $showScore ? $session->total_score : null,
                'duration_seconds' => $session->duration_seconds,
                'status' => $session->status,
                'started_at' => $session->started_at,
                'finished_at' => $session->finished_at,
            ],
            'answers' => $answers,
            'settings' => [
                'show_score' => $showScore,
                'show_answer_key' => $showAnswerKey,
                'show_explanation' => $showExplanation,
            ],
        ], 'Detail hasil latihan berhasil dimuat.');
    }
}
