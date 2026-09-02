{{-- user/practice/start.blade.php --}}
@extends('layouts.app')

@section('title', 'Latihan Soal')

@section('content')

<div class="space-y-6 practice-stagger">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-foreground">📝 {{ $package->title }}</h1>
            <p class="text-muted-foreground text-sm md:text-base">Total {{ count($questions) }} soal</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 md:gap-4">
            <span class="text-xs md:text-sm text-muted-foreground">⏱️ <span id="timer" class="{{ ($timeLimitMinutes ?? 0) > 0 ? 'font-bold text-danger-500' : '' }}">00:00</span></span>
            <span class="text-xs md:text-sm text-muted-foreground">📊 <span id="progress">0/{{ count($questions) }}</span></span>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Question Grid (Left) -->
        <div class="lg:w-64 flex-shrink-0">
            <div class="bg-card rounded-lg p-4 shadow-sm border border-border sticky top-20">
                <h3 class="text-sm font-semibold text-foreground mb-3">Navigasi Soal</h3>
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-2" id="questionGrid">
                    @foreach($questions as $index => $question)
                        <button type="button" class="question-nav w-full aspect-square rounded-lg text-sm font-medium transition-all duration-200
                            bg-muted text-muted-foreground hover:bg-muted border border-border"
                            data-index="{{ $index }}"
                            onclick="goToQuestion({{ $index }})">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-border flex flex-wrap gap-3 text-xs text-muted-foreground">
                    <div class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-muted border border-border"></span>
                        <span>Soal</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-navy-light"></span>
                        <span>Soal Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions (Right) -->
        <div class="flex-1">
            <form id="practice-form" action="{{ route('practice.submit', $session->id) }}" method="POST">
                @csrf
                <input type="hidden" name="duration_seconds" id="duration_seconds" value="0">

                <div class="space-y-4" id="questionsContainer">
                    @foreach($questions as $index => $question)
                        <div class="question-item bg-card rounded-md p-4 md:p-6 shadow-sm border border-border {{ $index > 0 ? 'hidden' : '' }}" data-index="{{ $index }}">
                            <div class="flex flex-wrap items-start justify-between gap-2 mb-4">
                                <h3 class="font-semibold text-foreground text-sm md:text-base">
                                    Soal {{ $index + 1 }} dari {{ count($questions) }}
                                </h3>
                                <span class="text-[10px] text-muted-foreground flex-shrink-0">Bobot: 1 poin</span>
                            </div>

                            @if(!empty($question['image']))
                                <img src="{{ \App\Support\QuestionFormatter::imageUrl($question['image']) }}"
                                     alt="Gambar soal"
                                     class="mb-4 max-w-full rounded-lg border border-border max-h-48 md:max-h-64 object-contain"
                                     onerror="this.style.display='none'">
                            @endif

                            <div class="text-muted-foreground question-content text-sm md:text-base">
                                {!! \App\Support\QuestionFormatter::render($question['question']) !!}
                            </div>

                            <div class="mt-4 space-y-2">
                                @foreach($question['options'] as $option)
                                    <label class="flex items-center p-3 border border-border rounded-md hover:bg-primary/5 hover:border-primary/30 cursor-pointer transition-all duration-200 text-sm md:text-base">
                                        <input type="radio" name="answers[{{ $index }}]" value="{{ $option }}"
                                               class="question-radio w-4 h-4 text-primary focus:ring-primary flex-shrink-0"
                                               data-index="{{ $index }}">
                                        <span class="ml-3 text-muted-foreground break-words">{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Navigation Buttons per Question -->
                            <div class="flex flex-wrap justify-between gap-3 mt-6 pt-4 border-t border-border">
                                <button type="button" onclick="prevQuestion()"
                                        class="prev-btn px-4 py-2 bg-muted text-muted-foreground rounded-lg hover:bg-muted transition text-sm font-medium {{ $index == 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    ← Sebelumnya
                                </button>
                                <button type="button" onclick="nextQuestion()"
                                        class="next-btn px-4 py-2 bg-muted text-muted-foreground rounded-lg hover:bg-muted transition text-sm font-medium {{ $index == count($questions) - 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    Selanjutnya →
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Button -->
                <div class="sticky bottom-0 bg-card/95 backdrop-blur-sm border-t border-border p-3 md:p-4 shadow-lg mt-6 rounded-t-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-3 md:gap-4">
                            <span class="text-xs md:text-sm text-muted-foreground">Terjawab: <span id="answered">0</span>/{{ count($questions) }}</span>
                            <span class="text-xs md:text-sm text-muted-foreground">⏱️ <span id="timer-display" class="{{ ($timeLimitMinutes ?? 0) > 0 ? 'font-bold text-danger-500' : '' }}">00:00</span></span>
                        </div>
                        <button type="submit" class="bg-navy-light text-white py-2 px-6 md:px-8 rounded-lg font-semibold hover:bg-navy transition text-sm md:text-base w-full sm:w-auto">
                            Selesai & Lihat Hasil ✅
                        </button>
                    </div>
                </div>
            </form>
        </div>
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

    // Timer
    const timeLimitMinutes = {{ $timeLimitMinutes ?? 0 }};
    let totalSeconds = 0;
    let maxSeconds = timeLimitMinutes * 60;
    let isCountdown = maxSeconds > 0;
    let seconds = isCountdown ? maxSeconds : 0;
    const timerDisplay = document.getElementById('timer');
    const timerDisplay2 = document.getElementById('timer-display');
    const durationInput = document.getElementById('duration_seconds');

    function formatTime(s) {
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
    }

    function updateTimerDisplay() {
        timerDisplay.textContent = formatTime(seconds);
        timerDisplay2.textContent = formatTime(seconds);
    }

    if (isCountdown) {
        updateTimerDisplay();
        durationInput.value = maxSeconds - seconds;
    } else {
        updateTimerDisplay();
    }

    const timerInterval = setInterval(function() {
        if (isCountdown) {
            seconds--;
            durationInput.value = maxSeconds - seconds;
            if (seconds <= 0) {
                clearInterval(timerInterval);
                alert('⏰ Waktu habis! Jawaban akan dikumpulkan otomatis.');
                document.getElementById('practice-form').submit();
                return;
            }
            if (seconds <= 60) {
                timerDisplay.classList.add('animate-pulse');
                timerDisplay2.classList.add('animate-pulse');
            }
        } else {
            seconds++;
            durationInput.value = seconds;
        }
        updateTimerDisplay();
    }, 1000);

    // Track answered questions
    const totalQuestions = {{ count($questions) }};
    const answeredSpan = document.getElementById('answered');
    const progressSpan = document.getElementById('progress');
    const radioButtons = document.querySelectorAll('.question-radio');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });

    function updateProgress() {
        const answered = document.querySelectorAll('.question-radio:checked').length;
        answeredSpan.textContent = answered;
        progressSpan.textContent = answered + '/' + totalQuestions;
    }

    // Navigation
    let currentQuestion = 0;
    const total = totalQuestions;

    function updateGridActive() {
        document.querySelectorAll('.question-nav').forEach(btn => {
            const index = parseInt(btn.dataset.index);
            if (index === currentQuestion) {
                btn.classList.remove('bg-muted', 'text-muted-foreground', 'hover:bg-muted');
                btn.classList.add('bg-navy-light', 'text-white');
            } else {
                btn.classList.remove('bg-navy-light', 'text-white');
                btn.classList.add('bg-muted', 'text-muted-foreground', 'hover:bg-muted');
            }
        });
    }

    window.goToQuestion = function(index) {
        if (index < 0 || index >= total) return;
        document.querySelectorAll('.question-item').forEach((el, i) => {
            el.classList.toggle('hidden', i !== index);
        });
        currentQuestion = index;
        updateNavButtons();
        updateGridActive();
        // Scroll to top of question
        document.querySelector('.question-item:not(.hidden)')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    window.nextQuestion = function() {
        if (currentQuestion < total - 1) goToQuestion(currentQuestion + 1);
    };

    window.prevQuestion = function() {
        if (currentQuestion > 0) goToQuestion(currentQuestion - 1);
    };

    function updateNavButtons() {
        document.querySelectorAll('.prev-btn, .next-btn').forEach(btn => {
            const isPrev = btn.classList.contains('prev-btn');
            if (isPrev) {
                btn.classList.toggle('opacity-50', currentQuestion === 0);
                btn.classList.toggle('cursor-not-allowed', currentQuestion === 0);
            } else {
                btn.classList.toggle('opacity-50', currentQuestion === total - 1);
                btn.classList.toggle('cursor-not-allowed', currentQuestion === total - 1);
            }
        });
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            nextQuestion();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            prevQuestion();
        }
    });

    // Form submission validation
    document.getElementById('practice-form').addEventListener('submit', function(e) {
        const answered = document.querySelectorAll('.question-radio:checked').length;
        if (answered < total) {
            const confirmSubmit = confirm(
                'Anda baru menjawab ' + answered + ' dari ' + total + ' soal.\n' +
                'Apakah Anda yakin ingin mengumpulkan?'
            );
            if (!confirmSubmit) e.preventDefault();
        }
    });

    // Initialize active question & progress
    updateGridActive();
    updateProgress();
});
</script>
@endpush
@endsection
