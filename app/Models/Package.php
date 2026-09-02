<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'kelas', 'jenjang', 'thumbnail', 'price',
        'discount_price', 'is_discount_active',
        'is_pay_what_you_want', 'min_pay_amount',
        'membership_duration_days',
        'is_active', 'hide_explanation', 'time_limit_minutes',
        'cards', 'questions', 'reviews'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hide_explanation' => 'boolean',
        'time_limit_minutes' => 'integer',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_discount_active' => 'boolean',
        'is_pay_what_you_want' => 'boolean',
        'min_pay_amount' => 'decimal:2',
        'membership_duration_days' => 'integer',
        'cards' => 'array',
        'questions' => 'array',
        'reviews' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function practiceSessions()
    {
        return $this->hasMany(PracticeSession::class);
    }

    /**
     * Apakah paket ini sedang punya diskon aktif & valid
     * (diskon harus lebih murah dari harga normal).
     */
    public function hasDiscount(): bool
    {
        return (bool) $this->is_discount_active
            && $this->discount_price !== null
            && (float) $this->discount_price < (float) $this->price;
    }

    /**
     * Harga akhir yang harus dibayar user: harga diskon (jika aktif)
     * atau harga normal.
     */
    public function getFinalPriceAttribute()
    {
        return $this->hasDiscount() ? $this->discount_price : $this->price;
    }

    /**
     * Persentase potongan harga, dibulatkan ke bawah. 0 jika tidak diskon.
     */
    public function getDiscountPercentAttribute(): int
    {
        if (!$this->hasDiscount() || (float) $this->price <= 0) {
            return 0;
        }

        return (int) round((((float) $this->price - (float) $this->discount_price) / (float) $this->price) * 100);
    }

    /**
     * Nominal minimum yang boleh dibayar user untuk paket "Bayar Seikhlasnya".
     */
    public function minimumPayAmount(): float
    {
        return (float) ($this->min_pay_amount ?? 0);
    }

    /**
     * Durasi membership paket ini dalam hari (fallback 30 hari jika belum diatur).
     */
    public function membershipDurationDays(): int
    {
        $days = (int) ($this->membership_duration_days ?? 30);
        return $days < 1 ? 30 : $days;
    }

    /**
     * Batas waktu pengerjaan dalam menit. 0 atau null berarti tanpa batas.
     */
    public function getTimeLimitAttribute(): ?int
    {
        $minutes = (int) ($this->time_limit_minutes ?? 0);
        return $minutes > 0 ? $minutes : null;
    }

    /**
     * Label waktu pengerjaan, mis. "60 Menit", "Tanpa Batas".
     */
    public function getTimeLimitLabelAttribute(): string
    {
        $minutes = $this->time_limit_minutes ?? 0;
        if ($minutes <= 0) {
            return 'Tanpa Batas';
        }
        if ($minutes >= 60 && $minutes % 60 === 0) {
            $hours = $minutes / 60;
            return $hours . ' Jam';
        }
        return $minutes . ' Menit';
    }

    /**
     * Label durasi membership yang mudah dibaca, mis. "30 Hari", "3 Bulan", "1 Tahun".
     */
    public function getMembershipDurationLabelAttribute(): string
    {
        $days = $this->membershipDurationDays();

        $presets = [
            7 => '7 Hari',
            14 => '14 Hari',
            30 => '30 Hari (1 Bulan)',
            60 => '60 Hari (2 Bulan)',
            90 => '90 Hari (3 Bulan)',
            180 => '180 Hari (6 Bulan)',
            365 => '365 Hari (1 Tahun)',
        ];

        return $presets[$days] ?? $days . ' Hari';
    }
}
