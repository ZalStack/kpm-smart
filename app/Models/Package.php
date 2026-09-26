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
     * Ringkasan paket yang AMAN dikirim ke siswa.
     *
     * Berbeda dengan model mentah, hasil ini tidak memuat `questions` sama
     * sekali — sehingga kunci jawaban tidak ikut terbawa ke browser. Paket ini
     * yang dipakai untuk prop `package` pada halaman mengerjakan soal.
     */
    public function summary(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'kelas' => $this->kelas,
            'bidang' => $this->bidang,
            'level' => $this->level,
            'cards' => $this->cards ?? [],
            'total_cards' => count($this->cards ?? []),
            'total_questions' => count($this->questions ?? []),
            'schedule_status' => $this->schedule_status,
            'schedule_label' => $this->schedule_label,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }

    /**
     * Ambil soal untuk ditampilkan ke siswa SEDANG mengerjakan.
     *
     * Penting: `correct_answer` dan `explanation` sengaja dibuang. Kalau ikut
     * dikirim, siswa cukup membuka DevTools > Network/Payload untuk membaca
     * seluruh kunci jawaban sebelum menjawab. Penilaian tetap dilakukan di
     * server (lihat PracticeController::submit).
     */
    public function questionsForAttempt(?string $cardId = null): array
    {
        $questions = collect($this->questions ?? [])
            ->when($cardId !== null, fn ($c) => $c->where('card_id', $cardId))
            ->values()
            ->all();

        return array_map(function (array $question): array {
            unset($question['correct_answer'], $question['explanation']);

            return $question;
        }, $questions);
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
