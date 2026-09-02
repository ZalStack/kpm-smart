{{-- admin/users/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah User Baru')
@section('header-title', 'Tambah User')
@section('header-sub', 'Buat akun user baru')

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.users.index') }}" class="text-primary hover:text-brand-800 text-sm inline-flex items-center gap-2 transition-colors group">
        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke Daftar Pengguna
    </a>

    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="admin-card p-5 md:p-6 space-y-6">
        @csrf

        {{-- Foto Profil --}}
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center overflow-hidden border-2 border-border flex-shrink-0" id="photoPreview">
                <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            </div>
            <div>
                <label class="block text-xs font-bold text-brand-900 uppercase tracking-wide mb-1">Foto Profil</label>
                <input type="file" name="profile_photo" accept="image/*" onchange="previewPhoto(this)"
                       class="text-sm text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer">
                @error('profile_photo') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="border-t border-border"></div>

        {{-- Info Akun --}}
        <div>
            <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                Info Akun
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
                    @error('name') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email <span class="text-danger-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input">
                    @error('email') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Password <span class="text-danger-500">*</span></label>
                    <input type="password" name="password" required minlength="8" class="form-input">
                    @error('password') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password <span class="text-danger-500">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="8" class="form-input">
                </div>
            </div>
        </div>

        <div class="border-t border-border"></div>

        {{-- Data Siswa --}}
        <div>
            <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                Data Siswa
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Kelas</label>
                    <input type="text" name="student_class" value="{{ old('student_class') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Bidang</label>
                    <input type="text" name="bidang" value="{{ old('bidang') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Level</label>
                    <input type="text" name="level" value="{{ old('level') }}" class="form-input" placeholder="Contoh: Pemula, Menengah, Lanjut">
                </div>
                <div>
                    <label class="form-label">Sekolah</label>
                    <input type="text" name="school_name" value="{{ old('school_name') }}" class="form-input">
                </div>
            </div>
        </div>

        <div class="border-t border-border"></div>

        {{-- Info Kontak --}}
        <div>
            <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                Info Kontak
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">No. HP/WA</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Agama</label>
                    <select name="religion" class="form-select">
                        <option value="">Pilih</option>
                        <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ old('religion') === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ old('religion') === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('religion') === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('religion') === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ old('religion') === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" rows="2" class="form-input">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t border-border">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Simpan User
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
