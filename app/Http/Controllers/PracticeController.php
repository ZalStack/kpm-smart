<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PracticeController extends Controller
{
    public function index()
    {
        return redirect()->route('practice.history');
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

        $existingSession = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where('status', 'completed')
            ->first();

        if ($existingSession) {
            return redirect()->route('practice.show', $existingSession->id)
                ->with('info', 'Kamu sudah mengerjakan paket ini. Hasil latihan hanya bisa dikerjakan 1 kali.');
        }

        $inProgress = PracticeSession::where('user_id', Auth::id())
            ->where('package_id', $package->id)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgress) {
            return view('practice.start', compact('package', 'inProgress'));
        }

        $cardId = $request->card_id;
        $questions = collect($package->questions ?? [])
            ->where('card_id', $cardId)
            ->values()
            ->all();

        if (empty($questions)) {
            return redirect()->route('packages.show', $package->id)
                ->with('error', 'Tidak ada soal pada card ini!');
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

        return view('practice.start', compact('package', 'questions', 'session'));
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

        return view('practice.result', compact(
            'session', 'results', 'correct', 'wrong', 'unanswered', 'totalScore',
            'showAnswerKey', 'showExplanation', 'showScore'
        ));
    }

    public function history()
    {
        $sessions = PracticeSession::where('user_id', Auth::id())
            ->with('package')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('practice.history', compact('sessions'));
    }

    public function show(PracticeSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        $results = $session->answers ?? [];
        $package = $session->package;

        // Ambil pengaturan realtime dari package (bisa berubah oleh admin kapan saja)
        $showAnswerKey   = $package->canShowAnswerKey();
        $showExplanation = $package->canShowExplanation();
        $showScore       = $package->canShowScore();

        return view('practice.show', compact('session', 'results', 'showAnswerKey', 'showExplanation', 'showScore'));
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

        return view('practice.statistics', compact(
            'totalAttempts', 'bestScore', 'averageScore',
            'totalQuestions', 'correctAnswers', 'accuracy',
            'sessionsByPackage'
        ));
    }
}
