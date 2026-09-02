@extends('layouts.admin')

@php
    $isEdit = isset($question);
@endphp

@section('title', ($isEdit ? 'Edit Soal' : 'Tambah Soal') . ' - ' . $package->title)
@section('header-title', $isEdit ? 'Edit Soal' : 'Tambah Soal Baru')
@section('header-sub', 'Paket: ' . $package->title)

@section('content')
<div class="space-y-6 pb-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-6">
        <a href="{{ route('admin.packages.index') }}" class="hover:text-primary transition-colors">Paket</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <a href="{{ route('admin.packages.edit.questions', $package) }}" class="hover:text-primary transition-colors">Soal</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <span class="text-foreground font-medium">{{ $isEdit ? 'Edit' : 'Tambah' }}</span>
    </div>

    {{-- Form Card --}}
    <div class="admin-card bg-card rounded-lg shadow-sm border border-border/80 overflow-hidden anim-fade-in-up">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-navy-light to-primary px-5 sm:px-6 py-4 sm:py-5">
            <h2 class="text-white font-bold text-base sm:text-lg flex items-center gap-2">
                @if($isEdit)
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                    Edit Soal
                @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Soal Baru
                @endif
            </h2>
            <p class="text-white/70 text-xs mt-1">{{ $isEdit ? 'Perbarui data soal yang sudah ada' : 'Isi data soal baru untuk paket ini' }}</p>
        </div>

        {{-- Form --}}
        <form id="questionForm" method="POST"
              action="{{ $isEdit ? route('admin.packages.update-question', ['package' => $package->id, 'questionId' => $question['id']]) : route('admin.packages.add-question', $package->id) }}"
              enctype="multipart/form-data" class="p-5 sm:p-6 space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="correct_answer" id="qf_correct" value="{{ $question['correct_answer'] ?? '' }}">

            {{-- Card --}}
            <div class="form-group">
                <label class="form-label">Card <span class="text-danger-500">*</span></label>
                <select name="card_id" required class="form-select">
                    <option value="">Pilih Card</option>
                    @foreach($cards as $card)
                        <option value="{{ $card['id'] }}" {{ ($question['card_id'] ?? '') === $card['id'] ? 'selected' : '' }}>{{ $card['title'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Soal --}}
            <div class="form-group">
                <label class="form-label">Soal <span class="text-danger-500">*</span></label>
                <textarea name="question" rows="4" required
                    placeholder="Tulis soal di sini. Gunakan $...$ untuk rumus LaTeX"
                    class="form-input">{{ $question['question'] ?? '' }}</textarea>
                <p class="text-[11px] text-muted-foreground mt-1.5">Rumus matematika/fisika/kimia dengan <code class="bg-muted px-1.5 py-0.5 rounded">$...$</code> atau tabel <code class="bg-muted px-1.5 py-0.5 rounded">| ... |</code></p>
            </div>

            {{-- Gambar Soal --}}
            <div class="form-group">
                <label class="form-label">Gambar Soal <span class="text-muted-foreground font-normal">(Opsional)</span></label>
                <input type="file" name="image" accept="image/*"
                    class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:text-xs file:font-semibold file:cursor-pointer">
                <p class="text-xs text-muted-foreground mt-1.5">Maksimal 3MB (JPG/PNG/WEBP)</p>
                @if($isEdit && !empty($question['image']))
                    <div id="qf_image_meta" class="mt-3 flex items-center gap-3">
                        <img src="{{ \App\Support\QuestionFormatter::imageUrl($question['image']) }}" class="h-16 object-contain rounded-md border border-border" alt="Gambar soal">
                        <label class="flex items-center gap-2 text-xs font-medium text-danger-500 cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="rounded border-border text-danger-500 focus:ring-danger-500">
                            Hapus gambar
                        </label>
                    </div>
                @endif
            </div>

            {{-- Pilihan Jawaban --}}
            <div class="form-group">
                <div class="flex items-center justify-between mb-2">
                    <label class="form-label mb-0">Pilihan Jawaban <span class="text-danger-500">*</span></label>
                    <span class="text-[11px] text-muted-foreground">Centang radio untuk kunci jawaban</span>
                </div>
                <div id="optionList" class="space-y-2"></div>
                <button type="button" id="addOptionBtn"
                    class="mt-2 text-xs font-semibold text-primary hover:text-navy-light transition px-4 py-2 rounded-md border-2 border-dashed border-primary/40 hover:border-primary">
                    + Tambah Pilihan
                </button>
            </div>

            {{-- Pembahasan --}}
            <div class="form-group">
                <label class="form-label">Pembahasan <span class="text-muted-foreground font-normal">(Opsional)</span></label>
                <textarea name="explanation" rows="3" placeholder="Pembahasan atau penjelasan jawaban"
                    class="form-input">{{ $question['explanation'] ?? '' }}</textarea>
            </div>

            {{-- Buttons --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5 border-t border-border">
                <a href="{{ route('admin.packages.edit.questions', $package) }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-card hover:bg-muted text-muted-foreground font-semibold rounded-md border border-border hover:border-border hover:shadow-md transition-all duration-200 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Batal
                </a>
                <button type="submit" id="submitBtn"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-navy-light to-primary text-white font-semibold rounded-md shadow-lg shadow-navy-light/20 hover:shadow-xl hover:shadow-navy-light/30 transition-all duration-200 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ $isEdit ? 'Update Soal' : 'Simpan Soal' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const optionList = document.getElementById('optionList');
    let optionSeq = 0;
    const existingOptions = {!! json_encode($question['options'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    const existingCorrect = {!! json_encode($question['correct_answer'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

    function escapeAttr(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function buildOptionRow(value, isCorrect) {
        const idx = optionSeq++;
        const div = document.createElement('div');
        div.className = 'option-row flex items-center gap-3 group';
        div.innerHTML =
            '<input type="radio" name="correct_radio" value="' + idx + '" ' + (isCorrect ? 'checked' : '') +
            ' class="correct-radio w-4 h-4 flex-shrink-0 text-primary focus:ring-primary cursor-pointer">' +
            '<input type="text" name="options[]" value="' + escapeAttr(value) +
            '" placeholder="Isi pilihan jawaban..." ' +
            'class="w-full px-4 py-2.5 border border-border rounded-md focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none text-sm bg-muted/50 hover:bg-card focus:bg-card">' +
            '<button type="button" class="remove-option w-8 h-8 flex-shrink-0 rounded-md bg-muted hover:bg-danger-50 hover:text-danger-500 text-muted-foreground flex items-center justify-center transition" title="Hapus pilihan">' +
            '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
        div.querySelector('.remove-option').addEventListener('click', function() {
            div.remove();
            renumberOptions();
        });
        return div;
    }

    function renumberOptions() {
        document.querySelectorAll('#optionList .option-row').forEach(function(row, i) {
            row.querySelector('.correct-radio').value = i;
        });
    }

    if (existingOptions.length > 0) {
        existingOptions.forEach(function(opt) {
            optionList.appendChild(buildOptionRow(opt, opt === existingCorrect && opt !== ''));
        });
        if (!(existingOptions.some(function(o) { return o === existingCorrect && o !== ''; }))) {
            const firstRadio = optionList.querySelector('.correct-radio');
            if (firstRadio) firstRadio.checked = true;
        }
    } else {
        for (let i = 0; i < 4; i++) {
            optionList.appendChild(buildOptionRow('', i === 0));
        }
    }
    renumberOptions();

    document.getElementById('addOptionBtn').addEventListener('click', function() {
        optionList.appendChild(buildOptionRow('', false));
    });

    document.getElementById('questionForm').addEventListener('submit', function(e) {
        const checked = this.querySelector('input[name="correct_radio"]:checked');
        if (!checked) {
            e.preventDefault();
            alert('Pilih salah satu pilihan sebagai jawaban benar (radio).');
            return;
        }
        const options = [];
        this.querySelectorAll('.option-row input[name="options[]"]').forEach(function(input) {
            options.push(input.value.trim());
        });
        const filled = options.filter(function(o) { return o !== ''; });
        if (filled.length < 2) {
            e.preventDefault();
            alert('Minimal 2 pilihan jawaban harus diisi.');
            return;
        }
        if (options.some(function(o) { return o === ''; })) {
            e.preventDefault();
            alert('Semua pilihan jawaban harus diisi. Hapus pilihan kosong atau lengkapi isinya.');
            return;
        }
        const selectedIndex = parseInt(checked.value, 10);
        document.getElementById('qf_correct').value = options[selectedIndex] || '';
    });
});
</script>
@endpush
