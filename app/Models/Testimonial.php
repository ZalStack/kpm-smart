<?php
// app/Models/Testimonial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'rating',
        'is_active',
        'is_approved',
        'approved_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'rating' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true)->where('is_active', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }
}
