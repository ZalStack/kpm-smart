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
        return redirect()->route('practice.history');
    }

    public function startRedirect(Package $package)
    {
        $inProgress = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgress) {
            return redirect()->route('practice.show', $inProgress->id);
        }

        return redirect()->route('packages.show', $package->id);
    }

    public function start(Request $request, Package $package)
    {
        if (!$package->is_active) {
            return redirect()->route('packages.index')
                ->with('error', 'Paket ini belum aktif!');
        }

        // Cek jadwal pengerjaan
        if ($package->schedule_status === 'expired') {
            return redirect()->route('packages.show', $package->id)
                ->with('error', 'Jadwal pengerjaan paket ini telah berakhir!');
        }

        if ($package->schedule_status === 'upcoming') {
            return redirect()->route('packages.show', $package->id)
                ->with('error', 'Paket ini belum bisa dikerjakan. Silakan tunggu jadwal mulai.');
        }

        $cardId = $request->card_id;

        // If card_id is not provided, pick first card if available
        if (!$cardId && !empty($package->cards)) {
            $cardId = $package->cards[0]['id'] ?? null;
        }

        $questions = collect($package->questions ?? [])
            ->where('card_id', $cardId)
            ->values()
            ->all();

        if (empty($questions)) {
            return redirect()->route('packages.show', $package->id)
                ->with('error', 'Tidak ada soal pada card ini!');
        }

        // Check if there is an in-progress session for this card
        $inProgress = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where('card_id', $cardId)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgress) {
            return Inertia::render('Practice/PracticeStart', [
                'package' => $package,
                'questions' => $questions,
                'session' => $inProgress,
                'timeLimitMinutes' => 0,
            ]);
        }

        // Blokir jika sudah mengerjakan (1-attempt restriction - tidak bisa retry)
        $existingSession = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where('card_id', $cardId)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if ($existingSession) {
            return redirect()->route('practice.show', $existingSession->id)
                ->with('info', 'Kamu sudah mengerjakan tugas pada card ini. Soal hanya bisa dikerjakan 1 kali. Berikut hasil tugasmu.');
        }

        $session = PracticeSession::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'card_id' => $cardId,
            'total_question' => count($questions),
            'started_at' => now(),
            'status' => 'in_progress',
            'answers' => [],
        ]);

        return Inertia::render('Practice/PracticeStart', [
            'package' => $package,
            'questions' => $questions,
            'session' => $session,
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
        $questions = collect($package->questions ?? [])
            ->where('card_id', $session->card_id)
            ->values()
            ->all();

        $correct = 0;
        $wrong = 0;
        $unanswered = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            $userAnswer = $answers[$index] ?? null;
            $isCorrect = $userAnswer === $question['correct_answer'];

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

        // Ambil pengaturan dari package (realtime)
        $showAnswerKey  = $package->canShowAnswerKey();
        $showExplanation = $package->canShowExplanation();
        $showScore      = $package->canShowScore();

        return Inertia::render('Practice/PracticeResult', [
            'session' => $session,
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
        $sessions = PracticeSession::where('user_id', Auth::id())
            ->with('package')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        return Inertia::render('Practice/PracticeHistory', [
            'sessions' => $sessions,
        ]);
    }

    public function show(PracticeSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $package = $session->package;

        if ($session->status === 'in_progress') {
            $questions = collect($package->questions ?? [])
                ->where('card_id', $session->card_id)
                ->values()
                ->all();

            return Inertia::render('Practice/PracticeStart', [
                'package' => $package,
                'questions' => $questions,
                'session' => $session,
                'timeLimitMinutes' => 0,
            ]);
        }

        $results = $session->answers ?? [];

        // Ambil pengaturan realtime dari package (bisa berubah oleh admin kapan saja)
        $showAnswerKey   = $package ? $package->canShowAnswerKey() : true;
        $showExplanation = $package ? $package->canShowExplanation() : true;
        $showScore       = $package ? $package->canShowScore() : true;

        return Inertia::render('Practice/PracticeShow', [
            'session' => $session,
            'results' => $results,
            'showAnswerKey' => $showAnswerKey,
            'showExplanation' => $showExplanation,
            'showScore' => $showScore,
        ]);
    }

    public function statistics()
    {
        $sessions = PracticeSession::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->with('package')
            ->get();

        $totalAttempts = $sessions->count();
        $bestScore = $sessions->max('total_score') ?? 0;
        $averageScore = $sessions->avg('total_score') ?? 0;
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
