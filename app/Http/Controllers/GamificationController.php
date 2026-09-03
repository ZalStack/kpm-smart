<?php

namespace App\Http\Controllers;

use App\Models\PracticeSession;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class GamificationController extends Controller
{
    public function getGamificationData($userId = null)
    {
        $userId = $userId ?? Auth::id();
        $sessions = PracticeSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('finished_at', 'desc')
            ->get();

        $totalAttempts = $sessions->count();
        $avgScore = $sessions->avg('total_score') ?? 0;
        $bestScore = $sessions->max('total_score') ?? 0;

        $xp = 0;
        foreach ($sessions as $s) {
            $xp += 10;
            if ($s->total_score >= 80) $xp += 20;
            elseif ($s->total_score >= 60) $xp += 10;
            if ($s->total_score === 100) $xp += 30;
        }

        $level = max(1, floor($xp / 100));
        $xpInLevel = $xp % 100;

        $badge = 'Pemula';
        if ($level >= 10) $badge = 'Master';
        elseif ($level >= 7) $badge = 'Ahli';
        elseif ($level >= 5) $badge = 'Mahir';
        elseif ($level >= 3) $badge = 'Menengah';

        $streak = $this->calculateStreak($sessions);

        return [
            'xp' => $xp,
            'level' => (int) $level,
            'xp_in_level' => (int) $xpInLevel,
            'badge' => $badge,
            'streak' => $streak,
            'total_attempts' => $totalAttempts,
            'avg_score' => round($avgScore, 1),
            'best_score' => round($bestScore, 1),
        ];
    }

    private function calculateStreak($sessions)
    {
        if ($sessions->isEmpty()) {
            return ['current' => 0, 'best' => 0];
        }

        $dates = $sessions->pluck('finished_at')
            ->filter()
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->sortDesc()
            ->values();

        $currentStreak = 0;
        $bestStreak = 0;
        $tempStreak = 0;
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        if ($dates->first() === $today || $dates->first() === $yesterday) {
            $currentStreak = 1;
            $tempStreak = 1;
            $prevDate = Carbon::parse($dates->first());

            for ($i = 1; $i < $dates->count(); $i++) {
                $currentDate = Carbon::parse($dates[$i]);
                if ($prevDate->diffInDays($currentDate) === 1) {
                    $tempStreak++;
                    $currentStreak = max($currentStreak, $tempStreak);
                } else {
                    $bestStreak = max($bestStreak, $tempStreak);
                    $tempStreak = 1;
                }
                $prevDate = $currentDate;
            }
            $bestStreak = max($bestStreak, $tempStreak);
        } else {
            $tempStreak = 1;
            $prevDate = Carbon::parse($dates->first());
            for ($i = 1; $i < $dates->count(); $i++) {
                $currentDate = Carbon::parse($dates[$i]);
                if ($prevDate->diffInDays($currentDate) === 1) {
                    $tempStreak++;
                } else {
                    $bestStreak = max($bestStreak, $tempStreak);
                    $tempStreak = 1;
                }
                $prevDate = $currentDate;
            }
            $bestStreak = max($bestStreak, $tempStreak);
        }

        return ['current' => $currentStreak, 'best' => $bestStreak];
    }

    public function leaderboard()
    {
        $users = PracticeSession::where('status', 'completed')
            ->select('user_id', DB::raw('COUNT(*) as total_attempts'), DB::raw('AVG(total_score) as avg_score'), DB::raw('MAX(total_score) as best_score'))
            ->groupBy('user_id')
            ->orderByDesc('avg_score')
            ->orderByDesc('best_score')
            ->limit(20)
            ->get()
            ->map(function ($item, $index) {
                $user = User::find($item->user_id);
                return [
                    'rank' => $index + 1,
                    'user' => $user ? ['id' => $user->id, 'name' => $user->name] : null,
                    'total_attempts' => $item->total_attempts,
                    'avg_score' => round($item->avg_score, 1),
                    'best_score' => round($item->best_score, 1),
                ];
            })
            ->filter(fn($item) => $item['user'] !== null)
            ->values();

        $currentUserRank = null;
        $currentUserData = PracticeSession::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->select(DB::raw('COUNT(*) as total_attempts'), DB::raw('AVG(total_score) as avg_score'), DB::raw('MAX(total_score) as best_score'))
            ->first();

        if ($currentUserData && $currentUserData->total_attempts > 0) {
            $rank = DB::table('practice_sessions')
                ->where('status', 'completed')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('AVG(total_score) > ? OR (AVG(total_score) = ? AND MAX(total_score) > ?) OR (AVG(total_score) = ? AND MAX(total_score) = ? AND user_id < ?)', [
                    $currentUserData->avg_score, $currentUserData->avg_score, $currentUserData->best_score,
                    $currentUserData->avg_score, $currentUserData->best_score, Auth::id()
                ])
                ->get()->count() + 1;

            $currentUserRank = [
                'rank' => $rank,
                'total_attempts' => $currentUserData->total_attempts,
                'avg_score' => round($currentUserData->avg_score, 1),
                'best_score' => round($currentUserData->best_score, 1),
            ];
        }

        return Inertia::render('Gamification/Leaderboard', [
            'leaderboard' => $users,
            'currentUserRank' => $currentUserRank,
        ]);
    }

    public function analytics()
    {
        $userId = Auth::id();
        $sessions = PracticeSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('package')
            ->orderBy('finished_at', 'asc')
            ->get();

        $scoreOverTime = $sessions->map(fn($s) => [
            'date' => $s->finished_at ? Carbon::parse($s->finished_at)->format('d M') : '',
            'score' => round($s->total_score, 1),
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
                    'avg_score' => round($group->avg('total_score'), 1),
                    'accuracy' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
                ];
            })
            ->values();

        $totalAttempts = $sessions->count();
        $avgScore = $sessions->avg('total_score') ?? 0;
        $bestScore = $sessions->max('total_score') ?? 0;
        $totalQuestions = $sessions->sum('total_question');
        $totalCorrect = $sessions->sum('correct_answer');
        $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;

        return Inertia::render('Gamification/Analytics', [
            'scoreOverTime' => $scoreOverTime,
            'accuracyOverTime' => $accuracyOverTime,
            'packageStats' => $packageStats,
            'totalAttempts' => $totalAttempts,
            'avgScore' => round($avgScore, 1),
            'bestScore' => round($bestScore, 1),
            'accuracy' => $accuracy,
        ]);
    }

    public function certificate(PracticeSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        if ($session->status !== 'completed') {
            return redirect()->back()->with('error', 'Sesi belum selesai!');
        }

        $session->load('user', 'package');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.certificate', [
            'session' => $session,
            'user' => $session->user,
            'package' => $session->package,
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('sertifikat-' . Str::slug($session->package?->title ?? 'tugas') . '-' . $session->id . '.pdf');
    }
}
