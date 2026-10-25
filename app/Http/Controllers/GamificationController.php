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

    /**
     * Papan peringkat siswa: top 10, disaring sesuai bidang & level milik siswa login.
     */
    public function leaderboard()
    {
        $me = Auth::user();

        $rows = $this->leaderboardQuery($me)
            ->limit(500)
            ->get()
            ->map(fn ($item, $index) => [
                'rank' => $index + 1,
                'user' => $this->leaderboardUser($item->user_id),
                'total_attempts' => $item->total_attempts,
                'avg_score' => round($item->avg_score, 1),
                'best_score' => round($item->best_score, 1),
            ])
            ->filter(fn ($item) => $item['user'] !== null)
            ->values();

        $currentUserRank = $me
            ? $rows->firstWhere('user.id', $me->id)
            : null;

        return Inertia::render('Gamification/Leaderboard', [
            'leaderboard' => $rows->take(10)->values(),
            'currentUserRank' => $currentUserRank,
        ]);
    }

    /**
     * Query agregat peringkat (rata-rata nilai lalu nilai tertinggi).
     */
    private function leaderboardQuery(?User $me)
    {
        $query = PracticeSession::query()
            ->join('users', 'users.id', '=', 'practice_sessions.user_id')
            ->where('practice_sessions.status', 'completed')
            ->where('users.role', 'user')
            ->select(
                'practice_sessions.user_id',
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('AVG(practice_sessions.total_score) as avg_score'),
                DB::raw('MAX(practice_sessions.total_score) as best_score')
            )
            ->groupBy('practice_sessions.user_id')
            ->orderByDesc('avg_score')
            ->orderByDesc('best_score');

        $this->applyBidangLevelScope($query, $me);

        return $query;
    }

    /**
     * Saring peringkat hanya untuk siswa satu bidang & level.
     */
    private function applyBidangLevelScope($query, ?User $me): void
    {
        if (!$me) {
            return;
        }

        $bidang = trim((string) $me->bidang);
        if ($bidang !== '') {
            $isMtk = (bool) preg_match('/(mtk|matematika|math)/i', $bidang);
            $isIpa = (bool) preg_match('/(ipa|sains|science)/i', $bidang);

            $query->where(function ($q) use ($isMtk, $isIpa) {
                if ($isMtk && !$isIpa) {
                    $q->whereRaw('LOWER(COALESCE(users.bidang, \'\')) LIKE ?', ['%mtk%'])
                      ->orWhereRaw('LOWER(COALESCE(users.bidang, \'\')) LIKE ?', ['%matematika%'])
                      ->orWhereRaw('LOWER(COALESCE(users.bidang, \'\')) LIKE ?', ['%math%']);
                } elseif ($isIpa && !$isMtk) {
                    $q->whereRaw('LOWER(COALESCE(users.bidang, \'\')) LIKE ?', ['%ipa%'])
                      ->orWhereRaw('LOWER(COALESCE(users.bidang, \'\')) LIKE ?', ['%sains%'])
                      ->orWhereRaw('LOWER(COALESCE(users.bidang, \'\')) LIKE ?', ['%science%']);
                }
            });
        }

        $level = trim((string) $me->level);
        if ($level !== '') {
            $query->whereRaw('LOWER(TRIM(COALESCE(users.level, \'\'))) = ?', [strtolower($level)]);
        }
    }

    private function leaderboardUser($userId): ?array
    {
        $user = User::find($userId);

        return $user ? ['id' => $user->id, 'name' => $user->name] : null;
    }

    /**
     * Papan peringkat admin: seluruh daftar siswa pada kelas terpilih.
     */
    public function adminLeaderboard(Request $request)
    {
        $kelas = $request->query('kelas');

        $availableKelas = User::where('role', 'user')
            ->whereNotNull('student_class')
            ->where('student_class', '!=', '')
            ->orderBy('student_class')
            ->pluck('student_class')
            ->unique()
            ->values();

        $query = User::where('role', 'user')
            ->when($kelas, fn ($q) => $q->where('student_class', $kelas));

        $users = $query->get()
            ->map(function ($user) {
                $sessions = PracticeSession::where('user_id', $user->id)
                    ->where('status', 'completed');

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'bidang' => $user->bidang,
                    'level' => $user->level,
                    'kelas' => $user->student_class,
                    'total_attempts' => (clone $sessions)->count(),
                    'avg_score' => round((clone $sessions)->avg('total_score') ?? 0, 1),
                    'best_score' => round((clone $sessions)->max('total_score') ?? 0, 1),
                ];
            })
            ->sortByDesc('avg_score')
            ->sortByDesc('best_score')
            ->values()
            ->map(fn ($entry, $index) => array_merge(['rank' => $index + 1], $entry));

        return Inertia::render('Admin/Leaderboard/AdminLeaderboardIndex', [
            'leaderboard' => $users,
            'availableKelas' => $availableKelas,
            'selectedKelas' => $kelas,
            'totalStudents' => $users->count(),
        ]);
    }

    public function analytics()
    {
        $sessions = PracticeSession::where('status', 'completed')
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
