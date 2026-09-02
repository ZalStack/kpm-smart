@extends('layouts.admin')

@section('title', 'Bantuan - Admin')

@section('header-title', 'Manajemen Bantuan')
@section('header-sub', 'Kelola pertanyaan dari pengguna')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <div class="admin-card stat-card stagger-item">
            <div class="flex items-center gap-3">
                <span class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </span>
                <div>
                    <p class="text-[10px] text-muted-foreground uppercase font-semibold tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-brand-900">{{ $counts['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card stagger-item">
            <div class="flex items-center gap-3">
                <span class="stat-icon bg-gradient-to-br from-warning-500 to-warning-600 text-white shadow-lg shadow-warning-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <div>
                    <p class="text-[10px] text-muted-foreground uppercase font-semibold tracking-wider">Menunggu</p>
                    <p class="text-2xl font-bold text-brand-900">{{ $counts['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card stagger-item">
            <div class="flex items-center gap-3">
                <span class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <div>
                    <p class="text-[10px] text-muted-foreground uppercase font-semibold tracking-wider">Dijawab</p>
                    <p class="text-2xl font-bold text-brand-900">{{ $counts['answered'] }}</p>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card stagger-item">
            <div class="flex items-center gap-3">
                <span class="stat-icon bg-gradient-to-br from-gray-400 to-gray-500 text-white shadow-lg shadow-gray-400/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <div>
                    <p class="text-[10px] text-muted-foreground uppercase font-semibold tracking-wider">Ditutup</p>
                    <p class="text-2xl font-bold text-brand-900">{{ $counts['closed'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-card p-3 sm:p-4">
        <form method="GET" action="{{ route('admin.support.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pertanyaan atau jawaban..."
                    class="form-input w-full">
            </div>
            <div class="flex flex-wrap gap-2">
                <select name="status" class="form-select">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="answered" {{ request('status') == 'answered' ? 'selected' : '' }}>Dijawab</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup</option>
                </select>
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Filter
                </button>
                <a href="{{ route('admin.support.index') }}" class="btn-secondary">Reset</a>
                <a href="{{ route('admin.support.export', request()->all()) }}" class="btn-success ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] admin-table">
                <thead>
                    <tr>
                        <th class="w-12 px-5 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-border text-primary focus:ring-accent-400">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Pengguna</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Pertanyaan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Dibuat</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-muted-foreground uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($tickets as $ticket)
                    @php
                        $statusClasses = [
                            'pending' => 'badge-warning',
                            'answered' => 'badge-success',
                            'closed' => 'badge-neutral',
                        ];
                    @endphp
                    <tr class="hover:bg-muted/80 transition-colors duration-150">
                        <td class="px-5 py-3.5">
                            <input type="checkbox" class="ticket-checkbox rounded border-border text-primary" value="{{ $ticket->id }}">
                        </td>
                        <td class="px-5 py-3.5 text-sm font-medium text-foreground">#{{ $ticket->id }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-900 to-brand-800 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($ticket->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">{{ $ticket->name ?? 'Anonim' }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ $ticket->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-foreground max-w-xs truncate">{{ $ticket->question }}</p>
                            @if($ticket->answer)
                                <span class="text-xs text-success-500 inline-flex items-center gap-1 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Sudah dijawab
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="{{ $statusClasses[$ticket->status] ?? 'badge-neutral' }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-muted-foreground whitespace-nowrap">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn-secondary px-3 py-1.5 text-xs">Detail</a>
                                @if($ticket->status == 'pending')
                                    <span class="text-xs text-warning-500 animate-pulse flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 fill-current" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12"/></svg>
                                        Baru
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16">
                            <div class="empty-state">
                                <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                </div>
                                <p class="text-lg font-semibold text-foreground">Belum ada pertanyaan</p>
                                <p class="text-sm text-muted-foreground">Belum ada pengguna yang mengirimkan pertanyaan bantuan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Bulk Actions -->
        <div class="px-3 sm:px-5 py-3 sm:py-4 border-t border-border flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <button id="bulkDeleteBtn" class="btn-danger text-sm" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Hapus Terpilih
                </button>
                <span class="text-sm text-muted-foreground" id="selectedCount">0 dipilih</span>
            </div>
            <div>{{ $tickets->links() }}</div>
        </div>
    </div>
</div>

<form id="bulkDeleteForm" method="POST" action="{{ route('admin.support.bulk-delete') }}">
    @csrf
    <input type="hidden" name="ids" id="bulkIds">
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkIds = document.getElementById('bulkIds');
    const selectedCount = document.getElementById('selectedCount');
    const bulkForm = document.getElementById('bulkDeleteForm');

    function updateSelected() {
        const checked = document.querySelectorAll('.ticket-checkbox:checked');
        const count = checked.length;
        selectedCount.textContent = count + ' dipilih';
        bulkDeleteBtn.disabled = count === 0;

        const ids = Array.from(checked).map(cb => cb.value);
        bulkIds.value = JSON.stringify(ids);
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelected();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelected();
            if (!this.checked) {
                selectAll.checked = false;
            } else {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                selectAll.checked = allChecked;
            }
        });
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const checked = document.querySelectorAll('.ticket-checkbox:checked');
        if (checked.length === 0) return;

        if (confirm('Yakin ingin menghapus ' + checked.length + ' tiket yang dipilih?')) {
            bulkForm.submit();
        }
    });

    updateSelected();
});
</script>
@endpush
@endsection
