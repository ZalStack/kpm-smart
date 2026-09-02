{{-- user/practice/result.blade.php --}}
@extends('layouts.app')

@section('title', 'Hasil Latihan')

@section('content')

<div class="space-y-6 result-stagger">
    <!-- Result Summary -->
    <div class="bg-card rounded-lg p-4 md:p-8 shadow-sm border border-border relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_0%,rgba(0,162,233,0.05)_0%,transparent_50%)] pointer-events-none"></div>
        <div class="text-center relative z-10">
            @if($showScore ?? true)
                <div class="text-5xl md:text-6xl mb-4">
                    @if($totalScore >= 80) 🎉 @elseif($totalScore >= 60) 😊 @else 💪 @endif
                </div>
            @else
                <div class="text-5xl md:text-6xl mb-4">✅</div>
            @endif
            <h1 class="text-xl md:text-3xl font-bold text-foreground">Hasil Latihan</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">{{ $session->package->title }}</p>

            <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-2 md:gap-4 max-w-2xl mx-auto">
                @if($showScore ?? true)
                    <div class="p-2 md:p-3 bg-muted rounded-lg">
                        <p class="text-[10px] text-muted-foreground">Skor</p>
                        <p class="text-base md:text-xl font-bold text-foreground">{{ number_format($totalScore, 1) }}</p>
                    </div>
                @endif
                @if($showAnswerKey ?? true)
                    <div class="p-2 md:p-3 bg-success-50 rounded-lg">
                        <p class="text-[10px] text-muted-foreground">Benar</p>
                        <p class="text-base md:text-xl font-bold text-success-500">{{ $correct }}</p>
                    </div>
                    <div class="p-2 md:p-3 bg-danger-50 rounded-lg">
                        <p class="text-[10px] text-muted-foreground">Salah</p>
                        <p class="text-base md:text-xl font-bold text-danger-500">{{ $wrong }}</p>
                    </div>
                    <div class="p-2 md:p-3 bg-muted rounded-lg">
                        <p class="text-[10px] text-muted-foreground">Tidak Dijawab</p>
                        <p class="text-base md:text-xl font-bold text-muted-foreground">{{ $unanswered }}</p>
                    </div>
                @endif
                <div class="p-2 md:p-3 bg-primary/10 rounded-lg col-span-2 md:col-span-1">
                    <p class="text-[10px] text-muted-foreground">Durasi</p>
                    <p class="text-base md:text-xl font-bold text-primary">
                        @php $minutes = floor($session->duration_seconds / 60); $seconds = $session->duration_seconds % 60; @endphp
                        {{ $minutes }}:{{ str_pad($seconds, 2, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>

            {{-- Info jika beberapa pengaturan disembunyikan --}}
            @if(!($showScore ?? true) || !($showAnswerKey ?? true))
                <div class="mt-4 inline-flex items-center gap-2 bg-muted/70 text-muted-foreground text-xs px-4 py-2 rounded-full">
                    🔒 Beberapa informasi disembunyikan oleh pengatur paket
                </div>
            @endif
        </div>
    </div>

    <!-- Question Details -->
    @if($showAnswerKey ?? true)
        <div>
            <h2 class="text-lg md:text-xl font-bold text-foreground mb-4">📋 Detail Jawaban</h2>
            <div class="space-y-3 md:space-y-4">
                @foreach($results as $index => $result)
                    <div class="bg-card rounded-md p-4 md:p-6 shadow-sm border-l-4 {{ $result['is_correct'] ? 'border-success-500' : 'border-danger-500' }}">
                        <div class="flex flex-col md:flex-row md:items-start gap-3 md:gap-4">
                            <span class="text-base md:text-lg font-bold text-foreground flex-shrink-0">#{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    @if($result['is_correct'])
                                        <span class="text-success-500 text-sm font-semibold">✅ Benar</span>
                                    @else
                                        <span class="text-danger-500 text-sm font-semibold">❌ Salah</span>
                                    @endif
                                </div>
                                @if(!empty($result['image']))
                                    <img src="{{ \App\Support\QuestionFormatter::imageUrl($result['image']) }}"
                                         alt="Gambar soal"
                                         class="mt-2 max-w-full rounded-lg border border-border max-h-48 md:max-h-64 object-contain"
                                         onerror="this.style.display='none'">
                                @endif
                                <div class="mt-2 text-muted-foreground question-content text-sm md:text-base">
                                    {!! \App\Support\QuestionFormatter::render($result['question']) !!}
                                </div>
                                <div class="mt-3 space-y-1 text-xs md:text-sm">
                                    <p>
                                        <span class="text-muted-foreground">Jawaban Anda:</span>
                                        <span class="{{ $result['is_correct'] ? 'text-success-500 font-semibold' : 'text-danger-500 font-semibold' }}">
                                            {{ $result['user_answer'] ?? 'Tidak dijawab' }}
                                        </span>
                                    </p>
                                    <p>
                                        <span class="text-muted-foreground">Jawaban Benar:</span>
                                        <span class="text-success-500 font-semibold">{{ $result['correct_answer'] }}</span>
                                    </p>
                                </div>
                                @if(($showExplanation ?? true) && !empty($result['explanation']))
                                    <div class="mt-3 p-3 bg-muted rounded-lg">
                                        <p class="text-xs md:text-sm font-semibold text-primary">💡 Pembahasan:</p>
                                        <div class="text-xs md:text-sm text-muted-foreground mt-1 question-content">
                                            {!! \App\Support\QuestionFormatter::render($result['explanation']) !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Kunci jawaban disembunyikan --}}
        <div class="bg-card rounded-lg p-8 text-center shadow-sm border border-border">
            <div class="text-4xl mb-3">🔒</div>
            <h3 class="font-bold text-foreground text-base md:text-lg">Kunci Jawaban Disembunyikan</h3>
            <p class="text-muted-foreground text-sm mt-2">Pengatur paket menyembunyikan detail jawaban untuk paket ini.</p>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
        <a href="{{ route('packages.show', $session->package_id) }}" class="bg-gold-400 text-foreground text-center px-4 md:px-6 py-2 rounded-lg font-semibold hover:bg-gold-500 transition text-sm md:text-base">🔄 Ulangi Latihan</a>
        <a href="{{ route('practice.history') }}" class="bg-navy-light text-white text-center px-4 md:px-6 py-2 rounded-lg font-semibold hover:bg-navy transition text-sm md:text-base">📊 Lihat Riwayat</a>
        <a href="{{ route('packages.index') }}" class="bg-muted text-foreground hover:bg-muted text-center px-4 md:px-6 py-2 rounded-lg font-semibold transition text-sm md:text-base">📚 Kembali ke Paket</a>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/contrib/auto-render.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.renderMathInElement) {
        renderMathInElement(document.body, {
            delimiters: [{ left: '$$', right: '$$', display: true }, { left: '$', right: '$', display: false }],
            throwOnError: false
        });
    }
});
</script>
@endpush
@endsection
