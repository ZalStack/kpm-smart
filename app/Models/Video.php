<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'package_id',
        'video_file',
        'video_url',
        'thumbnail',
        'price',
        'discount_type',
        'discount_value',
        'access_duration_days',
        'is_active',
        'is_pay_what_you_want',
        'min_pay_amount',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'is_active' => 'boolean',
            'access_duration_days' => 'integer',
            'is_pay_what_you_want' => 'boolean',
            'min_pay_amount' => 'decimal:2',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function videoOrders(): HasMany
    {
        return $this->hasMany(VideoOrder::class);
    }

    public function hasDiscount(): bool
    {
        return $this->discount_type !== null
            && $this->discount_value !== null
            && $this->discount_value > 0;
    }

    public function getFinalPriceAttribute(): float
    {
        if (!$this->hasDiscount()) {
            return (float) $this->price;
        }

        if ($this->discount_type === 'percent') {
            return round((float) $this->price * (1 - min(100, max(0, (float) $this->discount_value)) / 100));
        }

        return max(0, (float) $this->price - (float) $this->discount_value);
    }

    public function getDiscountLabelAttribute(): ?string
    {
        if (!$this->hasDiscount()) {
            return null;
        }

        if ($this->discount_type === 'percent') {
            return number_format($this->discount_value, 0) . '%';
        }

        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }

    public function minimumPayAmount(): float
    {
        return (float) ($this->min_pay_amount ?? 0);
    }
}
