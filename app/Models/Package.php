<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'kelas', 'thumbnail',
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
     * Apakah paket ini sedang dalam jadwal pengerjaan (termasuk waktu).
     */
    public function isWithinSchedule(): bool
    {
        $now = now();

        if ($this->start_date) {
            $startDateTime = $this->start_time
                ? $this->start_date->copy()->setTimeFromTimeString($this->start_time)
                : $this->start_date->copy()->startOfDay();
            if ($now->lt($startDateTime)) {
                return false;
            }
        }

        if ($this->end_date) {
            $endDateTime = $this->end_time
                ? $this->end_date->copy()->setTimeFromTimeString($this->end_time)
                : $this->end_date->copy()->endOfDay();
            if ($now->gt($endDateTime)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Status jadwal: upcoming, active, expired, atau no_limit.
     * Mempertimbangkan tanggal DAN waktu.
     */
    public function getScheduleStatusAttribute(): string
    {
        if (!$this->start_date && !$this->end_date) {
            return 'no_limit';
        }

        $now = now();

        if ($this->start_date) {
            $startDateTime = $this->start_time
                ? $this->start_date->copy()->setTimeFromTimeString($this->start_time)
                : $this->start_date->copy()->startOfDay();
            if ($now->lt($startDateTime)) {
                return 'upcoming';
            }
        }

        if ($this->end_date) {
            $endDateTime = $this->end_time
                ? $this->end_date->copy()->setTimeFromTimeString($this->end_time)
                : $this->end_date->copy()->endOfDay();
            if ($now->gt($endDateTime)) {
                return 'expired';
            }
        }

        return 'active';
    }

    /**
     * Label jadwal yang mudah dibaca (termasuk waktu jika ada).
     */
    public function getScheduleLabelAttribute(): string
    {
        $status = $this->schedule_status;

        if ($status === 'no_limit') {
            return 'Tanpa Batasan Waktu';
        }

        $parts = [];
        if ($this->start_date) {
            $label = $this->start_date->translatedFormat('d M Y');
            if ($this->start_time) {
                $label .= ' ' . substr($this->start_time, 0, 5) . ' WIB';
            }
            $parts[] = $label;
        }
        if ($this->end_date) {
            $label = $this->end_date->translatedFormat('d M Y');
            if ($this->end_time) {
                $label .= ' ' . substr($this->end_time, 0, 5) . ' WIB';
            }
            $parts[] = $label;
        }

        return implode(' — ', $parts);
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
