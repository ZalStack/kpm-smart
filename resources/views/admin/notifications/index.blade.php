@extends('layouts.admin')

@section('title', 'Notifikasi - Admin')
@section('header-title', 'Notifikasi')
@section('header-sub', 'Semua notifikasi terbaru')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-foreground font-display">Semua Notifikasi</h2>
            <p class="text-sm text-muted-foreground mt-0.5">Menampilkan {{ $notifications->total() }} notifikasi</p>
        </div>
        <button onclick="markAllRead()" id="markAllBtn"
            class="px-4 py-2 rounded-md text-sm font-semibold bg-navy text-white hover:bg-navy-light transition-all duration-200 shadow-md shadow-navy/15 active:scale-95">
            Tandai Semua Dibaca
        </button>
    </div>

    {{-- Notification List --}}
    <div class="space-y-3" id="notifList">
        @forelse($notifications as $notif)
            <div class="notif-card group bg-card rounded-lg border {{ $notif->isRead() ? 'border-border' : 'border-accent-400/30 bg-accent-400/[0.02]' }} p-4 sm:p-5 transition-all duration-200 hover:shadow-lg hover:shadow-navy/5 hover:border-border"
                 data-id="{{ $notif->id }}" data-read="{{ $notif->isRead() ? '1' : '0' }}">
                <div class="flex items-start gap-3 sm:gap-4">
                    {{-- Icon --}}
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-md flex items-center justify-center flex-shrink-0
                        @if($notif->type === 'order') bg-gold-400/10
                        @elseif($notif->type === 'testimonial') bg-accent-400/10
                        @elseif($notif->type === 'support') bg-danger-500/10
                        @elseif($notif->type === 'enroll') bg-success-500/10
                        @elseif($notif->type === 'video') bg-pink-500/10
                        @else bg-muted
                        @endif">
                        @if($notif->type === 'order')
                            <svg class="w-5 h-5 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        @elseif($notif->type === 'testimonial')
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                        @elseif($notif->type === 'support')
                            <svg class="w-5 h-5 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                        @elseif($notif->type === 'enroll')
                            <svg class="w-5 h-5 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                        @elseif($notif->type === 'video')
                            <svg class="w-5 h-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>
                        @else
                            <svg class="w-5 h-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-foreground leading-tight {{ $notif->isRead() ? 'text-muted-foreground' : '' }}">
                                {{ $notif->title }}
                            </h3>
                            @if(!$notif->isRead())
                                <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0 mt-1.5"></span>
                            @endif
                        </div>
                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed">{{ $notif->message }}</p>
                        <p class="text-xs text-muted-foreground mt-2">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-card rounded-lg border border-border">
                <div class="w-16 h-16 rounded-lg bg-muted flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                </div>
                <h3 class="text-muted-foreground font-semibold">Belum ada notifikasi</h3>
                <p class="text-sm text-muted-foreground mt-1">Notifikasi akan muncul di sini</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark individual as read on click
    document.querySelectorAll('.notif-card').forEach(card => {
        card.addEventListener('click', function() {
            if (this.dataset.read === '0') {
                const id = this.dataset.id;
                fetch(`/admin/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                }).then(r => r.json()).then(data => {
                    this.dataset.read = '1';
                    this.classList.remove('border-accent-400/30', 'bg-accent-400/[0.02]');
                    this.classList.add('border-border');
                    const dot = this.querySelector('span.bg-primary');
                    if (dot) dot.remove();
                    updateSidebarBadge(data.unread_count);
                });
            }
        });
    });
});

function markAllRead() {
    const btn = document.getElementById('markAllBtn');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    fetch('{{ route("admin.notifications.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        document.querySelectorAll('.notif-card').forEach(card => {
            card.dataset.read = '1';
            card.classList.remove('border-accent-400/30', 'bg-accent-400/[0.02]');
            card.classList.add('border-border');
            const dot = card.querySelector('span.bg-primary');
            if (dot) dot.remove();
        });
        updateSidebarBadge(0);
        btn.textContent = 'Semua Sudah Dibaca';
    });
}

function updateSidebarBadge(count) {
    // Update header notification badge
    const headerBadge = document.querySelector('#notifBtn span');
    if (headerBadge) {
        if (count > 0) {
            headerBadge.textContent = count;
        } else {
            headerBadge.parentElement?.classList.add('hidden');
        }
    }
    // Update sidebar badges could be done via broadcast or reload
}
</script>
@endpush
@endsection
