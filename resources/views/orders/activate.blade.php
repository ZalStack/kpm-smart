{{-- user/orders/activate.blade.php --}}
@extends('layouts.app')

@section('title', 'Aktivasi Paket')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="bg-card rounded-lg shadow-xl max-w-md w-full p-6 md:p-8 border border-border">
        <div class="text-center">
            <div class="text-5xl md:text-6xl mb-4">🔑</div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Aktivasi Paket</h1>
            <p class="text-muted-foreground mt-2 text-sm md:text-base">Masukkan Enroll Key untuk mengaktifkan paket</p>
        </div>

        <div class="my-6 p-4 bg-muted rounded-md border border-border">
            <p class="text-sm text-muted-foreground">Paket</p>
            <p class="font-bold text-foreground text-base md:text-lg">{{ $order->item_title }}</p>
            <p class="text-sm text-muted-foreground mt-1 break-all">Enroll Key: <code class="bg-muted px-2 py-1 rounded text-xs md:text-sm border border-border">{{ $order->enrollment['key'] }}</code></p>
        </div>

        <form action="{{ route('orders.activate', $order->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Masukkan Enroll Key</label>
                <input type="text" name="enroll_key" placeholder="Contoh: ENR-XXXXXX-XXXX" required
                       class="w-full px-4 py-3 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base transition">
            </div>
            <button type="submit" class="w-full bg-navy-light text-white py-3 rounded-md font-semibold hover:bg-navy transition text-sm md:text-base">
                Aktivasi Paket 🔓
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('orders.index') }}" class="text-muted-foreground hover:text-foreground text-sm transition">
                ← Kembali ke Riwayat Pesanan
            </a>
        </div>
    </div>
</div>
@endsection
