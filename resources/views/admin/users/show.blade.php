{{-- admin/users/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail User')
@section('header-title', 'Detail User')
@section('header-sub', $user->name)

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.users.index') }}" class="text-primary hover:text-brand-800 text-sm inline-flex items-center gap-2 transition-colors group">
        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke Daftar Pengguna
    </a>

    <!-- Profile Header -->
    <div class="admin-card overflow-hidden">
        <div class="h-32 md:h-40 bg-gradient-to-r from-brand-950 via-brand-900 to-primary relative">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 40%, rgba(255,255,255,.6) 0, transparent 40%), radial-gradient(circle at 80% 70%, rgba(255,255,255,.5) 0, transparent 35%);"></div>
            <div class="absolute bottom-0 left-4 md:left-8 pb-1 flex items-end gap-4">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                     class="w-24 h-24 md:w-32 md:h-32 rounded-lg object-cover border-4 border-card shadow-xl -translate-y-6 md:-translate-y-8">
            </div>
        </div>
        <div class="pt-14 md:pt-18 px-4 md:px-8 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="min-w-0">
                    <div class="flex items-center flex-wrap gap-2">
                        <h2 class="text-xl md:text-2xl font-bold text-brand-900">{{ $user->name }}</h2>
                        @if($user->is_active)
                            <span class="badge-success">
                                <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span> Aktif
                            </span>
                        @else
                            <span class="badge-neutral">
                                <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground"></span> Nonaktif
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-muted-foreground mt-0.5">{{ $user->email }}</p>
                </div>
                <div class="sm:ml-auto flex items-center gap-2">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-primary inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        Edit
                    </a>
                    <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                        @csrf
                        <button type="submit" class="{{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                            @if($user->is_active)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Nonaktifkan
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Aktifkan
                            @endif
                        </button>
                    </form>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus user {{ $user->name }}? Semua data terkait akan dihapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Terdaftar sejak {{ $user->created_at->format('d M Y') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left column -->
        <div class="space-y-4">
            <div class="admin-card p-5 md:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                    Data Siswa
                </h3>
                <div class="space-y-3.5 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Kelas</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->student_class ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Bidang</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->bidang ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Level</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->level ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Sekolah</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->school_name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="admin-card p-5 md:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    Info Kontak
                </h3>
                <div class="space-y-3.5 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">No. HP/WA</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Jenis Kelamin</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->gender ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Agama</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->religion ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Alamat</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="admin-card p-5 md:p-6">
            <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                Info Tambahan
            </h3>
            <div class="space-y-3.5 text-sm">
                <div class="flex items-start justify-between gap-3">
                    <span class="form-label shrink-0 mb-0">Role</span>
                    <span class="text-brand-900 font-medium text-right">{{ ucfirst($user->role) }}</span>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <span class="form-label shrink-0 mb-0">Status</span>
                    @if($user->is_active)
                        <span class="badge-success">Aktif</span>
                    @else
                        <span class="badge-neutral">Nonaktif</span>
                    @endif
                </div>
                <div class="flex items-start justify-between gap-3">
                    <span class="form-label shrink-0 mb-0">Terdaftar Sejak</span>
                    <span class="text-brand-900 font-medium text-right">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <span class="form-label shrink-0 mb-0">Terakhir Diperbarui</span>
                    <span class="text-brand-900 font-medium text-right">{{ $user->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection