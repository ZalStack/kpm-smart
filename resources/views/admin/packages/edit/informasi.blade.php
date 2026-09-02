{{-- admin/packages/edit/informasi.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Informasi - ' . $package->title)
@section('header-title', '📋 Edit Informasi Paket')
@section('header-sub', 'Perbarui data paket ' . $package->title)

@section('content')
<div class="space-y-6 pb-10">
    {{-- Package Header --}}
    <div class="bg-gradient-to-r from-navy via-[#1a2070] to-navy-light rounded-lg overflow-hidden shadow-lg shadow-navy/20 mb-6">
        <div class="relative px-4 sm:px-6 py-4 sm:py-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    @if($package->thumbnail)
                        <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="Thumbnail" class="w-16 h-16 rounded-lg object-cover ring-4 ring-white/20 shadow-xl flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-lg bg-white/10 ring-4 ring-white/20 flex items-center justify-center text-3xl flex-shrink-0 shadow-xl">📚</div>
                    @endif
                    <div>
                        <h1 class="text-xl font-bold text-white">{{ $package->title }}</h1>
                        <p class="text-sm text-white/70 line-clamp-1">{{ $package->description }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 sm:ml-auto">
                    <a href="{{ route('admin.packages.edit.informasi', $package) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/20 text-white text-sm font-semibold transition backdrop-blur ring-2 ring-white/30">📄 Info</a>
                    <a href="{{ route('admin.packages.edit.cards', $package) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">📋 Card</a>
                    <a href="{{ route('admin.packages.edit.questions', $package) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">📝 Soal</a>
                    <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">← Kembali</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $totalCards = count($package->cards ?? []);
        $totalQuestions = count($package->questions ?? []);
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="admin-card p-4 text-center">
            <p class="text-2xl font-bold text-foreground">{{ $totalCards }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Card</p>
        </div>
        <div class="admin-card p-4 text-center">
            <p class="text-2xl font-bold text-navy-light">{{ $totalQuestions }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Soal</p>
        </div>
        <div class="admin-card p-4 text-center">
            <p class="text-2xl font-bold text-success-500">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Status</p>
        </div>
        <div class="admin-card p-4 text-center">
            <p class="text-2xl font-bold text-gold-400">{{ $package->schedule_status === 'active' ? 'Berjalan' : ($package->schedule_status === 'upcoming' ? 'Akan Datang' : ($package->schedule_status === 'expired' ? 'Berakhir' : 'Tanpa Batas')) }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Jadwal</p>
        </div>
    </div>

    {{-- Form Edit --}}
    <div class="admin-card overflow-hidden">
        <form action="{{ route('admin.packages.update', $package) }}" method="POST" enctype="multipart/form-data" id="packageForm">
            @csrf
            @method('PUT')

            <!-- Informasi Dasar -->
            <div class="p-4 sm:p-6 border-b border-border">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-navy to-navy-light flex items-center justify-center text-white text-sm">📄</div>
                    <h3 class="text-base font-bold text-foreground">Informasi Dasar</h3>
                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary ml-auto">Wajib diisi</span>
                </div>
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Judul Paket <span class="text-danger-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $package->title) }}" required class="form-input" placeholder="Masukkan judul paket">
                        @error('title') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi <span class="text-danger-500">*</span></label>
                        <textarea name="description" rows="4" required class="form-input" placeholder="Deskripsi paket secara lengkap">{{ old('description', $package->description) }}</textarea>
                        @error('description') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" value="{{ old('kelas', $package->kelas) }}" class="form-input" placeholder="Contoh: 7, 8, 9">
                            @error('kelas') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bidang</label>
                            <input type="text" name="bidang" value="{{ old('bidang', $package->bidang) }}" class="form-input" placeholder="Contoh: Teknik Informatika">
                            @error('bidang') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Level</label>
                            <input type="text" name="level" value="{{ old('level', $package->level) }}" class="form-input" placeholder="Contoh: Pemula, Menengah, Lanjut">
                            @error('level') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp) <span class="text-danger-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $package->price) }}" required min="0" step="1" class="form-input sm:w-72" placeholder="0">
                        @error('price') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thumbnail</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 w-full">
                                @if($package->thumbnail)
                                    <div id="currentThumbnail" class="mb-3">
                                        <p class="text-xs text-muted-foreground mb-2">Thumbnail saat ini:</p>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="Current Thumbnail" class="w-16 h-16 rounded-md object-cover border border-border shadow-sm">
                                            <button type="button" id="removeCurrentThumb" class="text-xs font-medium text-danger-500 hover:underline flex items-center gap-1">🗑️ Ganti gambar</button>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*" class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:text-xs file:font-semibold file:cursor-pointer">
                                <p class="text-xs text-muted-foreground mt-1.5">Maksimal 2MB (JPG/PNG/WEBP)</p>
                            </div>
                            <div id="thumbnailPreview" class="hidden w-20 h-20 rounded-md border-2 border-dashed border-border overflow-hidden flex-shrink-0">
                                <img id="thumbnailPreviewImg" src="" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                        @error('thumbnail') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="inline-flex items-center gap-3 cursor-pointer p-3 bg-muted/50 border border-border rounded-md hover:bg-muted/50 transition">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-border text-primary focus:ring-primary focus:ring-2">
                            <span class="text-sm font-medium text-muted-foreground">Aktifkan Paket</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Jadwal Pengerjaan -->
            <div class="p-4 sm:p-6 border-b border-border bg-muted/30">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white text-sm">📅</div>
                    <h3 class="text-base font-bold text-foreground">Jadwal Pengerjaan</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-4">Tentukan kapan paket ini bisa dikerjakan oleh user. Kosongkan jika tanpa batasan waktu.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $package->start_date?->format('Y-m-d')) }}" class="form-input">
                        @error('start_date') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $package->end_date?->format('Y-m-d')) }}" class="form-input">
                        @error('end_date') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $package->start_time ? substr($package->start_time, 0, 5) : '') }}" class="form-input">
                        @error('start_time') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Berakhir</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $package->end_time ? substr($package->end_time, 0, 5) : '') }}" class="form-input">
                        @error('end_time') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Pengaturan Soal -->
            <div class="p-4 sm:p-6 border-b border-border">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-gold-400 to-[#f5a623] flex items-center justify-center text-white text-sm">⚙️</div>
                    <h3 class="text-base font-bold text-foreground">Pengaturan Soal</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-4">Atur visibilitas kunci jawaban, pembahasan, dan skor untuk user.</p>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 bg-muted/50 rounded-md border border-border">
                        <div>
                            <label class="text-sm font-medium text-foreground cursor-pointer">Tampilkan Kunci Jawaban (Benar/Salah)</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user bisa melihat jawaban yang benar setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="show_answer_key" value="1" {{ old('show_answer_key', $package->show_answer_key) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-muted/50 rounded-md border border-border">
                        <div>
                            <label class="text-sm font-medium text-foreground cursor-pointer">Tampilkan Pembahasan</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user bisa melihat pembahasan soal setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="show_explanation" value="1" {{ old('show_explanation', $package->show_explanation) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-muted/50 rounded-md border border-border">
                        <div>
                            <label class="text-sm font-medium text-foreground cursor-pointer">Tampilkan Skor / Nilai</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user bisa melihat skor/nilai setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="show_score" value="1" {{ old('show_score', $package->show_score) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 flex flex-col sm:flex-row sm:justify-end gap-3 bg-muted/30">
                <a href="{{ route('admin.packages.edit.informasi', $package) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary flex items-center justify-center gap-2">
                    <span>💾</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="fixed bottom-4 right-4 bg-success-500 text-white px-6 py-3 rounded-md shadow-lg z-50 animate-slideIn">
        {{ session('success') }}
    </div>
    <style>
        @keyframes slideIn {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-slideIn { animation: slideIn 0.3s ease-out; }
    </style>
@endif

@if($errors->any())
    <div class="fixed bottom-4 right-4 bg-danger-500 text-white px-6 py-3 rounded-md shadow-lg z-50 animate-slideIn">
        {{ $errors->first() }}
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('thumbnailInput');
    const preview = document.getElementById('thumbnailPreview');
    const previewImg = document.getElementById('thumbnailPreviewImg');
    const removeBtn = document.getElementById('removeCurrentThumb');
    const currentThumb = document.getElementById('currentThumbnail');

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (currentThumb) currentThumb.classList.add('hidden');
        });
    }

    if (input) {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2 * 1024 * 1024) {
                    alert('⚠️ Ukuran thumbnail terlalu besar (maks 2MB).');
                    this.value = '';
                    preview.classList.add('hidden');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.classList.remove('hidden');
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection
