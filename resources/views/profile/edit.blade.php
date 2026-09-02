@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<style>
    .profile-stagger > * {
        animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .profile-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .profile-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .profile-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .profile-stagger > *:nth-child(4) { animation-delay: 180ms; }
    .profile-stagger > *:nth-child(5) { animation-delay: 240ms; }
</style>

<div class="max-w-3xl mx-auto space-y-6 profile-stagger">
    <div>
        <span class="inline-block px-3 py-1 rounded-full bg-navy-light/10 text-navy-light text-xs font-semibold mb-2">👤 Profil</span>
        <h1 class="text-2xl md:text-3xl font-bold text-foreground">Profil Saya</h1>
        <p class="text-muted-foreground mt-1">Kelola informasi akun Anda</p>
    </div>

    @if(session('success'))
        <div class="bg-success-50 border border-success-200 text-success-600 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-card rounded-lg p-6 sm:p-8 shadow-sm border border-border">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Foto Profil -->
            <div class="flex flex-col items-center gap-3">
                <div class="relative group">
                    <img id="photoPreview" src="{{ $user->profile_photo_url }}" alt="Foto profil"
                         class="w-28 h-28 rounded-lg object-cover border-4 border-border shadow-md group-hover:border-primary/30 transition-all duration-300">
                    <label for="profile_photo"
                           class="absolute bottom-0 right-0 bg-navy-light text-white w-8 h-8 rounded-lg flex items-center justify-center cursor-pointer shadow-md hover:bg-navy hover:scale-110 transition-all duration-200"
                           title="Ganti foto">
                        📷
                    </label>
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="hidden">
                </div>
                <p class="text-xs text-muted-foreground">Maks. 2MB (JPG/PNG/WEBP)</p>
                @error('profile_photo')
                    <p class="text-xs text-danger-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Akun -->
            <div>
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wide mb-3">🔐 Data Akun</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">
                        @error('name') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Email</label>
                        <input type="email" value="{{ $user->email }}" disabled
                               class="w-full px-4 py-2.5 border border-border rounded-md bg-muted text-muted-foreground">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">No. HP/WA *</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                               class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">
                        @error('phone') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Data Siswa -->
            <div class="pt-6 border-t border-border">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wide mb-3">🎓 Data Siswa</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Nama Siswa *</label>
                        <input type="text" name="student_name" value="{{ old('student_name', $user->student_name) }}" required
                               class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">
                        @error('student_name') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Kelas *</label>
                        <input type="text" name="student_class" value="{{ old('student_class', $user->student_class) }}" required
                               class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">
                        @error('student_class') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Jurusan *</label>
                        <input type="text" name="student_major" value="{{ old('student_major', $user->student_major) }}" required
                               class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">
                        @error('student_major') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Sekolah -->
            <div class="pt-6 border-t border-border">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wide mb-3">🏫 Sekolah</h2>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Nama Sekolah *</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $user->school_name) }}" required
                           class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">
                    @error('school_name') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Info Tambahan -->
            <div class="pt-6 border-t border-border">
                <h2 class="text-sm font-bold text-foreground uppercase tracking-wide mb-3">ℹ️ Info Tambahan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Jenis Kelamin</label>
                        <select name="gender" class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200 appearance-none cursor-pointer">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" {{ old('gender', $user->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender', $user->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Agama</label>
                        <select name="religion" class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200 appearance-none cursor-pointer">
                            <option value="">Pilih</option>
                            <option value="Islam" {{ old('religion', $user->religion) === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('religion', $user->religion) === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('religion', $user->religion) === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('religion', $user->religion) === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('religion', $user->religion) === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('religion', $user->religion) === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Alamat Lengkap</label>
                        <textarea name="address" rows="2"
                                  class="w-full px-4 py-2.5 border border-border rounded-md bg-muted hover:bg-card focus:bg-card focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all duration-200">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-navy-light to-navy text-white py-2.5 px-8 rounded-2xl font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    Simpan Perubahan 💾
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profile_photo');
    const preview = document.getElementById('photoPreview');
    const MAX_MB = 2;

    if (input) {
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            if (file.size > MAX_MB * 1024 * 1024) {
                alert('⚠️ Ukuran foto terlalu besar (maks ' + MAX_MB + 'MB).');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = e => preview.src = e.target.result;
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush
@endsection
