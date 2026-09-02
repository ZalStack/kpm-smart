<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'package_id', 'video_order_id', 'type', 'order_number', 'total_price',
        'is_custom_amount', 'payment_status', 'transaction_id', 'payment_reference',
        'payment_url',
        'payment_type', 'payment_time', 'payment_notes',
        'enrollment',
        'membership_duration_days', 'membership_start', 'membership_end',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_custom_amount' => 'boolean',
        'payment_time' => 'datetime',
        'enrollment' => 'array',
        'membership_duration_days' => 'integer',
        'membership_start' => 'date',
        'membership_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function videoOrder()
    {
        return $this->belongsTo(VideoOrder::class);
    }

    public function isVideoOrder(): bool
    {
        return $this->type === 'video' || $this->video_order_id !== null;
    }

    /**
     * Judul item yang dibeli pada order ini (video pembahasan atau paket).
     */
    public function getItemTitleAttribute(): string
    {
        if ($this->isVideoOrder()) {
            return $this->videoOrder?->video?->title ?? 'Video Pembahasan';
        }

        return $this->package?->title ?? 'Paket Dihapus';
    }

    public function practiceSessions()
    {
        return $this->hasMany(PracticeSession::class);
    }

    /**
     * Enroll key sudah diaktifkan DAN dikirim oleh admin.
     * Ini adalah syarat sebelum enroll key boleh tampil / dipakai oleh user.
     */
    public function enrollmentIsReady(): bool
    {
        $enrollment = $this->enrollment ?? [];
        return (bool) ($enrollment['sent_by_admin'] ?? false) && (bool) ($enrollment['activated'] ?? false);
    }

    /**
     * User sudah memasukkan enroll key yang benar di halaman paket,
     * sehingga berhak mengakses soal latihan.
     */
    public function enrollmentIsUnlocked(): bool
    {
        $enrollment = $this->enrollment ?? [];
        return (bool) ($enrollment['unlocked'] ?? false);
    }

    /*
    |--------------------------------------------------------------------
    | Membership (masa aktif paket)
    |--------------------------------------------------------------------
    */

    /**
     * Tandai order lunas & aktifkan membership secara atomik.
     * Aman dipanggil berkali-kali dan dari jalur paralel (webhook, redirect,
     * verifikasi admin) — lockForUpdate memastikan aktivasi hanya terjadi
     * sekali per order agar tanggal tidak bergeser ganda.
     */
    public function markPaid(array $extra = []): void
    {
        // Sumber kebenaran pembayaran video ada di tabel video_orders —
        // delegasikan ke sana; baris mirror tersinkron otomatis via observer
        // (sinkronisasi antar tabel hanya satu arah: video_orders -> orders).
        if ($this->isVideoOrder()) {
            $videoOrder = $this->videoOrder;

            if ($videoOrder) {
                $videoOrder->markPaid($extra);

                // Muat ulang baris mirror yang baru ditulis oleh observer.
                $this->refresh();
                return;
            }
        }

        DB::transaction(function () use ($extra) {
            // Kunci baris; caller lain yang paralel menunggu di sini lalu
            // melihat status 'paid' sehingga melewati blok aktivasi.
            $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (!$fresh || $fresh->payment_status === 'paid') {
                return;
            }

            $fresh->update(array_merge(['payment_status' => 'paid'], $extra));
            $fresh->activateMembership();

            // Sinkronkan instance caller dengan data terbaru.
            $this->setRawAttributes($fresh->getAttributes());
        });
    }

    /**
     * Aktifkan / perpanjang masa membership order ini berdasarkan durasi
     * membership paket. Jika user memiliki order lain (paket yang sama)
     * yang masih aktif, masa aktif baru akan disambung dari tanggal
     * berakhir sebelumnya. Jika sudah kedaluwarsa (atau belum pernah
     * membeli), masa aktif dihitung mulai hari ini.
     */
    public function activateMembership(?Package $package = null): void
    {
        // Order video pembahasan tidak memiliki membership paket.
        if ($this->isVideoOrder()) {
            return;
        }

        $package = $package ?? $this->package;

        if (!$package) {
            return;
        }

        $duration = (int) ($package->membership_duration_days ?? 30);
        if ($duration < 1) {
            $duration = 30;
        }

        $previous = static::where('user_id', $this->user_id)
            ->where('package_id', $this->package_id)
            ->where('id', '!=', $this->id)
            ->where('payment_status', 'paid')
            ->whereNotNull('membership_end')
            ->orderByDesc('membership_end')
            ->first();

        $today = now()->startOfDay();

        if ($previous && $previous->membership_end && $previous->membership_end->copy()->endOfDay()->gte($today)) {
            // Membership sebelumnya masih aktif -> sambung dari tanggal berakhir sebelumnya.
            $start = $previous->membership_end->copy()->addDay();
        } else {
            // Belum pernah punya membership / sudah kedaluwarsa -> mulai dari hari ini.
            $start = $today->copy();
        }

        $end = $start->copy()->addDays($duration - 1);

        $this->membership_duration_days = $duration;
        $this->membership_start = $start->toDateString();
        $this->membership_end = $end->toDateString();
        $this->save();
    }

    /**
     * Apakah masa membership order ini masih aktif (belum lewat tanggal berakhir).
     */
    public function isMembershipActive(): bool
    {
        if (!$this->membership_end) {
            return false;
        }

        return now()->startOfDay()->lte($this->membership_end->copy()->endOfDay());
    }

    /**
     * Apakah membership akan segera berakhir (<= $withinDays hari lagi) namun belum kedaluwarsa.
     */
    public function isMembershipExpiringSoon(int $withinDays = 7): bool
    {
        if (!$this->isMembershipActive()) {
            return false;
        }

        return $this->membershipDaysRemaining() <= $withinDays;
    }

    /**
     * Sisa hari aktif membership (0 jika sudah lewat / belum ada data membership).
     */
    public function membershipDaysRemaining(): int
    {
        if (!$this->membership_end) {
            return 0;
        }

        $days = now()->startOfDay()->diffInDays($this->membership_end->copy()->endOfDay(), false);

        return (int) max(0, ceil($days));
    }

    /**
     * Status membership order ini: 'active', 'expiring', 'expired',
     * atau null jika order belum pernah punya data membership (belum dibayar).
     */
    public function membershipStatus(): ?string
    {
        if (!$this->membership_end) {
            return null;
        }

        if (!$this->isMembershipActive()) {
            return 'expired';
        }

        if ($this->isMembershipExpiringSoon()) {
            return 'expiring';
        }

        return 'active';
    }

    /**
     * Label status membership dalam Bahasa Indonesia, untuk ditampilkan ke user.
     */
    public function membershipStatusLabel(): string
    {
        return match ($this->membershipStatus()) {
            'active' => 'Aktif',
            'expiring' => 'Akan Berakhir',
            'expired' => 'Kedaluwarsa',
            default => '-',
        };
    }

    /**
     * Order (paid) terbaru milik seorang user untuk sebuah paket — dipakai
     * sebagai acuan status membership paket tersebut saat ini, karena setiap
     * perpanjangan membuat order baru yang menyambung dari order sebelumnya.
     */
    public static function latestPaidFor(int $userId, int $packageId): ?self
    {
        return static::where('user_id', $userId)
            ->where('package_id', $packageId)
            ->where('payment_status', 'paid')
            ->orderByDesc('created_at')
            ->first();
    }
}
