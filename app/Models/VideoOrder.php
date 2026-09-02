<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'order_number',
        'total_price',
        'payment_status',
        'transaction_id',
        'payment_reference',
        'payment_url',
        'payment_type',
        'payment_time',
        'access_granted',
        'access_start',
        'access_end',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'payment_time' => 'datetime',
            'access_granted' => 'boolean',
            'access_start' => 'date',
            'access_end' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Baris mirror di tabel orders — membuat transaksi video ikut tampil di
     * halaman Pesanan user, Transaksi admin, dan Laporan. Dibuat otomatis
     * oleh VideoOrderObserver, method ini hanya jaring pengaman.
     * Aman terhadap dua request paralel (unique constraint + recovery).
     */
    public function mirrorOrder(): Order
    {
        $existing = Order::where('video_order_id', $this->id)->first();

        if ($existing) {
            return $existing;
        }

        try {
            return Order::create([
                'video_order_id' => $this->id,
                'user_id' => $this->user_id,
                'type' => 'video',
                'order_number' => $this->order_number,
                'total_price' => $this->total_price,
                'payment_status' => $this->payment_status,
                'payment_notes' => 'Pembelian video pembahasan',
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Request lain lebih dulu membuat baris mirror.
            return Order::where('video_order_id', $this->id)->firstOrFail();
        }
    }

    public function activateAccess(): void
    {
        if (!$this->video) {
            return;
        }

        $start = now()->toDateTime();
        $end = now()->addDays($this->video->access_duration_days ?? 30)->toDateTime();

        $this->update([
            'access_granted' => true,
            'access_start' => $start,
            'access_end' => $end,
        ]);
    }

    /**
     * Tandai pesanan video lunas secara atomik.
     * Akses video TIDAK diaktifkan otomatis — menunggu aktivasi manual oleh admin.
     * Aman dipanggil berkali-kali dan dari jalur paralel (webhook, redirect).
     */
    public function markPaid(array $extra = []): void
    {
        DB::transaction(function () use ($extra) {
            $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (!$fresh || $fresh->payment_status === 'paid') {
                return;
            }

            $fresh->update(array_merge(['payment_status' => 'paid'], $extra));

            // Sinkronkan instance caller dengan data terbaru.
            $this->setRawAttributes($fresh->getAttributes());
        });
    }

    public function isAccessActive(): bool
    {
        return $this->access_granted
            && $this->access_end
            && $this->access_end->isFuture();
    }

    public function accessDaysRemaining(): int
    {
        if (!$this->isAccessActive()) {
            return 0;
        }

        return (int) now()->diffInDays($this->access_end, false);
    }

    public function accessStatus(): string
    {
        if ($this->payment_status === 'pending') {
            return 'pending_payment';
        }

        if ($this->payment_status !== 'paid') {
            return 'failed';
        }

        if (!$this->access_granted) {
            return 'awaiting_activation';
        }

        return $this->isAccessActive() ? 'active' : 'expired';
    }
}
