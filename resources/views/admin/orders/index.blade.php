{{-- admin/orders/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')
@section('header-title', 'Manajemen Pesanan')
@section('header-sub', 'Kelola semua transaksi pesanan')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Total Pesanan</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">{{ $orders->count() }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Lunas</p>
                    <p class="text-2xl font-bold text-success-500 leading-tight">{{ $orders->where('payment_status', 'paid')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Pending</p>
                    <p class="text-2xl font-bold text-gold-600 leading-tight">{{ $orders->where('payment_status', 'pending')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-danger-500 to-danger-600 text-white shadow-lg shadow-danger-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Gagal</p>
                    <p class="text-2xl font-bold text-danger-500 leading-tight">{{ $orders->where('payment_status', 'failed')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="admin-card overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-12 md:p-16 text-center">
                <div class="w-20 h-20 mx-auto rounded-lg bg-muted flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121 0 2.09-.773 2.34-1.872l1.836-8.046A1.125 1.125 0 0018.054 3H5.106m2.394 11.25l-1.5-6h13.5"/></svg>
                </div>
                <h3 class="text-lg font-bold text-muted-foreground">Belum Ada Pesanan</h3>
                <p class="text-muted-foreground mt-1 text-sm">Belum ada transaksi yang terjadi</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3.5 text-left">No. Pesanan</th>
                            <th class="px-5 py-3.5 text-left hidden sm:table-cell">Pengguna</th>
                            <th class="px-5 py-3.5 text-left hidden md:table-cell">Paket</th>
                            <th class="px-5 py-3.5 text-left">Harga</th>
                            <th class="px-5 py-3.5 text-left hidden lg:table-cell">Status</th>
                            <th class="px-5 py-3.5 text-left hidden lg:table-cell">Membership</th>
                            <th class="px-5 py-3.5 text-left hidden xl:table-cell">Enroll Key</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($orders as $order)
                            @php
                                $enrollment = $order->enrollment ?? [];
                                $sent = $enrollment['sent_by_admin'] ?? false;
                                $activated = $enrollment['activated'] ?? false;
                                $unlocked = $enrollment['unlocked'] ?? false;
                                $mStatus = $order->membershipStatus();
                            @endphp
                            <tr class="hover:bg-muted/50 transition-colors">
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs font-semibold text-brand-800 bg-brand-50 px-2 py-0.5 rounded-md">{{ $order->order_number }}</span>
                                    @if(str_contains($order->payment_notes ?? '', 'Perpanjangan'))
                                        <div class="text-[10px] text-primary font-medium mt-1">Perpanjangan</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 hidden sm:table-cell text-foreground">{{ $order->user->name ?? 'User' }}</td>
                                <td class="px-5 py-4 hidden md:table-cell text-muted-foreground">@if($order->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $order->item_title }}</td>
                                <td class="px-5 py-4 font-semibold text-brand-900">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    @if($order->is_custom_amount)
                                        <div class="text-[10px] text-success-500 font-medium mt-0.5">Seikhlasnya</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    @if($order->payment_status === 'paid')
                                        <span class="badge-success">Lunas</span>
                                    @elseif($order->payment_status === 'pending')
                                        <span class="badge-warning">Pending</span>
                                    @else
                                        <span class="badge-danger">Gagal</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    @if($mStatus === null)
                                        <span class="text-xs text-muted-foreground">-</span>
                                    @else
                                        <div class="flex flex-col leading-tight gap-0.5">
                                            <span class="{{ $mStatus === 'active' ? 'badge-success' : ($mStatus === 'expiring' ? 'badge-warning' : 'badge-danger') }} text-[10px] py-0.5 px-2 w-fit">
                                                {{ $order->membershipStatusLabel() }}
                                            </span>
                                            <span class="text-[10px] text-muted-foreground">
                                                s/d {{ $order->membership_end?->translatedFormat('d M Y') }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 hidden xl:table-cell">
                                    @if($order->enrollment && isset($order->enrollment['key']))
                                        <div class="flex items-center gap-1.5">
                                            <code class="bg-muted px-2 py-0.5 rounded-md text-xs font-mono text-foreground">{{ $order->enrollment['key'] }}</code>
                                            @if($unlocked)
                                                <svg class="w-3.5 h-3.5 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" title="Used"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                            @elseif($activated)
                                                <svg class="w-3.5 h-3.5 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" title="Activated"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @elseif($sent)
                                                <svg class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" title="Sent"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" title="Not Sent"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-muted-foreground">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary text-xs font-semibold rounded-md hover:bg-primary/20 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection