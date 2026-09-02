{{-- admin/packages/edit/questions.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Soal - ' . $package->title)
@section('header-title', '📝 Kelola Bank Soal')
@section('header-sub', 'Atur soal dan kunci jawaban untuk paket ' . $package->title)

@section('content')
    @php
        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($allQuestions);
    @endphp

    <div class="space-y-6 pb-10">
        {{-- Package Header --}}
        <div
            class="bg-gradient-to-r from-navy via-[#1a2070] to-navy-light rounded-lg overflow-hidden shadow-lg shadow-navy/20 mb-6">
            <div class="relative px-4 sm:px-6 py-4 sm:py-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        @if ($package->thumbnail)
                            <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="Thumbnail"
                                class="w-16 h-16 rounded-lg object-cover ring-4 ring-white/20 shadow-xl flex-shrink-0">
                        @else
                            <div
                                class="w-16 h-16 rounded-lg bg-white/10 ring-4 ring-white/20 flex items-center justify-center text-3xl flex-shrink-0 shadow-xl">
                                📚</div>
                        @endif
                        <div>
                            <h1 class="text-xl font-bold text-white">{{ $package->title }}</h1>
                            <p class="text-sm text-white/70 line-clamp-1">{{ $package->description }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:ml-auto">
                        <a href="{{ route('admin.packages.edit.informasi', $package) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">📄
                            Info</a>
                        <a href="{{ route('admin.packages.edit.cards', $package) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">📋
                            Card</a>
                        <a href="{{ route('admin.packages.edit.questions', $package) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/20 text-white text-sm font-semibold transition backdrop-blur ring-2 ring-white/30">📝
                            Soal</a>
                        <a href="{{ route('admin.packages.index') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">←
                            Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="admin-card p-4 text-center">
                <p class="text-2xl font-bold text-foreground">{{ $totalQuestions }}</p>
                <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Soal</p>
            </div>
            <div class="admin-card p-4 text-center">
                <p class="text-2xl font-bold text-navy-light">{{ $totalCards }}</p>
                <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Card</p>
            </div>
            <div class="admin-card p-4 text-center col-span-2 sm:col-span-1">
                <p class="text-2xl font-bold text-gold-400">
                    {{ $totalCards > 0 ? round($totalQuestions / $totalCards, 1) : 0 }}</p>
                <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Rata-rata/Card</p>
            </div>
            <div class="admin-card p-4 text-center">
                <p class="text-2xl font-bold text-primary">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Status Paket</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="admin-card overflow-hidden mb-6">
            <div
                class="p-4 border-b border-border bg-muted/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-bold text-foreground flex items-center gap-2">📝 Bank Soal</h3>
                    <span class="text-xs text-muted-foreground">{{ $totalQuestions }} soal</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($totalCards > 0)
                        <a href="{{ route('admin.packages.show-import', $package) }}"
                            class="px-4 py-2 bg-success-500/10 text-success-500 text-sm font-semibold rounded-md hover:bg-success-500/20 transition inline-flex items-center gap-1.5">
                            📄 Import PDF</a>
                        <a href="{{ route('admin.packages.create-question', $package) }}"
                            class="px-4 py-2 bg-gradient-to-r from-navy-light to-primary text-white text-sm font-semibold rounded-md hover:shadow-lg transition inline-flex items-center gap-1.5">
                            ➕ Tambah Soal</a>
                    @else
                        <span class="text-sm text-muted-foreground bg-muted px-4 py-2 rounded-md border border-border">⚠️ Tambah
                            card terlebih dahulu</span>
                    @endif
                </div>
            </div>

            {{-- Filters --}}
            <div class="p-4 border-b border-border">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">🔍</span>
                        <input type="text" id="questionSearch" placeholder="Cari soal atau pilihan jawaban..."
                            class="form-input pl-9 pr-4">
                    </div>
                    <select id="questionCardFilter"
                        class="form-select sm:w-48">
                        <option value="">📊 Semua Card</option>
                        @foreach ($package->cards ?? [] as $card)
                            <option value="{{ $card['id'] }}">{{ $card['title'] }}
                                ({{ isset($questionsByCard[$card['id']]) ? count($questionsByCard[$card['id']]) : 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Bagian Question List yang diperbaiki --}}
        <div id="questionList" class="space-y-4">
            @if ($totalCards === 0)
                <div class="admin-card border-2 border-dashed border-border p-6 md:p-8 text-center">
                    <div class="text-5xl mb-3 opacity-50">📋</div>
                    <h3 class="text-lg font-semibold text-muted-foreground">Belum Ada Card</h3>
                    <p class="text-sm text-muted-foreground mt-1">Buat card terlebih dahulu sebelum menambahkan soal</p>
                    <a href="{{ route('admin.packages.edit.cards', $package) }}"
                        class="btn-success mt-4">📋
                        Kelola Card</a>
                </div>
            @elseif(empty($allQuestions))
                <div class="admin-card border-2 border-dashed border-border p-6 md:p-8 text-center">
                    <div class="text-5xl mb-3 opacity-50">📝</div>
                    <h3 class="text-lg font-semibold text-muted-foreground">Belum Ada Soal</h3>
                    <p class="text-sm text-muted-foreground mt-1">Tambahkan soal manual atau import dari PDF</p>
                    <div class="flex flex-wrap justify-center gap-2 mt-4">
                        <a href="{{ route('admin.packages.show-import', $package) }}"
                            class="px-5 py-2.5 bg-success-500/10 text-success-500 text-sm font-semibold rounded-md hover:bg-success-500/20 transition inline-flex items-center gap-1.5">
                            📄 Import PDF</a>
                        <a href="{{ route('admin.packages.create-question', $package) }}"
                            class="px-5 py-2.5 bg-gradient-to-r from-navy-light to-primary text-white text-sm font-semibold rounded-md hover:shadow-lg transition inline-flex items-center gap-1.5">
                            ➕ Tambah Soal</a>
                    </div>
                </div>
            @else
                @foreach ($allQuestions as $index => $question)
                    @php
                        $qOptions = $question['options'] ?? [];
                        $qImage = $question['image'] ?? null;
                        $qCard = $cardsMap[$question['card_id']] ?? null;
                        $qCreated = isset($question['created_at'])
                            ? \Carbon\Carbon::parse($question['created_at'])
                            : null;
                        $correctAnswer = $question['correct_answer'] ?? '';
                    @endphp
                    <div class="question-card admin-card hover:border-navy-light/40 hover:shadow-md transition-all duration-300 overflow-hidden"
                        data-card-id="{{ $question['card_id'] }}"
                        data-search="{{ mb_strtolower(($question['question'] ?? '') . ' ' . implode(' ', $qOptions)) }}">

                        {{-- Card Header --}}
                        <div
                            class="flex items-center justify-between gap-3 px-4 py-3 bg-muted/80 border-b border-border">
                            <div class="flex items-center gap-3 min-w-0">
                                <span
                                    class="flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-br from-navy to-navy-light text-white text-xs font-bold flex-shrink-0">#{{ $index + 1 }}</span>
                                @if ($qCard)
                                    <span
                                        class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-gold-400/20 text-foreground whitespace-nowrap">{{ $qCard['title'] }}</span>
                                @endif
                                @if (!empty($question['imported_from_pdf']))
                                    <span
                                        class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary whitespace-nowrap">📄
                                        Import</span>
                                @endif
                                @if ($qCreated)
                                    <span
                                        class="text-[10px] text-muted-foreground hidden sm:inline">{{ $qCreated->translatedFormat('d M Y H:i') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button type="button" data-preview-id="{{ $question['id'] }}"
                                    class="preview-trigger w-8 h-8 rounded-md bg-primary/10 hover:bg-primary/20 text-primary flex items-center justify-center transition-all hover:scale-110"
                                    title="Preview">👁️</button>
                                <a href="{{ route('admin.packages.edit-question', ['package' => $package->id, 'questionId' => $question['id']]) }}"
                                    class="w-8 h-8 rounded-md bg-muted hover:bg-navy-light/10 text-muted-foreground hover:text-navy-light flex items-center justify-center transition-all hover:scale-110"
                                    title="Edit">✏️</a>
                                <form
                                    action="{{ route('admin.packages.remove-question', ['package' => $package->id, 'questionId' => $question['id']]) }}"
                                    method="POST" onsubmit="return confirm('Hapus Soal #{{ $index + 1 }} permanen?')"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-md bg-muted hover:bg-danger-50 text-muted-foreground hover:text-danger-500 flex items-center justify-center transition-all hover:scale-110"
                                        title="Hapus">🗑️</button>
                                </form>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-4">
                            {{-- Soal --}}
                            <div class="text-sm text-foreground leading-relaxed mb-3 line-clamp-3">
                                {!! \App\Support\QuestionFormatter::render($question['question']) !!}
                            </div>

                            {{-- Gambar --}}
                            @if ($qImage)
                                <img src="{{ \App\Support\QuestionFormatter::imageUrl($qImage) }}" alt="Gambar soal"
                                    class="mb-3 h-12 object-contain rounded-md border border-border"
                                    onerror="this.style.display='none'">
                            @endif

                            {{-- Pilihan Jawaban --}}
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($qOptions as $option)
                                    <span
                                        class="text-[11px] px-3 py-1.5 rounded-md border
                                {{ $option === $correctAnswer ? 'bg-success-500/10 border-success-500/40 text-success-500 font-semibold' : 'bg-muted border-border text-muted-foreground' }}">
                                        {{ $option }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div id="questionEmptyState"
            class="hidden admin-card border-2 border-dashed border-border p-6 md:p-8 text-center">
            <div class="text-4xl mb-2 opacity-50">🔍</div>
            <p class="text-sm text-muted-foreground">Tidak ada soal yang cocok dengan pencarian.</p>
        </div>
    </div>

    {{-- ===================== HIDDEN PREVIEW TEMPLATES ===================== --}}
    @foreach ($allQuestions as $index => $question)
        <div id="preview-template-{{ $question['id'] }}" class="hidden">
            @include('admin.packages._question-preview', [
                'question' => $question,
                'index' => $index,
                'card' => $cardsMap[$question['card_id']] ?? null,
            ])
        </div>
    @endforeach

    {{-- ===================== MODAL: PREVIEW ===================== --}}
    <div id="previewModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closePreviewModal()"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="bg-card rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col animate-scaleIn">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-border bg-gradient-to-r from-navy to-navy-light rounded-t-lg flex-shrink-0">
                    <h3 class="text-white font-bold text-sm flex items-center gap-2">👁️ Preview Soal</h3>
                    <button onclick="closePreviewModal()" class="w-8 h-8 rounded-md bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">✕</button>
                </div>
                <div id="previewContent" class="p-4 sm:p-6 overflow-y-auto flex-1" style="max-height: calc(90vh - 80px);"></div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-scaleIn {
            animation: scaleIn 0.2s ease-out;
        }

        /* Perbaikan untuk preview soal */
        #previewContent .preview-question {
            max-width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        #previewContent .preview-question img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 0.5rem;
        }

        #previewContent .preview-question .option-label img {
            max-width: 200px;
            max-height: 120px;
            object-fit: contain;
            display: inline-block;
            vertical-align: middle;
            border-radius: 0.375rem;
            margin: 0.25rem 0;
        }

        #previewContent .preview-question table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.75rem 0;
        }

        #previewContent .preview-question table td,
        #previewContent .preview-question table th {
            border: 1px solid #d1d5db;
            padding: 0.5rem 0.75rem;
            text-align: left;
        }

        #previewContent .preview-question table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        #previewContent .preview-question .table-wrapper {
            overflow-x: auto;
            margin: 0.75rem 0;
        }

        /* Perbaikan untuk konten LaTeX */
        #previewContent .katex-display {
            margin: 0.5rem 0;
            overflow-x: auto;
            overflow-y: hidden;
        }

        #previewContent .katex {
            font-size: 1.05em;
        }

        /* Responsive untuk preview */
        @media (max-width: 640px) {
            #previewContent .preview-question .flex.items-start.gap-3 {
                flex-direction: column;
                align-items: stretch;
            }

            #previewContent .preview-question .flex.items-center.gap-3.w-full {
                flex-wrap: wrap;
            }

            #previewContent .preview-question .p-3 {
                padding: 0.75rem;
            }

            #previewContent .preview-question .text-base {
                font-size: 0.95rem;
            }
        }

        /* Perbaikan untuk line clamp */
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Responsive untuk card soal */
        @media (max-width: 640px) {
            .question-card .flex.items-center.justify-between {
                flex-wrap: wrap;
                gap: 8px;
            }

            .question-card .flex.items-center.gap-3.min-w-0 {
                flex-wrap: wrap;
            }

            .question-card .flex.items-center.gap-1\.5.flex-shrink-0 {
                margin-left: auto;
            }
        }
    </style>

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/contrib/auto-render.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.renderMathInElement) {
                renderMathInElement(document.body, {
                    delimiters: [{
                        left: '$$',
                        right: '$$',
                        display: true
                    }, {
                        left: '$',
                        right: '$',
                        display: false
                    }],
                    throwOnError: false
                });
            }

            function unlockScroll() {
                document.body.style.overflow = '';
            }

            /* ===================== Preview ===================== */
            window.openPreviewModal = function(id) {
                const template = document.getElementById('preview-template-' + id);
                const content = document.getElementById('previewContent');
                if (!template || !content) return;

                // Clone content to avoid modifying the template
                content.innerHTML = template.innerHTML;

                // Render LaTeX if available
                if (window.renderMathInElement) {
                    renderMathInElement(content, {
                        delimiters: [{
                                left: '$$',
                                right: '$$',
                                display: true
                            },
                            {
                                left: '$',
                                right: '$',
                                display: false
                            }
                        ],
                        throwOnError: false
                    });
                }
                document.getElementById('previewModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };
            window.closePreviewModal = function() {
                document.getElementById('previewModal').classList.add('hidden');
                unlockScroll();
            };

            document.querySelectorAll('.preview-trigger').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openPreviewModal(this.dataset.previewId);
                });
            });

            /* ===================== Search & filter soal ===================== */
            const searchInput = document.getElementById('questionSearch');
            const cardFilter = document.getElementById('questionCardFilter');

            function filterQuestions() {
                const query = (searchInput.value || '').trim().toLowerCase();
                const card = cardFilter.value;
                let visible = 0;
                document.querySelectorAll('.question-card').forEach(function(cardEl) {
                    const matchCard = !card || cardEl.dataset.cardId === card;
                    const matchSearch = !query || cardEl.dataset.search.includes(query);
                    const show = matchCard && matchSearch;
                    cardEl.classList.toggle('hidden', !show);
                    if (show) visible++;
                });
                document.getElementById('questionEmptyState').classList.toggle('hidden', visible > 0);
            }

            searchInput.addEventListener('input', filterQuestions);
            cardFilter.addEventListener('change', filterQuestions);
        });
    </script>
@endpush
