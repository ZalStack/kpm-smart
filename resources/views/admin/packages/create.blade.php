{{-- admin/packages/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Paket')
@section('header-title', '📚 Tambah Paket Baru')
@section('header-sub', 'Buat paket bank soal baru')

@section('content')
<div class="pb-10">
    <div class="admin-card overflow-hidden">
        <form action="{{ route('admin.packages.store') }}" method="POST" enctype="multipart/form-data" id="packageForm">
            @csrf

            <!-- Informasi Dasar -->
            <div class="p-4 sm:p-6 border-b border-border">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-navy to-navy-light flex items-center justify-center text-white text-sm">📄</div>
                    <h3 class="text-base font-bold text-navy">Informasi Dasar</h3>
                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary ml-auto">Wajib diisi</span>
                </div>
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Judul Paket <span class="text-danger-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required class="form-input" placeholder="Masukkan judul paket">
                        @error('title') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi <span class="text-danger-500">*</span></label>
                        <textarea name="description" rows="4" required class="form-input" placeholder="Deskripsi paket secara lengkap">{{ old('description') }}</textarea>
                        @error('description') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" value="{{ old('kelas') }}" class="form-input" placeholder="Contoh: 7, 8, 9">
                            @error('kelas') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bidang</label>
                            <input type="text" name="bidang" value="{{ old('bidang') }}" class="form-input" placeholder="Contoh: Teknik Informatika">
                            @error('bidang') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Level</label>
                            <input type="text" name="level" value="{{ old('level') }}" class="form-input" placeholder="Contoh: Pemula, Menengah, Lanjut">
                            @error('level') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp) <span class="text-danger-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price') }}" required min="0" step="1" class="form-input sm:w-72" placeholder="0">
                        @error('price') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thumbnail</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 w-full">
                                <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*" class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-md file:border-0 file:bg-primary/10 file:text-primary file:text-xs file:font-semibold file:cursor-pointer">
                                <p class="text-xs text-muted-foreground mt-1.5">Maksimal 2MB (JPG/PNG/WEBP)</p>
                            </div>
                            <div id="thumbnailPreview" class="hidden w-20 h-20 rounded-md border-2 border-dashed border-muted-foreground/30 overflow-hidden flex-shrink-0">
                                <img id="thumbnailPreviewImg" src="" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                        @error('thumbnail') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="inline-flex items-center gap-3 cursor-pointer p-3 bg-muted/50 border border-border rounded-md hover:bg-muted/50 transition">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-border text-primary focus:ring-primary focus:ring-2">
                            <span class="text-sm font-medium text-foreground">Aktifkan Paket</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Jadwal Pengerjaan -->
            <div class="p-4 sm:p-6 border-b border-border bg-muted/30">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white text-sm">📅</div>
                    <h3 class="text-base font-bold text-navy">Jadwal Pengerjaan</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-4">Tentukan kapan paket ini bisa dikerjakan oleh user. Kosongkan jika tanpa batasan waktu.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-input">
                        @error('start_date') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-input">
                        @error('end_date') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" class="form-input">
                        @error('start_time') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Berakhir</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" class="form-input">
                        @error('end_time') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Pengaturan Soal -->
            <div class="p-4 sm:p-6 border-b border-border">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-gold-400 to-[#f5a623] flex items-center justify-center text-white text-sm">⚙️</div>
                    <h3 class="text-base font-bold text-navy">Pengaturan Soal</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-4">Atur visibilitas kunci jawaban, pembahasan, dan skor untuk user.</p>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 bg-muted/50 rounded-md border border-border">
                        <div>
                            <label class="text-sm font-medium text-foreground cursor-pointer">Tampilkan Kunci Jawaban (Benar/Salah)</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user bisa melihat jawaban yang benar setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="show_answer_key" value="1" {{ old('show_answer_key') ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-muted/50 rounded-md border border-border">
                        <div>
                            <label class="text-sm font-medium text-foreground cursor-pointer">Tampilkan Pembahasan</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user bisa melihat pembahasan soal setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="show_explanation" value="1" {{ old('show_explanation', true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-muted/50 rounded-md border border-border">
                        <div>
                            <label class="text-sm font-medium text-foreground cursor-pointer">Tampilkan Skor / Nilai</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user bisa melihat skor/nilai setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="show_score" value="1" {{ old('show_score', true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 flex flex-col sm:flex-row sm:justify-end gap-3 bg-muted/30">
                <a href="{{ route('admin.packages.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary flex items-center justify-center gap-2">
                    <span>💾</span> Simpan Paket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('thumbnailInput');
    const preview = document.getElementById('thumbnailPreview');
    const previewImg = document.getElementById('thumbnailPreviewImg');
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
