@extends('layouts.admin')

@section('title', 'Detail Bantuan - Admin')

@section('header-title', 'Detail Tiket Bantuan')
@section('header-sub', '#' . $ticket->id . ' - ' . ($ticket->name ?? 'Anonim'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.support.index') }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke daftar
        </a>
        @if(!$ticket->answer)
            <button type="button" onclick="updateStatus('closed')" class="btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Tutup Tiket
            </button>
        @endif
    </div>

    <div class="admin-card overflow-hidden">
        <!-- Header -->
        <div class="p-5 md:p-6 border-b border-border flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-brand-900">#{{ $ticket->id }}</h2>
                    @php
                        $statusClasses = [
                            'pending' => 'badge-warning',
                            'answered' => 'badge-success',
                            'closed' => 'badge-neutral',
                        ];
                    @endphp
                    <span class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
                <div class="text-sm text-muted-foreground mt-1.5">
                    Dibuat: {{ $ticket->created_at->format('d/m/Y H:i') }}
                    @if($ticket->answered_at)
                        &bull; Dijawab: {{ $ticket->answered_at->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
            <form action="{{ route('admin.support.delete', $ticket->id) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus tiket ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Hapus
                </button>
            </form>
        </div>

        <!-- User Info -->
        <div class="p-5 md:p-6 border-b border-border bg-muted/70">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="stagger-item">
                    <p class="form-label">Nama</p>
                    <p class="font-medium text-foreground mt-0.5">{{ $ticket->name ?? 'Anonim' }}</p>
                </div>
                <div class="stagger-item">
                    <p class="form-label">Email</p>
                    <p class="font-medium text-foreground mt-0.5 break-all">{{ $ticket->email ?? '-' }}</p>
                </div>
                <div class="stagger-item">
                    <p class="form-label">Tipe Pengguna</p>
                    <p class="font-medium text-foreground mt-0.5">
                        @if($ticket->user)
                            <span class="badge-info">Member</span>
                        @else
                            <span class="text-muted-foreground">Pengunjung</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Question -->
        <div class="p-5 md:p-6 border-b border-border">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-md bg-gradient-to-br from-brand-900 to-brand-800 text-white flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="form-label">Pertanyaan</p>
                    <div class="text-foreground whitespace-pre-wrap">{{ $ticket->question }}</div>
                </div>
            </div>
        </div>

        <!-- Answer Section -->
        <div class="p-5 md:p-6">
            @if($ticket->answer)
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-md bg-gradient-to-br from-success-500 to-success-600 text-white flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="form-label">Jawaban</p>
                        <div class="text-foreground whitespace-pre-wrap">{{ $ticket->answer }}</div>
                        @if($ticket->answered_at)
                            <div class="text-xs text-muted-foreground mt-2">
                                Dijawab pada {{ $ticket->answered_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="w-12 h-12 mx-auto bg-accent-50 rounded-md flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    </div>
                    <p class="text-muted-foreground text-sm mb-4">Belum ada jawaban untuk tiket ini</p>
                    <form action="{{ route('admin.support.answer', $ticket->id) }}" method="POST">
                        @csrf
                        <div class="mb-4 text-left">
                            <label class="form-label">Tulis Jawaban</label>
                            <textarea name="answer" rows="6"
                                class="form-input w-full"
                                placeholder="Tulis jawaban untuk pengguna..." required></textarea>
                            @error('answer')
                                <p class="text-danger-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="text-left">
                            <button type="submit" class="btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                Kirim Jawaban
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Status Update (for answered tickets) -->
        @if($ticket->answer)
        <div class="p-5 md:p-6 border-t border-border bg-muted/70 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <label for="statusSelect" class="form-label mb-0">Update Status:</label>
                <select id="statusSelect" class="form-select">
                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="answered" {{ $ticket->status == 'answered' ? 'selected' : '' }}>Dijawab</option>
                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Ditutup</option>
                </select>
            </div>
            <button onclick="updateStatus(document.getElementById('statusSelect').value)" class="btn-primary">
                Update Status
            </button>
        </div>
        @endif
    </div>
</div>

<form id="statusForm" method="POST" action="{{ route('admin.support.update-status', $ticket->id) }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="status" id="statusInput">
</form>

@push('scripts')
<script>
function updateStatus(status) {
    if (confirm('Yakin ingin mengubah status tiket ini menjadi ' + status + '?')) {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusForm').submit();
    }
}
</script>
@endpush
@endsection
