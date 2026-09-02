{{-- admin/videos/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Video')
@section('header-title', 'Edit Video Pembahasan')
@section('header-sub', '{{ $video->title }}')

@section('content')
<div class="space-y-6">
    <div class="admin-card stagger-item">
        <div class="px-5 py-4 border-b border-border flex items-center gap-3">
            <a href="{{ route('admin.videos.index') }}" class="w-9 h-9 rounded-md bg-muted text-muted-foreground hover:bg-muted transition-colors flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <h3 class="font-bold text-brand-900">Form Edit Video</h3>
                <p class="text-xs text-muted-foreground mt-0.5">Perbarui detail video pembahasan</p>
            </div>
        </div>

        <form action="{{ route('admin.videos.update', $video->id) }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-6">
            @csrf
            @method('PUT')

            <!-- Info Dasar -->
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-4">Informasi Video</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Judul Video <span class="text-danger-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $video->title) }}" placeholder="Contoh: Pembahasan Matematika - Bilangan Bulat" class="form-input" required>
                        @error('title')<p class="text-xs text-danger-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Deskripsi singkat isi video..." class="form-input resize-none">{{ old('description', $video->description) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Paket Terkait (Opsional)</label>
                        <select name="package_id" class="form-select">
                            <option value="">— Tanpa Paket —</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id', $video->package_id) == $package->id ? 'selected' : '' }}>{{ $package->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thumbnail</label>
                        @if($video->thumbnail)
                            <div class="flex items-center gap-3 mb-2">
                                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="Thumbnail" class="w-24 h-14 object-cover rounded-md ring-1 ring-border">
                                <span class="text-[11px] text-muted-foreground">Biarkan kosong jika tidak ingin mengganti</span>
                            </div>
                        @endif
                        <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp" class="form-input file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-accent-50 file:text-accent-400 hover:file:bg-accent-100">
                        <p class="text-[11px] text-muted-foreground mt-1">JPG, PNG, WEBP maksimal 3MB</p>
                    </div>
                </div>
            </div>

            <!-- Sumber Video -->
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-4">Sumber Video</h4>

                @if($video->video_file || $video->video_url)
                    <div class="mb-4 p-3 bg-accent-50/50 rounded-md flex items-center gap-3">
                        <svg class="w-5 h-5 text-accent-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>
                        <p class="text-xs text-muted-foreground">
                            Sumber saat ini:
                            @if($video->video_file)
                                <strong>📁 File Upload</strong> ({{ $video->video_url ? 'juga punya link' : 'biarkan kosong untuk tetap memakai' }})
                            @endif
                            @if($video->video_url)
                                <a href="{{ $video->video_url }}" target="_blank" rel="noopener" class="text-accent-400 font-semibold hover:underline break-all">{{ Illuminate\Support\Str::limit($video->video_url, 60) }}</a>
                            @endif
                        </p>
                    </div>
                @endif

                <!-- Tab Switcher -->
                <div class="grid grid-cols-2 gap-2 p-1.5 bg-muted rounded-md mb-4 max-w-md" id="sourceTabs">
                    <button type="button" data-tab="upload"
                            class="video-tab py-2.5 px-4 rounded-md text-sm font-semibold transition-all duration-200 {{ old('video_url') !== null && $video->video_file === null ? 'bg-transparent text-muted-foreground' : ($video->video_url && !$video->video_file ? 'bg-card text-brand-900 shadow-sm' : 'bg-card text-brand-900 shadow-sm') }}">
                        📁 Upload File
                    </button>
                    <button type="button" data-tab="link"
                            class="video-tab py-2.5 px-4 rounded-md text-sm font-semibold transition-all duration-200 {{ $video->video_url && !$video->video_file ? 'bg-card text-brand-900 shadow-sm' : 'bg-transparent text-muted-foreground' }}">
                        🔗 Link Video
                    </button>
                </div>

                <!-- Upload Panel -->
                <div id="panel-upload" class="video-panel {{ $video->video_url && !$video->video_file ? 'hidden' : '' }}">
                    <div class="border-2 border-dashed border-border rounded-lg p-6 sm:p-8 text-center hover:border-accent-400/50 hover:bg-accent-50/20 transition-all duration-300 cursor-pointer" id="dropZone">
                        <input type="file" name="video_file" id="videoFileInput" accept="video/mp4,video/avi,video/quicktime,video/x-ms-wmv" class="hidden">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-lg bg-accent-50 flex items-center justify-center">
                            <svg class="w-7 h-7 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-foreground" id="fileNameText">{{ $video->video_file ? 'Ganti file video (opsional)' : 'Klik atau seret file video ke sini' }}</p>
                        <p class="text-xs text-muted-foreground mt-1">MP4, AVI, MOV, WMV • Maksimal <strong>50MB</strong> (disarankan &lt;20MB agar ringan)</p>
                    </div>
                </div>

                <!-- Link Panel -->
                <div id="panel-link" class="video-panel {{ $video->video_url && !$video->video_file ? '' : 'hidden' }}">
                    <div class="form-group">
                        <label class="form-label">URL Video {{ $video->video_file ? '(opsional)' : '' }}</label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                            <input type="url" name="video_url" value="{{ old('video_url', $video->video_url) }}" placeholder="https://youtube.com/watch?v=... atau https://drive.google.com/..."
                                   class="form-input pl-10">
                        </div>
                        <p class="text-[11px] text-muted-foreground mt-1">Gunakan link YouTube, Google Drive, Vimeo, atau URL video langsung</p>
                    </div>
                </div>
                @error('video_file')<p class="text-xs text-danger-500 mt-2">{{ $message }}</p>@enderror
                @error('video_url')<p class="text-xs text-danger-500 mt-2">{{ $message }}</p>@enderror
            </div>

            <!-- Harga & Akses -->
            <div>
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-4">Harga & Akses</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Harga (Rp) <span class="text-danger-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $video->price) }}" min="0" step="1000" placeholder="50000" class="form-input" required>
                        @error('price')<p class="text-xs text-danger-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Durasi Akses (Hari) <span class="text-danger-500">*</span></label>
                        <input type="number" name="access_duration_days" value="{{ old('access_duration_days', $video->access_duration_days) }}" min="1" class="form-input" required>
                        <p class="text-[11px] text-muted-foreground mt-1">Berapa lama user bisa menonton setelah membeli</p>
                        @error('access_duration_days')<p class="text-xs text-danger-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipe Diskon</label>
                        <select name="discount_type" class="form-select" id="discountType">
                            <option value="">Tanpa Diskon</option>
                            <option value="percent" {{ old('discount_type', $video->discount_type) === 'percent' ? 'selected' : '' }}>Persen (%)</option>
                            <option value="nominal" {{ old('discount_type', $video->discount_type) === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nilai Diskon</label>
                        <input type="number" name="discount_value" value="{{ old('discount_value', $video->discount_value) }}" min="0" step="0.01" placeholder="0" class="form-input" id="discountValue" {{ !old('discount_type', $video->discount_type) ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center justify-between bg-muted rounded-md p-4">
                <div>
                    <p class="text-sm font-semibold text-foreground">Aktifkan Video</p>
                    <p class="text-xs text-muted-foreground mt-0.5">Video aktif akan tampil dan bisa dibeli oleh user</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active') !== null ? (old('is_active') !== '0' ? 'checked' : '') : ($video->is_active ? 'checked' : '') }}>
                    <span class="toggle-track"></span>
                    <span class="toggle-thumb"></span>
                </label>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-2 border-t border-border">
                <a href="{{ route('admin.videos.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Perbarui Video
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switcher
    const tabs = document.querySelectorAll('.video-tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(t => {
                t.classList.remove('bg-white', 'text-brand-900', 'shadow-sm');
                t.classList.add('bg-transparent', 'text-muted-foreground');
            });
            tab.classList.add('bg-white', 'text-brand-900', 'shadow-sm');
            tab.classList.remove('bg-transparent', 'text-muted-foreground');

            document.querySelectorAll('.video-panel').forEach(p => p.classList.add('hidden'));
            document.getElementById('panel-' + tab.dataset.tab).classList.remove('hidden');
        });
    });

    // File input display
    const videoFileInput = document.getElementById('videoFileInput');
    const fileNameText = document.getElementById('fileNameText');
    const dropZone = document.getElementById('dropZone');

    dropZone.addEventListener('click', function(e) {
        if (e.target !== videoFileInput) videoFileInput.click();
    });

    ['dragover', 'dragenter'].forEach(evt => {
        dropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            dropZone.classList.add('border-accent-400', 'bg-accent-50/30');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            dropZone.classList.remove('border-accent-400', 'bg-accent-50/30');
        });
    });

    dropZone.addEventListener('drop', function(e) {
        if (e.dataTransfer.files.length) {
            videoFileInput.files = e.dataTransfer.files;
            updateFileName();
        }
    });

    videoFileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (videoFileInput.files.length) {
            const file = videoFileInput.files[0];
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            fileNameText.innerHTML = '<span class="text-success-500">✓ ' + escapeHtml(file.name) + '</span><br><span class="text-xs font-normal text-muted-foreground">' + sizeMB + ' MB — klik untuk ganti</span>';
            if (file.size > 50 * 1024 * 1024) {
                fileNameText.innerHTML += '<br><span class="text-xs font-normal text-danger-500">⚠ Ukuran melebihi 50MB!</span>';
            }
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Discount toggle
    const discountType = document.getElementById('discountType');
    const discountValue = document.getElementById('discountValue');
    discountType.addEventListener('change', function() {
        discountValue.disabled = !this.value;
        if (!this.value) discountValue.value = '';
    });

    // Prevent double submit + show upload progress state
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (form.dataset.submitting === '1') {
            e.preventDefault();
            return;
        }
        form.dataset.submitting = '1';
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan... jangan tutup halaman';
    });
});
</script>
@endpush
