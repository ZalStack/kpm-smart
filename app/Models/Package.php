<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'kelas', 'thumbnail', 'price',
        'bidang', 'level',
        'start_date', 'end_date', 'start_time', 'end_time',
        'show_answer_key', 'show_explanation', 'show_score',
        'is_active',
        'cards', 'questions', 'reviews',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_answer_key' => 'boolean',
        'show_explanation' => 'boolean',
        'show_score' => 'boolean',
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'cards' => 'array',
        'questions' => 'array',
        'reviews' => 'array',
    ];

    public function practiceSessions()
    {
        return $this->hasMany(PracticeSession::class);
    }

    /**
     * Apakah paket ini sedang dalam jadwal pengerjaan.
     */
    public function isWithinSchedule(): bool
    {
        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Status jadwal: upcoming, active, expired, atau no_limit.
     */
    public function getScheduleStatusAttribute(): string
    {
        if (!$this->start_date && !$this->end_date) {
            return 'no_limit';
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return 'upcoming';
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Label jadwal yang mudah dibaca.
     */
    public function getScheduleLabelAttribute(): string
    {
        $status = $this->schedule_status;

        if ($status === 'no_limit') {
            return 'Tanpa Batasan Waktu';
        }

        $parts = [];
        if ($this->start_date) {
            $parts[] = $this->start_date->translatedFormat('d M Y');
        }
        if ($this->end_date) {
            $parts[] = $this->end_date->translatedFormat('d M Y');
        }

        $dateStr = implode(' — ', $parts);

        if ($this->start_time && $this->end_time) {
            $dateStr .= ' (' . substr($this->start_time, 0, 5) . ' — ' . substr($this->end_time, 0, 5) . ' WIB)';
        }

        return $dateStr;
    }

    /**
     * Apakah user boleh melihat kunci jawaban.
     */
    public function canShowAnswerKey(): bool
    {
        return (bool) $this->show_answer_key;
    }

    /**
     * Apakah user boleh melihat pembahasan.
     */
    public function canShowExplanation(): bool
    {
        return (bool) $this->show_explanation;
    }

    /**
     * Apakah user boleh melihat skor.
     */
    public function canShowScore(): bool
    {
        return (bool) $this->show_score;
    }
}
