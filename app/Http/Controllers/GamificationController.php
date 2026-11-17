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
     * Kelompok bidang studi siswa: 'Matematika', 'IPA', atau nilai bidang mentah.
     * Dipakai papan peringkat agar bidang tidak pernah tercampur.
     */
    public static function bidangGroup(?string $bidang): string
    {
        $value = trim((string) $bidang);

        if ($value === '') {
            return 'Lainnya';
        }
        if (preg_match('/(mtk|matematika|math)/i', $value)) {
            return 'Matematika';
        }
        if (preg_match('/(ipa|sains|science)/i', $value)) {
            return 'IPA';
        }

        return $value;
    }

    /**
     * Papan peringkat siswa: top 10, disaring sesuai bidang dan level milik siswa login
     * sehingga peringkat Matematika dan IPA tidak pernah tercampur.
     */
    /**
     * Papan peringkat siswa: dipisahkan secara presisi berdasarkan Kelas, Level, dan Bidang siswa.
     * Secara default menampilkan cohort kelas, level, dan bidang siswa yang sedang login.
     * Siswa juga dapat beralih antar kelas, level, dan bidang dengan filter interaktif.
     */
    public function leaderboard(Request $request)
    {
        $me = Auth::user();

        $myBidangGroup = $me ? self::bidangGroup($me->bidang) : 'Lainnya';
        $myKelas = $me?->student_class ?? '';
        $myLevel = $me?->level ?? '';

        $availableKelas = User::where('role', 'user')
            ->whereNotNull('student_class')
            ->where('student_class', '!=', '')
            ->distinct()
            ->pluck('student_class')
            ->sort(SORT_NATURAL)
            ->values();

        $availableLevel = User::where('role', 'user')
            ->whereNotNull('level')
            ->where('level', '!=', '')
            ->distinct()
            ->pluck('level')
            ->sort(SORT_NATURAL)
            ->values();

        // Default filters:
        // Jika query string ada, gunakan query string (jika 'all' artinya tanpa filter).
        // Jika belum ada query string (kunjungan pertama):
        // - bidang default ke bidang siswa
        // - kelas default ke kelas siswa (jika profil siswa ada kelas)
        // - level default ke level siswa (jika profil siswa ada level)
        $selectedBidang = $request->has('bidang')
            ? trim((string) $request->query('bidang'))
            : ($me ? $myBidangGroup : '');

        $selectedKelas = $request->has('kelas')
            ? trim((string) $request->query('kelas'))
            : ($myKelas !== '' ? $myKelas : '');

        $selectedLevel = $request->has('level')
            ? trim((string) $request->query('level'))
            : ($myLevel !== '' ? $myLevel : '');

        if (strtolower($selectedBidang) === 'all') {
            $selectedBidang = '';
        }
        if (strtolower($selectedKelas) === 'all') {
            $selectedKelas = '';
        }
        if (strtolower($selectedLevel) === 'all') {
            $selectedLevel = '';
        }

        $rows = PracticeSession::query()
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
            ->orderByDesc('best_score')
            ->limit(2000)
            ->get();

        $usersById = User::whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');

        $mapped = $rows->map(function ($item) use ($usersById) {
            $u = $usersById->get($item->user_id);
            if (!$u) {
                return null;
            }

            $bg = self::bidangGroup($u->bidang);
            return [
                'user' => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'school_name' => $u->school_name,
                    'profile_photo_url' => $u->profile_photo_url,
                    'student_class' => $u->student_class,
                    'kelas' => $u->student_class,
                    'level' => $u->level,
                    'bidang' => $u->bidang,
                    'bidang_group' => $bg,
                ],
                'bidang' => $u->bidang,
                'bidang_group' => $bg,
                'level' => $u->level,
                'kelas' => $u->student_class,
                'total_attempts' => (int) $item->total_attempts,
                'avg_score' => round((float) $item->avg_score, 1),
                'best_score' => round((float) $item->best_score, 1),
            ];
        })->filter();

        $filtered = $mapped
            ->when($selectedBidang !== '', function ($collection) use ($selectedBidang) {
                if (in_array($selectedBidang, ['Matematika', 'IPA', 'Lainnya'], true)) {
                    return $collection->filter(fn ($entry) => $entry['bidang_group'] === $selectedBidang);
                }
                return $collection->filter(fn ($entry) => strcasecmp((string) $entry['bidang'], $selectedBidang) === 0);
            })
            ->when($selectedKelas !== '', fn ($collection) => $collection->filter(
                fn ($entry) => (string) $entry['kelas'] === $selectedKelas
            ))
            ->when($selectedLevel !== '', fn ($collection) => $collection->filter(
                fn ($entry) => (string) $entry['level'] === $selectedLevel
            ))
            ->sortBy([['avg_score', 'desc'], ['best_score', 'desc']])
            ->values();

        $ranked = $filtered->map(function ($entry, $index) {
            return array_merge(['rank' => $index + 1], $entry);
        });

        $currentUserRank = $me
            ? $ranked->firstWhere('user.id', $me->id)
            : null;

        $bidangLabel = $selectedBidang !== '' ? $selectedBidang : 'Semua Bidang';

        $isMyScope = (
            ($myBidangGroup === '' || $selectedBidang === '' || $selectedBidang === $myBidangGroup) &&
            ($myKelas === '' || $selectedKelas === '' || $selectedKelas === $myKelas) &&
            ($myLevel === '' || $selectedLevel === '' || $selectedLevel === $myLevel)
        );

        return Inertia::render('Gamification/Leaderboard', [
            'leaderboard' => $ranked->take(50)->values(),
            'currentUserRank' => $currentUserRank,
            'bidangLabel' => $bidangLabel,
            'selectedBidang' => $selectedBidang,
            'selectedKelas' => $selectedKelas,
            'selectedLevel' => $selectedLevel,
            'availableKelas' => $availableKelas,
            'availableLevel' => $availableLevel,
            'myScope' => [
                'bidang' => $myBidangGroup,
                'kelas' => $myKelas,
                'level' => $myLevel,
            ],
            'isMyScope' => $isMyScope,
            'totalParticipants' => $ranked->count(),
        ]);
    }

    /**
     * Papan peringkat admin: peringkat siswa per bidang studi, kelas, dan level.
     */
    public function adminLeaderboard(Request $request)
    {
        $selectedBidang = trim((string) $request->query('bidang'));
        $selectedKelas = trim((string) $request->query('kelas'));
        $selectedLevel = trim((string) $request->query('level'));

        if (strtolower($selectedBidang) === 'all') {
            $selectedBidang = '';
        }
        if (strtolower($selectedKelas) === 'all') {
            $selectedKelas = '';
        }
        if (strtolower($selectedLevel) === 'all') {
            $selectedLevel = '';
        }

        $availableKelas = User::where('role', 'user')
            ->whereNotNull('student_class')
            ->where('student_class', '!=', '')
            ->distinct()
            ->pluck('student_class')
            ->sort(SORT_NATURAL)
            ->values();

        $availableLevel = User::where('role', 'user')
            ->whereNotNull('level')
            ->where('level', '!=', '')
            ->distinct()
            ->pluck('level')
            ->sort(SORT_NATURAL)
            ->values();

        $rows = PracticeSession::query()
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
            ->orderByDesc('best_score')
            ->limit(2000)
            ->get();

        $usersById = User::whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');

        $bidangStats = ['Matematika' => 0, 'IPA' => 0, 'Lainnya' => 0];

        $leaderboard = $rows
            ->map(function ($item) use ($usersById, &$bidangStats) {
                $user = $usersById->get($item->user_id);
                if (!$user) {
                    return null;
                }

                $group = self::bidangGroup($user->bidang);
                $statKey = in_array($group, ['Matematika', 'IPA'], true) ? $group : 'Lainnya';
                $bidangStats[$statKey]++;

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'school_name' => $user->school_name,
                        'profile_photo_url' => $user->profile_photo_url,
                        'student_class' => $user->student_class,
                        'level' => $user->level,
                        'bidang' => $user->bidang,
                    ],
                    'bidang' => $user->bidang,
                    'bidang_group' => $group,
                    'level' => $user->level,
                    'kelas' => $user->student_class,
                    'total_attempts' => (int) $item->total_attempts,
                    'avg_score' => round((float) $item->avg_score, 1),
                    'best_score' => round((float) $item->best_score, 1),
                ];
            })
            ->filter()
            ->when($selectedBidang !== '', fn ($rows2) => $rows2->filter(
                fn ($entry) => $entry['bidang_group'] === $selectedBidang
            ))
            ->when($selectedKelas !== '', fn ($rows2) => $rows2->filter(
                fn ($entry) => (string) $entry['kelas'] === $selectedKelas
            ))
            ->when($selectedLevel !== '', fn ($rows2) => $rows2->filter(
                fn ($entry) => (string) $entry['level'] === $selectedLevel
            ))
            ->sortBy([['avg_score', 'desc'], ['best_score', 'desc']])
            ->values()
            ->map(fn ($entry, $index) => array_merge(['rank' => $index + 1], $entry))
            ->values();

        $totalStudents = User::where('role', 'user')->count();
        $attemptedStudents = $rows->count();

        return Inertia::render('Admin/Leaderboard/AdminLeaderboardIndex', [
            'leaderboard' => $leaderboard,
            'availableKelas' => $availableKelas,
            'availableLevel' => $availableLevel,
            'selectedBidang' => $selectedBidang,
            'selectedKelas' => $selectedKelas,
            'selectedLevel' => $selectedLevel,
            'totalStudents' => $totalStudents,
            'attemptedStudents' => $attemptedStudents,
            'notAttemptedStudents' => max(0, $totalStudents - $attemptedStudents),
            'bidangStats' => $bidangStats,
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
