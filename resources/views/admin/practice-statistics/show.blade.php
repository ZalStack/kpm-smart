{{-- admin/practice-statistics/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Sesi Pengerjaan - Admin')
@section('header-title', 'Detail Sesi Pengerjaan')
@section('header-sub', 'Lihat detail lengkap sesi pengerjaan soal')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('admin.practice-statistics.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali
        </a>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                Print
            </button>
        </div>
    </div>

    <!-- Session Info -->
    <div class="admin-card overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-border">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-brand-900">Sesi {{ $session->id }}</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ $session->user?->name ?? 'Unknown' }} &bull;
                            {{ $session->package?->title ?? 'Deleted Package' }}
                        </p>
                    </div>
                </div>
                @if($session->status == 'completed')
                    <span class="badge-success text-sm py-2 px-4">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                @elseif($session->status == 'in_progress')
                    <span class="badge-warning text-sm py-2 px-4">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                @else
                    <span class="badge-danger text-sm py-2 px-4">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                @endif
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <div class="stat-card stagger-item !p-4 text-center">
                    <p class="form-label">Total Soal</p>
                    <p class="text-2xl font-bold text-brand-900">{{ $session->total_question }}</p>
                </div>
                <div class="stat-card stagger-item !p-4 text-center">
                    <p class="form-label">Benar</p>
                    <p class="text-2xl font-bold text-success-500">{{ $session->correct_answer }}</p>
                </div>
                <div class="stat-card stagger-item !p-4 text-center">
                    <p class="form-label">Salah</p>
                    <p class="text-2xl font-bold text-danger-500">{{ $session->wrong_answer }}</p>
                </div>
                <div class="stat-card stagger-item !p-4 text-center">
                    <p class="form-label">Nilai</p>
                    <p class="text-2xl font-bold text-gold-600">{{ number_format($session->total_score ?? 0, 1) }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-border">
                <div class="stagger-item">
                    <p class="form-label">Waktu Mulai</p>
                    <p class="font-medium text-brand-900 mt-1">{{ $session->started_at ? Carbon\Carbon::parse($session->started_at)->format('d/m/Y H:i:s') : '-' }}</p>
                </div>
                <div class="stagger-item">
                    <p class="form-label">Waktu Selesai</p>
                    <p class="font-medium text-brand-900 mt-1">{{ $session->finished_at ? Carbon\Carbon::parse($session->finished_at)->format('d/m/Y H:i:s') : '-' }}</p>
                </div>
                <div class="stagger-item">
                    <p class="form-label">Durasi</p>
                    <p class="font-medium text-brand-900 mt-1">{{ $session->duration_seconds ? gmdate('i:s', $session->duration_seconds) : '-' }}</p>
                </div>
                <div class="stagger-item">
                    <p class="form-label">Card ID</p>
                    <p class="font-medium text-brand-900 font-mono mt-1">{{ $session->card_id ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-border">
                <div class="stagger-item">
                    <p class="form-label">Batas Waktu</p>
                    <p class="font-medium text-brand-900 mt-1">
                        @if($timeLimitMinutes)
                            {{ $timeLimitMinutes }} menit
                        @else
                            <span class="text-muted-foreground">Tidak dibatasi</span>
                        @endif
                    </p>
                </div>
                <div class="stagger-item">
                    <p class="form-label">Tampilkan Pembahasan</p>
                    <p class="font-medium mt-1">
                        @if($hideExplanation)
                            <span class="text-danger-500 font-semibold">Disembunyikan</span>
                        @else
                            <span class="text-success-500 font-semibold">Ditampilkan</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Answers -->
    @if(!empty($results) && count($results) > 0)
        <div class="admin-card overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-border">
                <h3 class="text-sm font-semibold text-brand-900">Detail Jawaban</h3>
            </div>
            <div class="divide-y divide-border">
                @foreach($results as $index => $result)
                    <div class="p-4 hover:bg-brand-50/30 transition {{ isset($result['is_correct']) && $result['is_correct'] ? 'border-l-4 border-l-success-500' : 'border-l-4 border-l-danger-500' }}">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-md bg-muted flex items-center justify-center text-sm font-semibold text-muted-foreground">{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-foreground">{{ $result['question'] ?? 'Soal tidak tersedia' }}</p>

                                @if(isset($result['options']) && is_array($result['options']))
                                    <div class="mt-2 space-y-1">
                                        @foreach($result['options'] as $key => $option)
                                            <div class="flex items-center gap-2 text-sm py-1 px-3 rounded-md
                                                @if($option == $result['correct_answer']) bg-success-50 border border-success-500/20
                                                @elseif($option == $result['user_answer'] && $option != $result['correct_answer']) bg-danger-50 border border-danger-500/20
                                                @else bg-muted @endif">
                                                <span class="font-semibold text-muted-foreground w-6">{{ chr(65 + $key) }}.</span>
                                                <span class="text-foreground">{{ $option }}</span>
                                                @if($option == $result['correct_answer'])
                                                    <span class="ml-auto text-success-500 text-xs font-semibold">
                                                        <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                        Benar
                                                    </span>
                                                @endif
                                                @if($option == $result['user_answer'] && $option != $result['correct_answer'])
                                                    <span class="ml-auto text-danger-500 text-xs font-semibold">
                                                        <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        Salah
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                    <span class="form-label">Jawaban Anda:</span>
                                    <span class="font-bold {{ isset($result['is_correct']) && $result['is_correct'] ? 'text-success-500' : 'text-danger-500' }}">
                                        {{ $result['user_answer'] ?? 'Tidak dijawab' }}
                                    </span>
                                    <span class="text-muted-foreground">|</span>
                                    <span class="form-label">Jawaban Benar:</span>
                                    <span class="font-bold text-success-500">{{ $result['correct_answer'] }}</span>
                                </div>

                                @if(!$hideExplanation && !empty($result['explanation']) && $result['explanation'] != 'Tidak ada pembahasan')
                                    <div class="mt-3 bg-accent-50 rounded-md p-3 text-sm">
                                        <span class="font-semibold text-primary">
                                            <svg class="w-4 h-4 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                                            Pembahasan:
                                        </span>
                                        <p class="text-foreground mt-1">{{ $result['explanation'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="admin-card p-6 md:p-8 text-center">
            <div class="empty-state">
                <div class="w-12 h-12 mx-auto bg-muted rounded-md flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <p class="text-muted-foreground text-sm">Belum ada data jawaban</p>
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .main-content, .main-content * { visibility: visible; }
    .main-content { position: absolute; left: 0; top: 0; width: 100%; }
    .admin-header, .btn-print, form button { display: none !important; }
    .bg-card { background: white !important; box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    .rounded-md { border-radius: 0 !important; }
}
</style>
@endsection
