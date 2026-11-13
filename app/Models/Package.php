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
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = now($tz);

        if ($this->start_date) {
            $startDateStr = $this->start_date instanceof \DateTimeInterface 
                ? $this->start_date->format('Y-m-d') 
                : substr((string) $this->start_date, 0, 10);
            $startTimeStr = $this->start_time ? substr((string) $this->start_time, 0, 8) : '00:00:00';
            if (strlen($startTimeStr) === 5) {
                $startTimeStr .= ':00';
            }
            try {
                $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$startDateStr} {$startTimeStr}", $tz);
                if ($now->lt($startDateTime)) {
                    return false;
                }
            } catch (\Throwable $e) {
                if ($now->lt($this->start_date->copy()->startOfDay())) {
                    return false;
                }
            }
        }

        if ($this->end_date) {
            $endDateStr = $this->end_date instanceof \DateTimeInterface 
                ? $this->end_date->format('Y-m-d') 
                : substr((string) $this->end_date, 0, 10);
            $endTimeStr = $this->end_time ? substr((string) $this->end_time, 0, 8) : '23:59:59';
            if (strlen($endTimeStr) === 5) {
                $endTimeStr .= ':00';
            }
            try {
                $endDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$endDateStr} {$endTimeStr}", $tz);
                if ($now->gt($endDateTime)) {
                    return false;
                }
            } catch (\Throwable $e) {
                if ($now->gt($this->end_date->copy()->endOfDay())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Status jadwal: upcoming, active, expired, atau no_limit.
     * Mempertimbangkan tanggal DAN waktu dalam zona waktu Asia/Jakarta (WIB).
     */
    public function getScheduleStatusAttribute(): string
    {
        if (!$this->start_date && !$this->end_date) {
            return 'no_limit';
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = now($tz);

        if ($this->start_date) {
            $startDateStr = $this->start_date instanceof \DateTimeInterface 
                ? $this->start_date->format('Y-m-d') 
                : substr((string) $this->start_date, 0, 10);
            $startTimeStr = $this->start_time ? substr((string) $this->start_time, 0, 8) : '00:00:00';
            if (strlen($startTimeStr) === 5) {
                $startTimeStr .= ':00';
            }
            try {
                $startDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$startDateStr} {$startTimeStr}", $tz);
                if ($now->lt($startDateTime)) {
                    return 'upcoming';
                }
            } catch (\Throwable $e) {
                if ($now->lt($this->start_date->copy()->startOfDay())) {
                    return 'upcoming';
                }
            }
        }

        if ($this->end_date) {
            $endDateStr = $this->end_date instanceof \DateTimeInterface 
                ? $this->end_date->format('Y-m-d') 
                : substr((string) $this->end_date, 0, 10);
            $endTimeStr = $this->end_time ? substr((string) $this->end_time, 0, 8) : '23:59:59';
            if (strlen($endTimeStr) === 5) {
                $endTimeStr .= ':00';
            }
            try {
                $endDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$endDateStr} {$endTimeStr}", $tz);
                if ($now->gt($endDateTime)) {
                    return 'expired';
                }
            } catch (\Throwable $e) {
                if ($now->gt($this->end_date->copy()->endOfDay())) {
                    return 'expired';
                }
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
     * Memeriksa apakah siswa yang sedang login berhak mengakses paket PR ini
     * berdasarkan Bidang Studi, Tingkat Kelas, dan Level Berbakat.
     *
     * @param  \App\Models\User|null  $user
     * @return array{allowed: bool, reason: string|null}
     */
    public function matchesStudent($user): array
    {
        if (! $user || $user->role !== 'user') {
            return ['allowed' => true, 'reason' => null];
        }

        $studentBidang = trim((string) $user->bidang);
        $studentLevel  = trim((string) $user->level);
        $studentClass  = trim(str_replace(["'", '"'], '', (string) $user->student_class));
        $combinedInfo  = $studentBidang . ' ' . $studentLevel;

        $isStudentMtk = (bool) preg_match('/(mtk|matematika|math)/i', $combinedInfo);
        $isStudentIpa = (bool) preg_match('/(ipa|sains|science)/i', $combinedInfo);
        $isStudentDual = ($isStudentMtk && $isStudentIpa)
            || preg_match('/(&|,|dan|\/)/i', $studentBidang)
            || str_contains(strtolower($studentBidang), 'dual')
            || str_contains(strtolower($studentBidang), 'semua');

        $pkgBidang = trim((string) ($this->bidang ?? ''));
        $pkgTitle  = trim((string) ($this->title ?? ''));
        $pkgKelas  = trim(str_replace(["'", '"'], '', (string) ($this->kelas ?? '')));
        $pkgLevel  = trim((string) ($this->level ?? ''));

        // 1. Cek Bidang Studi
        if (! $isStudentDual && $pkgBidang !== '') {
            $pkgIsIpa = (bool) (preg_match('/(ipa|sains|science)/i', $pkgBidang) || preg_match('/(ipa|sains|science)/i', $pkgTitle));
            $pkgIsMtk = (bool) (preg_match('/(mtk|matematika|math)/i', $pkgBidang) || preg_match('/(mtk|matematika|math)/i', $pkgTitle));

            if ($isStudentMtk && ! $isStudentIpa && $pkgIsIpa && ! $pkgIsMtk) {
                return [
                    'allowed' => false,
                    'reason' => 'Maaf, tugas PR ini khusus untuk bidang studi IPA. Anda terdaftar pada bidang Matematika.',
                ];
            }
            if ($isStudentIpa && ! $isStudentMtk && $pkgIsMtk && ! $pkgIsIpa) {
                return [
                    'allowed' => false,
                    'reason' => 'Maaf, tugas PR ini khusus untuk bidang studi Matematika. Anda terdaftar pada bidang IPA.',
                ];
            }
        }

        // 2. Cek Tingkat Kelas
        if ($pkgKelas !== '' && $studentClass !== '') {
            $numStudent = preg_replace('/[^0-9]/', '', $studentClass);
            $numPkg     = preg_replace('/[^0-9]/', '', $pkgKelas);

            $matched = false;
            if (strcasecmp($pkgKelas, $studentClass) === 0) {
                $matched = true;
            } elseif (strcasecmp($pkgKelas, 'Kelas ' . $studentClass) === 0 || strcasecmp('Kelas ' . $pkgKelas, $studentClass) === 0) {
                $matched = true;
            } elseif (! empty($numStudent) && ! empty($numPkg) && $numStudent === $numPkg) {
                $matched = true;
            }

            if (! $matched) {
                $displayPkgKelas = str_starts_with(strtolower($pkgKelas), 'kelas') ? $pkgKelas : 'Kelas ' . $pkgKelas;
                $displayUserKelas = str_starts_with(strtolower($user->student_class), 'kelas') ? $user->student_class : 'Kelas ' . $user->student_class;
                return [
                    'allowed' => false,
                    'reason' => "Maaf, tugas PR ini diperuntukkan untuk {$displayPkgKelas}. Kelas Anda saat ini: {$displayUserKelas}.",
                ];
            }
        }

        // 3. Cek Level Kemampuan / Berbakat
        if ($pkgLevel !== '' && $studentLevel !== '') {
            $effectiveStudentLevel = $studentLevel;
            // Jika profil siswa memiliki format ganda seperti "Berbakat A (IPA), Berbakat B (MTK)"
            $pkgIsIpa = (bool) (preg_match('/(ipa|sains|science)/i', $pkgBidang) || preg_match('/(ipa|sains|science)/i', $pkgTitle));
            $pkgIsMtk = (bool) (preg_match('/(mtk|matematika|math)/i', $pkgBidang) || preg_match('/(mtk|matematika|math)/i', $pkgTitle));

            if ($pkgIsIpa && preg_match('/Berbakat\s*([A-C][0-9]?)\s*\((?:IPA|Sains)\)/i', $studentLevel, $mSub)) {
                $effectiveStudentLevel = 'Berbakat ' . strtoupper($mSub[1]);
            } elseif ($pkgIsMtk && preg_match('/Berbakat\s*([A-C][0-9]?)\s*\((?:MTK|Matematika)\)/i', $studentLevel, $mSub)) {
                $effectiveStudentLevel = 'Berbakat ' . strtoupper($mSub[1]);
            }

            $matched = false;
            if (strcasecmp($pkgLevel, $effectiveStudentLevel) === 0) {
                $matched = true;
            } elseif (preg_match('/Berbakat\s*([A-C])/i', $effectiveStudentLevel, $ms) && preg_match('/Berbakat\s*([A-C])/i', $pkgLevel, $mp)) {
                if (strtoupper($ms[1]) === strtoupper($mp[1])) {
                    $matched = true;
                }
            } elseif (str_contains(strtolower($effectiveStudentLevel), strtolower($pkgLevel))) {
                $matched = true;
            }

            if (! $matched) {
                return [
                    'allowed' => false,
                    'reason' => "Maaf, tugas PR ini khusus untuk Level {$pkgLevel}. Level Anda saat ini: {$studentLevel}.",
                ];
            }
        }

        return ['allowed' => true, 'reason' => null];
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
        $all = collect($this->questions ?? []);

        // Paket lama bisa saja belum memiliki konsep kartu pada soalnya.
        $hasCardConcept = $all->contains(fn ($question) => array_key_exists('card_id', $question));

        if ($cardId !== null && $hasCardConcept) {
            // Kartu tidak dikenal = tidak ada soal. Mencegah siswa menembak
            // seluruh soal semua kartu lewat card_id acak.
            $questions = $all->where('card_id', $cardId)->values()->all();
        } else {
            $questions = $all->values()->all();
        }

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
