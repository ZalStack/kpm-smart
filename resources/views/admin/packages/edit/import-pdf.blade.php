@extends('layouts.admin')

@section('title', 'Import Soal dari PDF - ' . $package->title)
@section('header-title', 'Import Soal dari PDF')
@section('header-sub', 'Paket: ' . $package->title)

@section('content')
<div class="space-y-6 pb-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-6">
        <a href="{{ route('admin.packages.index') }}" class="hover:text-primary transition-colors">Paket</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <a href="{{ route('admin.packages.edit.questions', $package) }}" class="hover:text-primary transition-colors">Soal</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <span class="text-foreground font-medium">Import PDF</span>
    </div>

    {{-- Form Card --}}
    <div class="admin-card bg-card rounded-lg shadow-sm border border-border/80 overflow-hidden anim-fade-in-up">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-success-500 to-success-600 px-5 sm:px-6 py-4 sm:py-5">
            <h2 class="text-white font-bold text-base sm:text-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Import Soal dari PDF
            </h2>
            <p class="text-white/70 text-xs mt-1">Upload file PDF untuk menambahkan soal secara otomatis</p>
        </div>

        {{-- Format Info --}}
        <div class="mx-5 sm:mx-6 mt-5 bg-primary/5 border border-primary/20 rounded-md p-4">
            <p class="font-semibold text-navy-light text-sm mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                Format PDF yang Didukung
            </p>
            <ul class="space-y-1.5 text-xs text-muted-foreground leading-relaxed">
                <li class="flex items-start gap-2">
                    <span class="text-success-500 mt-0.5 flex-shrink-0">&#10003;</span>
                    <span>Setiap soal diawali nomor + titik, contoh: <code class="bg-card px-1.5 py-0.5 rounded text-[11px]">1. Ibu kota Indonesia adalah...</code></span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-success-500 mt-0.5 flex-shrink-0">&#10003;</span>
                    <span>Pilihan jawaban di baris terpisah diawali huruf A-E + titik</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-success-500 mt-0.5 flex-shrink-0">&#10003;</span>
                    <span>Rumus LaTeX <code class="bg-card px-1.5 py-0.5 rounded text-[11px]">$...$</code> &amp; tabel <code class="bg-card px-1.5 py-0.5 rounded text-[11px]">|...|</code> otomatis dikenali</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-success-500 mt-0.5 flex-shrink-0">&#10003;</span>
                    <span>Gambar dalam PDF <strong>otomatis diekstrak</strong> dan ditempel ke soal</span>
                </li>
            </ul>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.packages.import-pdf', $package->id) }}" method="POST"
              enctype="multipart/form-data" class="p-5 sm:p-6 space-y-5">
            @csrf

            {{-- Card --}}
            <div class="form-group">
                <label class="form-label">Card <span class="text-danger-500">*</span></label>
                <select name="card_id" required class="form-select">
                    <option value="">Pilih Card tujuan soal</option>
                    @foreach($cards as $card)
                        <option value="{{ $card['id'] }}">{{ $card['title'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- PDF Soal --}}
            <div class="form-group">
                <label class="form-label">File PDF Soal <span class="text-danger-500">*</span></label>
                <input type="file" name="pdf_file" accept=".pdf" required
                    class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-success-500/10 file:text-success-500 file:text-xs file:font-semibold file:cursor-pointer">
                <p class="text-xs text-muted-foreground mt-1.5">Maksimal 2MB. Format PDF dengan soal berurutan.</p>
            </div>

            {{-- Kunci Jawaban --}}
            <div class="form-group">
                <label class="form-label">File PDF Kunci Jawaban <span class="text-muted-foreground font-normal">(Opsional)</span></label>
                <input type="file" name="answer_key_pdf" accept=".pdf"
                    class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-success-500/10 file:text-success-500 file:text-xs file:font-semibold file:cursor-pointer">
                <p class="text-xs text-muted-foreground mt-1.5">PDF berisi kunci jawaban (opsional).</p>
            </div>

            {{-- ZIP Gambar --}}
            <div class="form-group">
                <label class="form-label">ZIP Gambar <span class="text-muted-foreground font-normal">(Opsional)</span></label>
                <input type="file" name="images_zip" accept=".zip"
                    class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-success-500/10 file:text-success-500 file:text-xs file:font-semibold file:cursor-pointer">
                <p class="text-xs text-muted-foreground mt-1.5">Gambar direferensikan dengan <code class="bg-muted px-1.5 py-0.5 rounded">[GAMBAR:nama_file]</code>. Maksimal 20MB.</p>
            </div>

            {{-- Buttons --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5 border-t border-border">
                <a href="{{ route('admin.packages.edit.questions', $package) }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-card hover:bg-muted text-muted-foreground font-semibold rounded-md border border-border hover:border-border hover:shadow-md transition-all duration-200 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-success-500 to-success-600 text-white font-semibold rounded-md shadow-lg shadow-success-500/20 hover:shadow-xl hover:shadow-success-500/30 transition-all duration-200 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Import &amp; Generate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function bindSizeCheck(input, maxBytes) {
        if (!input) return;
        input.addEventListener('change', function() {
            if (this.files && this.files[0] && this.files[0].size > maxBytes) {
                alert('Ukuran file terlalu besar (maks ' + Math.round(maxBytes / 1024 / 1024) + 'MB).');
                this.value = '';
            }
        });
    }
    document.querySelectorAll('input[name="pdf_file"], input[name="answer_key_pdf"]').forEach(function(el) {
        bindSizeCheck(el, 2 * 1024 * 1024);
    });
    document.querySelectorAll('input[name="images_zip"]').forEach(function(el) {
        bindSizeCheck(el, 20 * 1024 * 1024);
    });
});
</script>
@endpush
