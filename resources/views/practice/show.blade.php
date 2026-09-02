{{-- user/practice/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Latihan')

@section('content')

<div class="space-y-6 show-stagger">
    <div class="flex flex-wrap items-center gap-2 md:gap-4">
        <a href="{{ route('practice.history') }}" class="text-primary hover:text-foreground text-sm md:text-base font-medium">← Kembali</a>
        <h1 class="text-xl md:text-2xl font-bold text-foreground">📋 Detail Latihan</h1>
    </div>

    <div class="bg-card rounded-md p-4 md:p-6 shadow-sm border border-border overflow-x-auto">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 min-w-[300px]">
            <div><p class="text-[10px] text-muted-foreground">Paket</p><p class="font-semibold text-foreground text-sm md:text-base truncate">{{ $session->package->title }}</p></div>
            <div><p class="text-[10px] text-muted-foreground">Skor</p><p class="font-bold text-foreground text-sm md:text-base">{{ number_format($session->total_score, 1) }}</p></div>
            <div><p class="text-[10px] text-muted-foreground">Benar</p><p class="font-bold text-success-500 text-sm md:text-base">{{ $session->correct_answer }}</p></div>
            <div><p class="text-[10px] text-muted-foreground">Salah</p><p class="font-bold text-danger-500 text-sm md:text-base">{{ $session->wrong_answer }}</p></div>
            <div class="col-span-2 md:col-span-1"><p class="text-[10px] text-muted-foreground">Tanggal</p><p class="text-xs md:text-sm">{{ $session->created_at->format('d M Y H:i') }}</p></div>
        </div>
    </div>

    <div class="space-y-3 md:space-y-4">
        @foreach($results as $index => $result)
            <div class="bg-card rounded-md p-4 md:p-6 shadow-sm border-l-4 {{ $result['is_correct'] ? 'border-success-500' : 'border-danger-500' }}">
                <div class="flex flex-col md:flex-row md:items-start gap-3 md:gap-4">
                    <span class="font-bold text-foreground text-sm md:text-base flex-shrink-0">#{{ $index + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        @if(!empty($result['image']))
                            <img src="{{ \App\Support\QuestionFormatter::imageUrl($result['image']) }}"
                                 alt="Gambar soal"
                                 class="mb-2 max-w-full rounded-lg border border-border max-h-48 md:max-h-64 object-contain"
                                 onerror="this.style.display='none'">
                        @endif
                        <div class="text-muted-foreground question-content text-sm md:text-base">
                            {!! \App\Support\QuestionFormatter::render($result['question']) !!}
                        </div>
                        <div class="mt-2 text-xs md:text-sm space-y-1">
                            <p><span class="text-muted-foreground">Jawaban Anda:</span>
                                <span class="{{ $result['is_correct'] ? 'text-success-500' : 'text-danger-500' }} font-semibold">
                                    {{ $result['user_answer'] ?? 'Tidak dijawab' }}
                                </span>
                            </p>
                            <p><span class="text-muted-foreground">Jawaban Benar:</span>
                                <span class="text-success-500 font-semibold">{{ $result['correct_answer'] }}</span>
                            </p>
                        </div>
                        @if(!empty($result['explanation']) && !($hideExplanation ?? false))
                            <div class="mt-2 p-3 bg-muted rounded-lg">
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
