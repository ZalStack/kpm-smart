@extends('layouts.admin')

@section('title', 'Kelola Testimoni - Admin')
@section('header-title', 'Kelola Testimoni')
@section('header-sub', 'Kelola testimoni dari pengguna')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Total</p>
                    <p class="text-xl font-bold text-brand-900">{{ $stats['total'] }}</p>
                </div>
                <span class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Disetujui</p>
                    <p class="text-xl font-bold text-success-500">{{ $stats['approved'] }}</p>
                </div>
                <span class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Menunggu</p>
                    <p class="text-xl font-bold text-warning-500">{{ $stats['pending'] }}</p>
                </div>
                <span class="stat-icon bg-gradient-to-br from-warning-500 to-warning-600 text-white shadow-lg shadow-warning-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Rating</p>
                    <p class="text-xl font-bold text-primary">{{ number_format($stats['avg_rating'], 1) }}</p>
                </div>
                <span class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="admin-card p-3 sm:p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <button id="bulkDeleteBtn" class="btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Hapus Terpilih
                </button>
                <a href="{{ route('admin.testimonials.export') }}" class="btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </a>
            </div>
            <div class="text-sm text-muted-foreground">Menampilkan {{ $testimonials->firstItem() ?? 0 }} - {{ $testimonials->lastItem() ?? 0 }} dari {{ $testimonials->total() }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="px-4 py-3 text-center w-12">
                            <input type="checkbox" id="selectAll" class="rounded border-border text-primary focus:ring-accent-400">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider hidden sm:table-cell">Testimoni</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider hidden md:table-cell">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-muted-foreground uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($testimonials as $testimonial)
                    <tr class="hover:bg-muted/60 transition-colors duration-150">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" class="testimonial-checkbox rounded border-border text-primary focus:ring-accent-400" value="{{ $testimonial->id }}">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-900 to-brand-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                                    {{ strtoupper(substr($testimonial->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-brand-900 text-sm">{{ $testimonial->user->name ?? 'User' }}</p>
                                    <p class="text-xs text-muted-foreground hidden sm:block">{{ $testimonial->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <p class="text-muted-foreground text-sm max-w-xs line-clamp-2">{{ $testimonial->content }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-warning-500 text-sm tracking-wide" data-rating="{{ $testimonial->rating }}">
                                @for($i=1; $i<=5; $i++) @if($i<=$testimonial->rating) ★ @else ☆ @endif @endfor
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($testimonial->is_approved)
                                @if($testimonial->is_active)
                                    <span class="badge-success">Aktif</span>
                                @else
                                    <span class="badge-neutral">Nonaktif</span>
                                @endif
                            @else
                                <span class="badge-warning">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                @if(!$testimonial->is_approved)
                                    <button data-id="{{ $testimonial->id }}" class="approve-btn btn-success px-2 py-1.5" title="Setujui">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </button>
                                @else
                                    <button data-id="{{ $testimonial->id }}" class="toggle-btn w-8 h-8 rounded-md {{ $testimonial->is_active ? 'btn-success' : 'btn-secondary' }} flex items-center justify-center hover:opacity-80 transition hover:scale-105" title="Toggle">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </button>
                                @endif
                                <button data-id="{{ $testimonial->id }}" class="delete-btn btn-danger px-2 py-1.5" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="empty-state">
                                <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                </div>
                                <p class="text-lg font-semibold text-muted-foreground">Belum ada testimoni</p>
                                <p class="text-sm text-muted-foreground mt-1">Testimoni dari pengguna akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($testimonials->hasPages())
            <div class="p-4 border-t border-border">{{ $testimonials->links() }}</div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.testimonial-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Hapus testimoni ini?')) return;
            fetch(`/admin/testimonials/${this.dataset.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
        });
    });

    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Setujui testimoni ini?')) return;
            fetch(`/admin/testimonials/${this.dataset.id}/approve`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
        });
    });

    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            fetch(`/admin/testimonials/${this.dataset.id}/toggle-active`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
        });
    });

    document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
        const selected = document.querySelectorAll('.testimonial-checkbox:checked');
        if (selected.length === 0) return alert('Pilih testimoni yang akan dihapus.');
        if (!confirm(`Hapus ${selected.length} testimoni?`)) return;
        const ids = Array.from(selected).map(cb => cb.value);
        fetch('/admin/testimonials/bulk-delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(data => { if(data.success) location.reload(); });
    });
});
</script>
@endsection
