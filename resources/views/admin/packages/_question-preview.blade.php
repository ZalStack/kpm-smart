{{-- admin/packages/_question-preview.blade.php --}}
@php
    $options = $question['options'] ?? [];
    $correct = $question['correct_answer'] ?? '';
    $explanation = $question['explanation'] ?? '';
    $image = $question['image'] ?? null;
@endphp

<div class="preview-question space-y-4">

    {{-- Header Soal --}}
    <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-border">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-navy to-navy-light text-white text-sm font-bold shadow-md shadow-navy/20 flex-shrink-0">
            #{{ $index + 1 }}
        </span>
        @if($card)
            <span class="text-[10px] font-semibold px-3 py-1 rounded-full bg-gold-400/20 text-foreground">
                {{ $card['title'] }}
            </span>
        @endif
        <span class="text-[10px] text-muted-foreground bg-muted px-3 py-1 rounded-full">
            {{ count($options) }} Pilihan
        </span>
        @if(!empty($question['imported_from_pdf']))
            <span class="text-[10px] font-semibold px-3 py-1 rounded-full bg-primary/10 text-primary">
                📄 Import
            </span>
        @endif
    </div>

    {{-- Gambar Soal --}}
    @if($image)
        <div class="bg-muted rounded-md p-4 border border-border">
            <img src="{{ \App\Support\QuestionFormatter::imageUrl($image) }}"
                 alt="Gambar soal"
                 class="max-w-full max-h-80 object-contain rounded-md mx-auto"
                 style="display: block;"
                 onerror="this.style.display='none'">
        </div>
    @endif

    {{-- Teks Soal --}}
    <div class="text-foreground text-base leading-relaxed p-4 bg-muted/50 rounded-md border border-border">
        {!! \App\Support\QuestionFormatter::render($question['question']) !!}
    </div>

    {{-- Pilihan Jawaban --}}
    <div class="space-y-2.5">
        @foreach($options as $i => $option)
            @php
                $isCorrect = $option === $correct;
            @endphp
            <div class="flex items-start gap-3 p-3 rounded-md border transition-all duration-200
                {{ $isCorrect ? 'border-success-500 bg-success-500/5 shadow-sm shadow-success-500/10' : 'border-border bg-card hover:border-border' }}">

                <div class="flex items-center gap-3 w-full">
                    <span class="w-7 h-7 rounded-md flex-shrink-0 flex items-center justify-center text-xs font-bold
                        {{ $isCorrect ? 'bg-success-500 text-white shadow-sm shadow-success-500/30' : 'bg-muted text-muted-foreground' }}">
                        {{ chr(65 + $i) }}
                    </span>
                    <div class="text-sm text-muted-foreground flex-1 break-words option-label">
                        {!! \App\Support\QuestionFormatter::render($option) !!}
                    </div>
                    @if($isCorrect)
                        <span class="text-success-500 text-[10px] font-semibold bg-success-500/10 px-2.5 py-1 rounded-full flex-shrink-0">
                            ✔ Kunci
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Jawaban & Pembahasan --}}
    <div class="rounded-md border border-success-500/30 bg-gradient-to-br from-green-50 to-emerald-50/50 p-4">
        <p class="text-sm font-semibold text-success-500 flex items-center gap-2 flex-wrap">
            ✅ Jawaban Benar:
            <span class="bg-success-500 text-white text-xs px-3 py-1 rounded-full font-semibold">
                {{ $correct }}
            </span>
        </p>
        @if(!empty($explanation))
            <div class="mt-3 pt-3 border-t border-success-500/20">
                <p class="text-xs font-semibold text-primary mb-1.5 flex items-center gap-1.5">
                    💡 Pembahasan
                </p>
                <div class="text-sm text-muted-foreground leading-relaxed p-3 bg-card/60 rounded-md">
                    {!! \App\Support\QuestionFormatter::render($explanation) !!}
                </div>
            </div>
        @endif
    </div>
</div>
