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
    /**
     * Get leaderboard ranking
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        $selectedBidang = trim((string) $request->query('bidang'));
        $selectedKelas = trim((string) $request->query('kelas'));
        $selectedLevel = trim((string) $request->query('level'));

        if (strtolower($selectedBidang) === 'all') $selectedBidang = '';
        if (strtolower($selectedKelas) === 'all') $selectedKelas = '';
        if (strtolower($selectedLevel) === 'all') $selectedLevel = '';

        $rows = PracticeSession::where('status', 'completed')
            ->select('user_id', DB::raw('COUNT(*) as total_attempts'), DB::raw('AVG(total_score) as avg_score'), DB::raw('MAX(total_score) as best_score'))
            ->groupBy('user_id')
            ->orderByDesc('avg_score')
            ->orderByDesc('best_score')
            ->limit(1000)
            ->get();

        $usersById = User::whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');

        $users = $rows
            ->map(function ($item) use ($usersById) {
                $u = $usersById->get($item->user_id);
                if (!$u) return null;

                $bg = \App\Http\Controllers\GamificationController::bidangGroup($u->bidang);

                return [
                    'user' => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'school_name' => $u->school_name,
                        'profile_photo_url' => $u->profile_photo_url,
                        'student_class' => $u->student_class,
                        'kelas' => $u->student_class,
                        'level' => $u->level,
                        'bidang' => $u->bidang,
                        'bidang_group' => $bg,
                    ],
                    'bidang_group' => $bg,
                    'kelas' => $u->student_class,
                    'level' => $u->level,
                    'total_attempts' => (int) $item->total_attempts,
                    'avg_score' => round((float) $item->avg_score, 1),
                    'best_score' => round((float) $item->best_score, 1),
                ];
            })
            ->filter()
            ->when($selectedBidang !== '', fn ($col) => $col->filter(
                fn ($e) => in_array($selectedBidang, ['Matematika', 'IPA', 'Lainnya'], true)
                    ? $e['bidang_group'] === $selectedBidang
                    : strcasecmp((string) $e['user']['bidang'], $selectedBidang) === 0
            ))
            ->when($selectedKelas !== '', fn ($col) => $col->filter(
                fn ($e) => (string) $e['kelas'] === $selectedKelas
            ))
            ->when($selectedLevel !== '', fn ($col) => $col->filter(
                fn ($e) => (string) $e['level'] === $selectedLevel
            ))
            ->sortBy([['avg_score', 'desc'], ['best_score', 'desc']])
            ->values()
            ->map(fn ($entry, $idx) => array_merge(['rank' => $idx + 1], $entry))
            ->values();

        $currentUserRank = null;
        if ($userId) {
            $myEntry = $users->firstWhere('user.id', $userId);
            if ($myEntry) {
                $currentUserRank = [
                    'rank' => $myEntry['rank'],
                    'total_attempts' => $myEntry['total_attempts'],
                    'avg_score' => $myEntry['avg_score'],
                    'best_score' => $myEntry['best_score'],
                ];
            }
        }

        return $this->sendResponse([
            'leaderboard' => $users->take(50)->values(),
            'current_user_rank' => $currentUserRank,
        ], 'Data leaderboard berhasil dimuat.');
    }

    /**
     * Get learning analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
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
