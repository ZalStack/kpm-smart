<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PracticeSession;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PracticeStatistics extends Component
{
    use WithPagination;

    public $startDate = '';
    public $endDate = '';
    public $packageId = '';
    public $status = '';
    public $search = '';
    public $lastUpdated = '';

    protected $listeners = ['applyFilters' => 'applyFilters'];

    public function mount(Request $request)
    {
        $this->startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $this->endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $this->packageId = $request->get('package_id', '');
        $this->status = $request->get('status', '');
        $this->search = $request->get('search', '');
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function applyFilters()
    {
        $this->resetPage();
        $this->lastUpdated = now()->format('H:i:s');
        $this->dispatch('chartsUpdated');
    }

    public function refreshData()
    {
        $this->lastUpdated = now()->format('H:i:s');
        $this->dispatch('chartsUpdated');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function baseQuery()
    {
        $dateRange = [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ];

        $query = PracticeSession::query()
            ->whereBetween('created_at', $dateRange);

        if ($this->packageId) {
            $query->where('package_id', $this->packageId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $escapedSearch = \App\Support\SearchHelper::escapeLike($this->search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->whereHas('user', function ($uq) use ($escapedSearch) {
                    $uq->where('name', 'LIKE', "%{$escapedSearch}%")
                        ->orWhere('email', 'LIKE', "%{$escapedSearch}%");
                })->orWhereHas('package', function ($pq) use ($escapedSearch) {
                    $pq->where('title', 'LIKE', "%{$escapedSearch}%");
                });
            });
        }

        return $query;
    }

    public function render()
    {
        $baseFiltered = $this->baseQuery();

        $sessions = (clone $baseFiltered)
            ->with(['user', 'package', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $filteredCompleted = (clone $baseFiltered)->where('status', 'completed');

        $stats = [
            'total_sessions' => (clone $baseFiltered)->count(),
            'completed_sessions' => (clone $filteredCompleted)->count(),
            'in_progress_sessions' => (clone $baseFiltered)->where('status', 'in_progress')->count(),
            'total_users' => (clone $baseFiltered)->distinct('user_id')->count('user_id'),
            'total_packages' => (clone $baseFiltered)->distinct('package_id')->count('package_id'),
            'avg_score' => (clone $filteredCompleted)->avg('total_score') ?? 0,
            'total_questions' => (clone $filteredCompleted)->sum('total_question'),
            'correct_answers' => (clone $filteredCompleted)->sum('correct_answer'),
            'accuracy' => 0,
        ];

        if ($stats['total_questions'] > 0) {
            $stats['accuracy'] = round(($stats['correct_answers'] / $stats['total_questions']) * 100, 2);
        }

        $packages = Package::where('is_active', true)->get();

        $dailyActivity = (clone $baseFiltered)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress"),
                DB::raw('AVG(CASE WHEN status = "completed" THEN total_score ELSE NULL END) as avg_score')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $packageDistribution = (clone $baseFiltered)
            ->where('status', 'completed')
            ->select('package_id', DB::raw('COUNT(*) as count'))
            ->with('package')
            ->groupBy('package_id')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        $topUsers = (clone $baseFiltered)
            ->where('status', 'completed')
            ->select('user_id',
                DB::raw('COUNT(*) as session_count'),
                DB::raw('AVG(total_score) as avg_score'),
                DB::raw('SUM(total_question) as total_questions'),
                DB::raw('SUM(correct_answer) as correct_answers')
            )
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('session_count', 'desc')
            ->take(10)
            ->get();

        $recentActivities = (clone $baseFiltered)
            ->with(['user', 'package'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $statusDistribution = (clone $baseFiltered)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return view('livewire.practice-statistics', compact(
            'sessions',
            'stats',
            'packages',
            'dailyActivity',
            'packageDistribution',
            'topUsers',
            'recentActivities',
            'statusDistribution'
        ));
    }
}
