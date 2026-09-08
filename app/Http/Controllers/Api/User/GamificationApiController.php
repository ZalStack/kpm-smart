<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PracticeSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class GamificationApiController extends BaseApiController
{
    /**
     * Get leaderboard ranking
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user');

        $users = PracticeSession::where('status', 'completed')
            ->select('user_id', DB::raw('COUNT(*) as total_attempts'), DB::raw('AVG(total_score) as avg_score'), DB::raw('MAX(total_score) as best_score'))
            ->groupBy('user_id')
            ->orderByDesc('avg_score')
            ->orderByDesc('best_score')
            ->limit(20)
            ->get()
            ->map(function ($item, $index) {
                $u = User::find($item->user_id);
                return [
                    'rank' => $index + 1,
                    'user' => $u ? [
                        'id' => $u->id,
                        'name' => $u->name,
                        'school_name' => $u->school_name,
                        'profile_photo_url' => $u->profile_photo_url,
                    ] : null,
                    'total_attempts' => (int) $item->total_attempts,
                    'avg_score' => round((float) $item->avg_score, 1),
                    'best_score' => round((float) $item->best_score, 1),
                ];
            })
            ->filter(fn($item) => $item['user'] !== null)
            ->values();

        $currentUserRank = null;
        $currentUserData = PracticeSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->select(DB::raw('COUNT(*) as total_attempts'), DB::raw('AVG(total_score) as avg_score'), DB::raw('MAX(total_score) as best_score'))
            ->first();

        if ($currentUserData && $currentUserData->total_attempts > 0) {
            $higherCount = PracticeSession::where('status', 'completed')
                ->groupBy('user_id')
                ->havingRaw('AVG(total_score) > ?', [$currentUserData->avg_score])
                ->count();

            $currentUserRank = [
                'rank' => $higherCount + 1,
                'total_attempts' => (int) $currentUserData->total_attempts,
                'avg_score' => round((float) $currentUserData->avg_score, 1),
                'best_score' => round((float) $currentUserData->best_score, 1),
            ];
        }

        return $this->sendResponse([
            'leaderboard' => $users,
            'current_user_rank' => $currentUserRank,
        ], 'Data leaderboard berhasil dimuat.');
    }

    /**
     * Get learning analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user');
        $sessions = PracticeSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('package:id,title')
            ->orderBy('finished_at', 'asc')
            ->get();

        $scoreOverTime = $sessions->map(fn($s) => [
            'date' => $s->finished_at ? Carbon::parse($s->finished_at)->format('d M') : '',
            'score' => round((float) $s->total_score, 1),
            'package' => $s->package?->title ?? 'Unknown',
        ])->values();

        $accuracyOverTime = $sessions->map(function ($s) {
            $total = $s->total_question > 0 ? $s->total_question : 1;
            return [
                'date' => $s->finished_at ? Carbon::parse($s->finished_at)->format('d M') : '',
                'accuracy' => round(($s->correct_answer / $total) * 100, 1),
            ];
        })->values();

        $packageStats = $sessions->groupBy(fn($s) => $s->package?->title ?? 'Unknown')
            ->map(function ($group, $name) {
                $total = $group->sum('total_question');
                $correct = $group->sum('correct_answer');
                return [
                    'name' => $name,
                    'attempts' => $group->count(),
                    'avg_score' => round((float) $group->avg('total_score'), 1),
                    'accuracy' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
                ];
            })
            ->values();

        $totalAttempts = $sessions->count();
        $avgScore = $sessions->avg('total_score') ?? 0;
        $bestScore = $sessions->max('total_score') ?? 0;
        $totalQuestions = (int) $sessions->sum('total_question');
        $totalCorrect = (int) $sessions->sum('correct_answer');
        $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;

        return $this->sendResponse([
            'summary' => [
                'total_attempts' => $totalAttempts,
                'avg_score' => round((float) $avgScore, 1),
                'best_score' => round((float) $bestScore, 1),
                'accuracy' => $accuracy,
                'total_questions' => $totalQuestions,
                'total_correct' => $totalCorrect,
            ],
            'score_over_time' => $scoreOverTime,
            'accuracy_over_time' => $accuracyOverTime,
            'package_stats' => $packageStats,
        ], 'Data analitik belajar berhasil dimuat.');
    }
}
