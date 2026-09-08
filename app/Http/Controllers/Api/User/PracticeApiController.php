<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PracticeApiController extends BaseApiController
{
    /**
     * Get user's practice sessions history
     */
    public function history(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user', true);
        $perPage = (int) $request->input('per_page', 15);

        $query = PracticeSession::where('status', 'completed')
            ->with(['package:id,title,bidang,level', 'user:id,name']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse($sessions, 'Riwayat pengerjaan tugas berhasil dimuat.');
    }

    /**
     * Get user's practice statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user');

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
     * Get practice session detail / review
     */
    public function show(PracticeSession $session, Request $request): JsonResponse
    {
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
