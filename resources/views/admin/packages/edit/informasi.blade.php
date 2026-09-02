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
                        <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="Thumbnail"
                             class="w-16 h-16 rounded-lg object-cover ring-4 ring-white/20 shadow-xl flex-shrink-0">
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
            <p class="text-2xl font-bold text-gold-400">{{ $package->time_limit_label }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Batas Waktu</p>
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
                        <input type="text" name="title" value="{{ old('title', $package->title) }}" required
                               class="form-input"
                               placeholder="Masukkan judul paket">
                        @error('title') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi <span class="text-danger-500">*</span></label>
                        <textarea name="description" rows="4" required
                                  class="form-input"
                                  placeholder="Deskripsi paket secara lengkap">{{ old('description', $package->description) }}</textarea>
                        @error('description') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" value="{{ old('kelas', $package->kelas) }}"
                                   class="form-input"
                                   placeholder="Contoh: 7, 8, 9, atau Semua Kelas">
                            <p class="text-xs text-muted-foreground mt-1.5">Kelas yang dituju (opsional)</p>
                            @error('kelas') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenjang</label>
                            <input type="text" name="jenjang" value="{{ old('jenjang', $package->jenjang) }}"
                                   class="form-input"
                                   placeholder="Contoh: SMP, SMA, Umum">
                            <p class="text-xs text-muted-foreground mt-1.5">Jenjang pendidikan (opsional)</p>
                            @error('jenjang') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thumbnail</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 w-full">
                                @if($package->thumbnail)
                                    <div id="currentThumbnail" class="mb-3">
                                        <p class="text-xs text-muted-foreground mb-2">Thumbnail saat ini:</p>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="Current Thumbnail"
                                                 class="w-16 h-16 rounded-md object-cover border border-border shadow-sm">
                                            <button type="button" id="removeCurrentThumb"
                                                    class="text-xs font-medium text-danger-500 hover:underline flex items-center gap-1">
                                                🗑️ Ganti gambar
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*"
                                       class="form-input file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:text-xs file:font-semibold file:cursor-pointer">
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
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $package->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-border text-primary focus:ring-primary focus:ring-2">
                            <span class="text-sm font-medium text-muted-foreground">Aktifkan Paket</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Harga & Diskon -->
            <div class="p-4 sm:p-6 border-b border-border bg-muted/30">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-danger-500 to-[#ff6b6b] flex items-center justify-center text-white text-sm">💰</div>
                    <h3 class="text-base font-bold text-foreground">Harga &amp; Diskon</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Harga Normal (Rp) <span class="text-danger-500">*</span></label>
                        <input type="number" id="priceInput" name="price" value="{{ old('price', $package->price) }}" required min="0" step="1"
                               class="form-input"
                               placeholder="0">
                        @error('price') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Setelah Diskon (Rp)</label>
                        <input type="number" id="discountPriceInput" name="discount_price" value="{{ old('discount_price', $package->discount_price) }}" min="0" step="1" disabled
                               class="form-input disabled:bg-muted disabled:text-muted-foreground cursor-not-allowed"
                               placeholder="0">
                        @error('discount_price') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4 p-4 bg-card rounded-md border border-border">
                    <div>
                        <label for="discountToggle" class="text-sm font-medium text-muted-foreground cursor-pointer">🏷️ Aktifkan Diskon</label>
                        <p class="text-xs text-muted-foreground mt-0.5">Tampilkan harga coret &amp; badge potongan harga ke user</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" id="discountToggle" name="is_discount_active" value="1"
                               {{ old('is_discount_active', $package->is_discount_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-danger-500 transition-colors duration-300"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                    </label>
                </div>

                <div id="discountPreview" class="hidden mt-3 text-xs md:text-sm text-success-500 font-medium bg-success-50 border border-success-200 rounded-md px-4 py-2.5"></div>
            </div>

            <!-- Masa Berlaku Membership -->
            <div class="p-4 sm:p-6 border-b border-border">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-primary to-[#4dd0e1] flex items-center justify-center text-white text-sm">⏳</div>
                    <h3 class="text-base font-bold text-foreground">Masa Berlaku Membership</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-4">Tentukan berapa lama user dapat mengakses paket ini setelah pembelian / perpanjangan berhasil.</p>

                <div class="flex flex-wrap gap-2 mb-4" id="durationPresets">
                    @foreach([30 => '30 Hari', 90 => '90 Hari', 180 => '180 Hari', 365 => '1 Tahun'] as $val => $label)
                        <button type="button" data-value="{{ $val }}"
                                class="duration-preset-btn px-4 py-2 text-sm rounded-md border-2 border-border text-muted-foreground hover:border-primary hover:text-primary transition-all font-medium">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="sm:w-72">
                    <div class="form-group">
                        <label class="form-label">Durasi (Hari) <span class="text-danger-500">*</span></label>
                        <input type="number" id="membershipDurationInput" name="membership_duration_days"
                               value="{{ old('membership_duration_days', $package->membership_duration_days ?? 30) }}" required min="1" step="1"
                               class="form-input">
                        <p class="text-xs text-muted-foreground mt-1.5">Masa aktif akan dihitung otomatis mulai tanggal pembayaran berhasil.</p>
                        @error('membership_duration_days') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Pengaturan Latihan -->
            <div class="p-4 sm:p-6 border-b border-border bg-muted/30">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-gold-400 to-[#f5a623] flex items-center justify-center text-white text-sm">📝</div>
                    <h3 class="text-base font-bold text-foreground">Pengaturan Latihan</h3>
                </div>
                <p class="text-sm text-muted-foreground mb-4">Atur batas waktu pengerjaan dan visibilitas pembahasan saat user mengerjakan soal.</p>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-card rounded-md border border-border">
                        <div>
                            <label for="hideExplanationToggle" class="text-sm font-medium text-muted-foreground cursor-pointer">Sembunyikan Pembahasan</label>
                            <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, user tidak akan melihat pembahasan setelah mengerjakan soal</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" id="hideExplanationToggle" name="hide_explanation" value="1"
                                   {{ old('hide_explanation', $package->hide_explanation) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-danger-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Batas Waktu Pengerjaan (Menit)</label>
                        <div class="flex flex-wrap gap-2 mb-3" id="timeLimitPresets">
                            @foreach([0 => 'Tanpa Batas', 30 => '30 Menit', 60 => '1 Jam', 90 => '1.5 Jam', 120 => '2 Jam'] as $val => $label)
                                <button type="button" data-value="{{ $val }}"
                                        class="time-limit-preset-btn px-4 py-2 text-sm rounded-md border-2 border-border text-muted-foreground hover:border-gold-400 hover:text-gold-400 transition-all font-medium">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <input type="number" id="timeLimitInput" name="time_limit_minutes"
                               value="{{ old('time_limit_minutes', $package->time_limit_minutes ?? 0) }}" min="0" max="480" step="1"
                               class="form-input sm:w-72"
                               placeholder="0 = Tanpa Batas">
                        <p class="text-xs text-muted-foreground mt-1.5">Isi 0 atau kosongkan untuk tanpa batas waktu. Maksimal 480 menit (8 jam).</p>
                        @error('time_limit_minutes') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Bayar Seikhlasnya -->
            <div class="p-4 sm:p-6 border-b border-border bg-muted/30">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-8 h-8 rounded-md bg-gradient-to-br from-success-500 to-[#00c853] flex items-center justify-center text-white text-sm">💝</div>
                    <h3 class="text-base font-bold text-foreground">Bayar Seikhlasnya</h3>
                </div>

                <div class="flex items-center justify-between p-4 bg-card rounded-md border border-border">
                    <div>
                        <label for="pwywToggle" class="text-sm font-medium text-muted-foreground cursor-pointer">Izinkan User Bayar Nominal Sendiri</label>
                        <p class="text-xs text-muted-foreground mt-0.5">Jika aktif, harga normal/diskon di atas tidak dipakai — user memasukkan nominal sendiri saat checkout</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" id="pwywToggle" name="is_pay_what_you_want" value="1"
                               {{ old('is_pay_what_you_want', $package->is_pay_what_you_want) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                    </label>
                </div>

                <div id="pwywFields" class="mt-4 hidden">
                    <div class="form-group">
                        <label class="form-label">Nominal Minimum (Rp)</label>
                        <input type="number" name="min_pay_amount" value="{{ old('min_pay_amount', $package->min_pay_amount ?? 0) }}" min="0" step="1"
                               class="form-input sm:w-64"
                               placeholder="0">
                        <p class="text-xs text-muted-foreground mt-1.5">Kosongkan / isi 0 jika tidak ada batas minimum</p>
                        @error('min_pay_amount') <p class="text-xs text-danger-500 mt-1.5">{{ $message }}</p> @enderror
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
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
    </style>
@endif

@if($errors->any())
    <div class="fixed bottom-4 right-4 bg-danger-500 text-white px-6 py-3 rounded-md shadow-lg z-50 animate-slideIn">
        {{ $errors->first() }}
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thumbnail
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

    // Price & Discount
    const priceInput = document.getElementById('priceInput');
    const discountToggle = document.getElementById('discountToggle');
    const discountPriceInput = document.getElementById('discountPriceInput');
    const discountPreview = document.getElementById('discountPreview');
    const pwywToggle = document.getElementById('pwywToggle');
    const pwywFields = document.getElementById('pwywFields');
    const durationInput = document.getElementById('membershipDurationInput');
    const durationPresetBtns = document.querySelectorAll('.duration-preset-btn');

    function syncDurationPresetHighlight() {
        durationPresetBtns.forEach(btn => {
            const active = String(btn.dataset.value) === String(durationInput.value);
            btn.classList.toggle('border-primary', active);
            btn.classList.toggle('bg-primary/10', active);
            btn.classList.toggle('text-primary', active);
            btn.classList.toggle('font-semibold', active);
            btn.classList.toggle('border-border', !active);
            btn.classList.toggle('text-muted-foreground', !active);
        });
    }

    durationPresetBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            durationInput.value = this.dataset.value;
            syncDurationPresetHighlight();
        });
    });
    durationInput.addEventListener('input', syncDurationPresetHighlight);
    syncDurationPresetHighlight();

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function updateDiscountPreview() {
        if (!discountToggle.checked) {
            discountPriceInput.disabled = true;
            discountPreview.classList.add('hidden');
            return;
        }
        discountPriceInput.disabled = false;

        const price = parseFloat(priceInput.value) || 0;
        const discountPrice = parseFloat(discountPriceInput.value) || 0;

        if (price > 0 && discountPrice > 0 && discountPrice < price) {
            const percent = Math.round(((price - discountPrice) / price) * 100);
            discountPreview.innerHTML = '✅ Hemat <strong>' + percent + '%</strong> — dari ' + formatRupiah(price) + ' menjadi ' + formatRupiah(discountPrice);
            discountPreview.classList.remove('hidden');
        } else {
            discountPreview.classList.add('hidden');
        }
    }

    function updatePwywFields() {
        pwywFields.classList.toggle('hidden', !pwywToggle.checked);
    }

    discountToggle.addEventListener('change', updateDiscountPreview);
    priceInput.addEventListener('input', updateDiscountPreview);
    discountPriceInput.addEventListener('input', updateDiscountPreview);
    pwywToggle.addEventListener('change', updatePwywFields);

    updateDiscountPreview();
    updatePwywFields();

    // Time Limit Presets
    const timeLimitInput = document.getElementById('timeLimitInput');
    const timeLimitPresetBtns = document.querySelectorAll('.time-limit-preset-btn');

    function syncTimeLimitPresetHighlight() {
        timeLimitPresetBtns.forEach(btn => {
            const active = String(btn.dataset.value) === String(timeLimitInput.value);
            btn.classList.toggle('border-gold-400', active);
            btn.classList.toggle('bg-gold-400/10', active);
            btn.classList.toggle('text-gold-400', active);
            btn.classList.toggle('font-semibold', active);
            btn.classList.toggle('border-border', !active);
            btn.classList.toggle('text-muted-foreground', !active);
        });
    }

    timeLimitPresetBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            timeLimitInput.value = this.dataset.value;
            syncTimeLimitPresetHighlight();
        });
    });
    timeLimitInput.addEventListener('input', syncTimeLimitPresetHighlight);
    syncTimeLimitPresetHighlight();
});
</script>
@endsection
