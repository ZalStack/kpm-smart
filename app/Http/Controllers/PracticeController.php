<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PracticeController extends Controller
{
    public function index()
    {
        return redirect()->route('user.practice.history');
    }

    public function startRedirect(Package $package)
    {
        return redirect()->route('user.packages.show', $package->id);
    }

    public function start(Request $request, Package $package)
    {
        if (!$package->is_active) {
            return redirect()->route('user.packages.index')
                ->with('error', 'Paket ini belum aktif!');
        }

        $user = Auth::user();
        if ($user && $user->role === 'user') {
            $studentBidang = trim((string) $user->bidang);
            $isStudentMtk = (bool) preg_match('/(mtk|matematika|math)/i', $studentBidang);
            $isStudentIpa = (bool) preg_match('/(ipa|sains|science)/i', $studentBidang);

            $pkgBidang = (string) ($package->bidang ?? '');
            $pkgTitle = (string) ($package->title ?? '');
            $pkgIsIpa = (bool) (preg_match('/(ipa|sains)/i', $pkgBidang) || preg_match('/(ipa|sains)/i', $pkgTitle));
            $pkgIsMtk = (bool) (preg_match('/(mtk|matematika|math)/i', $pkgBidang) || preg_match('/(mtk|matematika|math)/i', $pkgTitle));

            if ($isStudentMtk && $pkgIsIpa) {
                return redirect()->route('user.packages.index')
                    ->with('error', 'Maaf, tugas ini khusus untuk bidang studi IPA. Anda terdaftar pada bidang Matematika.');
            }

            if ($isStudentIpa && $pkgIsMtk) {
                return redirect()->route('user.packages.index')
                    ->with('error', 'Maaf, tugas ini khusus untuk bidang studi Matematika. Anda terdaftar pada bidang IPA.');
            }
        }

        // Cek jadwal pengerjaan
        if ($package->schedule_status === 'expired') {
            return redirect()->route('user.packages.show', $package->id)
                ->with('error', 'Jadwal pengerjaan paket ini telah berakhir!');
        }

        if ($package->schedule_status === 'upcoming') {
            return redirect()->route('user.packages.show', $package->id)
                ->with('error', 'Paket ini belum bisa dikerjakan. Silakan tunggu jadwal mulai.');
        }

        $cardId = $request->card_id;

        // If card_id is not provided, pick first card if available
        if (! $cardId && ! empty($package->cards)) {
            $cardId = $package->cards[0]['id'] ?? null;
        }

        if (! $cardId) {
            $firstQ = collect($package->questions ?? [])->first();
            $cardId = $firstQ['card_id'] ?? 'card-1';
        }

        // Tanpa kunci jawaban: siswa tidak boleh bisa membaca kunci jawaban dari payload.
        $questions = $package->questionsForAttempt($cardId);

        if (empty($questions)) {
            return redirect()->route('user.packages.show', $package->id)
                ->with('error', 'Tidak ada soal pada card ini!');
        }

        // Check if there is an in-progress session for this card
        $inProgress = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where(function ($q) use ($cardId) {
                $q->where('card_id', $cardId)
                    ->orWhereNull('card_id')
                    ->orWhere('card_id', 'default');
            })
            ->where('status', 'in_progress')
            ->first();

        if ($inProgress) {
            $savedAnswers = [];
            if (! empty($inProgress->answers)) {
                foreach ($inProgress->answers as $idx => $item) {
                    $savedAnswers[$idx] = $item['user_answer'] ?? null;
                }
            }

            return Inertia::render('Practice/PracticeStart', [
                'package' => $package->summary(),
                'questions' => $questions,
                'session' => $inProgress->withoutRelations(),
                'timeLimitMinutes' => 0,
                'savedAnswers' => $savedAnswers,
                'savedCurrentIndex' => null,
                'savedDurationSeconds' => $inProgress->duration_seconds ?? 0,
            ]);
        }

        // Blokir jika sudah mengerjakan (1-attempt restriction - tidak bisa retry)
        $existingSession = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where(function ($q) use ($cardId) {
                $q->where('card_id', $cardId)
                    ->orWhereNull('card_id')
                    ->orWhere('card_id', 'default');
            })
            ->where('status', 'completed')
            ->latest()
            ->first();

        if ($existingSession) {
            return redirect()->route('user.practice.show', $existingSession->id)
                ->with('info', 'Kamu sudah mengerjakan tugas pada card ini. Soal hanya bisa dikerjakan 1 kali. Berikut hasil tugasmu.');
        }

        $session = PracticeSession::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'card_id' => $cardId ?: 'card-1',
            'total_question' => count($questions),
            'started_at' => now(),
            'status' => 'in_progress',
            'answers' => [],
        ]);

        return Inertia::render('Practice/PracticeStart', [
            'package' => $package->summary(),
            'questions' => $questions,
            'session' => $session->withoutRelations(),
            'timeLimitMinutes' => 0,
        ]);
    }

    public function submit(Request $request, PracticeSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $answers = $request->answers ?? [];
        $package = $session->package;
        $allQuestions = collect($package->questions ?? []);
        if ($session->card_id && $allQuestions->contains('card_id', $session->card_id)) {
            $questions = $allQuestions->where('card_id', $session->card_id)->values()->all();
        } else {
            $questions = $allQuestions->values()->all();
        }

        $correct = 0;
        $wrong = 0;
        $unanswered = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            $userAnswer = $answers[$index] ?? null;
            $questionType = $question['type'] ?? 'pilihan_ganda';

            if ($questionType === 'isian_singkat') {
                $isCorrect = strtolower(trim($userAnswer ?? '')) === strtolower(trim($question['correct_answer']));
            } else {
                $isCorrect = $userAnswer === $question['correct_answer'];
            }

            if ($userAnswer === null) {
                $unanswered++;
            } else if ($isCorrect) {
                $correct++;
            } else {
                $wrong++;
            }

            $resultItem = [
                'question' => $question['question'],
                'options' => $question['options'],
                'type' => $questionType,
                'correct_answer' => $question['correct_answer'],
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'image' => $question['image'] ?? null,
                // Selalu simpan explanation di DB supaya bisa ditampilkan nanti jika admin ubah setting
                'explanation' => $question['explanation'] ?? '',
            ];

            $results[] = $resultItem;
        }

        $totalScore = count($questions) > 0 ? ($correct / count($questions)) * 100 : 0;

        $session->update([
            'correct_answer' => $correct,
            'wrong_answer' => $wrong,
            'unanswered' => $unanswered,
            'total_score' => $totalScore,
            'duration_seconds' => $request->duration_seconds ?? 0,
            'finished_at' => now(),
            'status' => 'completed',
            'answers' => $results,
        ]);

        // Kirim laporan hasil belajar PR ke Web Induk kpm-student-smart
        $currentUser = Auth::user();
        if ($currentUser && !empty($currentUser->email)) {
            \App\Services\ParentWeeklyReportService::report(
                $currentUser->email,
                (int) round($totalScore),
                true,
                "PR / Latihan Soal: " . ($package->title ?? 'Latihan Mandiri')
            );
        }

        // Ambil pengaturan dari package (realtime)
        $showAnswerKey  = $package->canShowAnswerKey();
        $showExplanation = $package->canShowExplanation();
        $showScore      = $package->canShowScore();

        return Inertia::render('Practice/PracticeResult', [
            'session' => $session->withoutRelations(),
            'results' => $results,
            'correct' => $correct,
            'wrong' => $wrong,
            'unanswered' => $unanswered,
            'totalScore' => $totalScore,
            'showAnswerKey' => $showAnswerKey,
            'showExplanation' => $showExplanation,
            'showScore' => $showScore,
        ]);
    }

    public function history()
    {
        $sessions = PracticeSession::with('package')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        return Inertia::render('Practice/PracticeHistory', [
            'sessions' => $sessions,
        ]);
    }

    public function show(PracticeSession $session)
    {
        $package = $session->package;

        if ($session->status === 'in_progress') {
            $questions = $package->questionsForAttempt($session->card_id);

            $savedAnswers = [];
            if (!empty($session->answers)) {
                foreach ($session->answers as $idx => $item) {
                    $savedAnswers[$idx] = $item['user_answer'] ?? null;
                }
            }

            return Inertia::render('Practice/PracticeStart', [
                'package' => $package->summary(),
                'questions' => $questions,
                'session' => $session->withoutRelations(),
                'timeLimitMinutes' => 0,
                'savedAnswers' => $savedAnswers,
                'savedCurrentIndex' => null,
                'savedDurationSeconds' => $session->duration_seconds ?? 0,
            ]);
        }

        $results = $session->answers ?? [];

        // Ambil pengaturan realtime dari package (bisa berubah oleh admin kapan saja)
        $showAnswerKey   = $package ? $package->canShowAnswerKey() : true;
        $showExplanation = $package ? $package->canShowExplanation() : true;
        $showScore       = $package ? $package->canShowScore() : true;

        return Inertia::render('Practice/PracticeShow', [
            'session' => $session->withoutRelations(),
            'results' => $results,
            'showAnswerKey' => $showAnswerKey,
            'showExplanation' => $showExplanation,
            'showScore' => $showScore,
        ]);
    }

    public function saveAnswers(Request $request, PracticeSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Akses ditolak!'], 403);
        }

        if ($session->status !== 'in_progress') {
            return response()->json(['error' => 'Sesi sudah selesai.'], 422);
        }

        $answers = $request->answers ?? [];

        $package = $session->package;
        $allQuestions = collect($package->questions ?? []);
        if ($session->card_id && $allQuestions->contains('card_id', $session->card_id)) {
            $questions = $allQuestions->where('card_id', $session->card_id)->values()->all();
        } else {
            $questions = $allQuestions->values()->all();
        }

        $results = [];
        $correct = 0;
        $wrong = 0;
        $unanswered = 0;

        foreach ($questions as $index => $question) {
            $userAnswer = $answers[$index] ?? null;
            $questionType = $question['type'] ?? 'pilihan_ganda';

            if ($questionType === 'isian_singkat') {
                $isCorrect = strtolower(trim($userAnswer ?? '')) === strtolower(trim($question['correct_answer']));
            } else {
                $isCorrect = $userAnswer === $question['correct_answer'];
            }

            if ($userAnswer === null) {
                $unanswered++;
            } else if ($isCorrect) {
                $correct++;
            } else {
                $wrong++;
            }

            // Autosave hanya butuh informasi untuk melanjutkan sesi, bukan
            // penilaian. `correct_answer`, `is_correct`, dan `explanation`
            // SENGAJA tidak disimpan di sini: kalau ikut tersimpan, payload
            // `session` pada halaman Kerjakan akan membocorkan kunci jawaban
            // di tengah pengerjaan. Penilaian final dihitung ulang oleh
            // submit() dari jawaban yang dikirim pengguna.
            $results[] = [
                'question' => $question['question'],
                'options' => $question['options'],
                'type' => $questionType,
                'user_answer' => $userAnswer,
                'image' => $question['image'] ?? null,
            ];
        }

        $session->update([
            'answers' => $results,
            'correct_answer' => $correct,
            'wrong_answer' => $wrong,
            'unanswered' => $unanswered,
            'duration_seconds' => $request->duration_seconds ?? $session->duration_seconds,
        ]);

        return response()->json(['success' => true, 'saved_at' => now()->toISOString()]);
    }

    public function statistics()
    {
        $sessions = PracticeSession::where('status', 'completed')
            ->with('package')
            ->get();

        $totalAttempts = $sessions->count();
        $bestScore = (float) ($sessions->max('total_score') ?? 0);
        $averageScore = (float) ($sessions->avg('total_score') ?? 0);
        $totalQuestions = $sessions->sum('total_question');
        $correctAnswers = $sessions->sum('correct_answer');
        $accuracy = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        $sessionsByPackage = $sessions->groupBy('package_id')->map(function($group) {
            $first = $group->first();
            return [
                'package' => $first && $first->package ? $first->package->title : 'Unknown',
                'attempts' => $group->count(),
                'avg_score' => $group->avg('total_score'),
                'best_score' => $group->max('total_score'),
            ];
        });

        return Inertia::render('Practice/PracticeStatistics', [
            'totalAttempts' => $totalAttempts,
            'bestScore' => $bestScore,
            'averageScore' => $averageScore,
            'totalQuestions' => $totalQuestions,
            'correctAnswers' => $correctAnswers,
            'accuracy' => $accuracy,
            'sessionsByPackage' => array_values($sessionsByPackage->toArray()),
        ]);
    }
}
