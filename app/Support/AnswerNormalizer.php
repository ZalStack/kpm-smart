<?php

namespace App\Support;

class AnswerNormalizer
{
    /**
     * Map kata angka Indonesia ke nilai numerik.
     */
    private const WORD_UNITS = [
        'nol' => 0,
        'kosong' => 0,
        'satu' => 1,
        'se' => 1,
        'dua' => 2,
        'tiga' => 3,
        'empat' => 4,
        'lima' => 5,
        'enam' => 6,
        'tujuh' => 7,
        'delapan' => 8,
        'sembilan' => 9,
        'sepuluh' => 10,
        'sebelas' => 11,
        'seratus' => 100,
        'seribu' => 1000,
        'sejuta' => 1000000,
        'semiliar' => 1000000000,
        'semilyar' => 1000000000,
        'setengah' => 0.5,
    ];

    /**
     * Cek apakah jawaban siswa cocok dengan kunci jawaban utama atau variasi yang diterima.
     *
     * @param string|null $userAnswer
     * @param string|null $correctAnswer
     * @param array|string|null $acceptedAnswers
     * @return bool
     */
    public static function isMatch(?string $userAnswer, ?string $correctAnswer, array|string|null $acceptedAnswers = []): bool
    {
        if ($userAnswer === null) {
            return false;
        }

        $userTrimmed = trim($userAnswer);
        if ($userTrimmed === '') {
            return false;
        }

        // Kumpulkan semua kandidat kunci jawaban
        $candidates = self::extractCandidates($correctAnswer, $acceptedAnswers);
        if (empty($candidates)) {
            return false;
        }

        $userLower = mb_strtolower($userTrimmed);
        $userTextNorm = self::normalizeText($userTrimmed);
        $userNumeric = self::parseNumber($userTrimmed);

        foreach ($candidates as $cand) {
            $candTrimmed = trim($cand);
            if ($candTrimmed === '') {
                continue;
            }

            $candLower = mb_strtolower($candTrimmed);

            // 1. Pencocokan langsung case-insensitive
            if ($userLower === $candLower) {
                return true;
            }

            // 2. Pencocokan nilai numerik / mata uang (misal 2500 vs Rp 2.500 vs 2.500 Rupiah)
            $candNumeric = self::parseNumber($candTrimmed);
            if ($userNumeric !== null && $candNumeric !== null) {
                if (abs($userNumeric - $candNumeric) < 0.0001) {
                    return true;
                }
            }

            // 3. Pencocokan teks normal (setelah hapus spasi berlebih, tanda baca tidak penting, awalan/akhiran Rp)
            $candTextNorm = self::normalizeText($candTrimmed);
            if ($userTextNorm !== '' && $userTextNorm === $candTextNorm) {
                return true;
            }

            // 4. Fuzzy text matching untuk essay/isian panjang non-angka (toleransi typo kecil)
            if ($userNumeric === null && $candNumeric === null) {
                $userLen = mb_strlen($userTextNorm);
                $candLen = mb_strlen($candTextNorm);

                if ($userLen >= 4 && $candLen >= 4) {
                    similar_text($userTextNorm, $candTextNorm, $similarityPct);
                    if ($similarityPct >= 90.0) {
                        return true;
                    }

                    // Toleransi 1 karakter typo untuk kata cukup panjang
                    if (strlen($userTextNorm) <= 255 && strlen($candTextNorm) <= 255 && $candLen >= 6) {
                        if (levenshtein($userTextNorm, $candTextNorm) <= 1) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Ekstrak daftar kandidat kunci jawaban dari correct_answer dan accepted_answers.
     */
    public static function extractCandidates(?string $correctAnswer, array|string|null $acceptedAnswers = []): array
    {
        $candidates = [];

        if ($correctAnswer !== null && trim($correctAnswer) !== '') {
            $candidates[] = trim($correctAnswer);
        }

        if ($acceptedAnswers) {
            if (is_string($acceptedAnswers)) {
                $decoded = json_decode($acceptedAnswers, true);
                if (is_array($decoded)) {
                    $acceptedAnswers = $decoded;
                } else {
                    // Split berdasarkan baris baru atau koma/titik-koma
                    $acceptedAnswers = preg_split('/[\r\n,;]+/', $acceptedAnswers);
                }
            }

            if (is_iterable($acceptedAnswers)) {
                foreach ($acceptedAnswers as $ans) {
                    if (is_string($ans)) {
                        $t = trim($ans);
                        if ($t !== '' && !in_array($t, $candidates, true)) {
                            $candidates[] = $t;
                        }
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * Normalisasi teks: lowercase, hilangkan prefix/suffix mata uang,
     * tanda baca yang tidak esensial, dan spasi ganda.
     */
    public static function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text));

        // Hapus tag HTML jika ada
        $text = strip_tags($text);

        // Hapus awalan mata uang
        $text = preg_replace('/^(?:rp\.?|idr)\s*/i', '', $text);

        // Hapus akhiran mata uang dan sen
        $text = preg_replace('/\s*(?:rupiah|idr|rp\.?)$/i', '', $text);
        $text = preg_replace('/[,.]-\s*$/', '', $text);
        $text = preg_replace('/,00(?:\s*-\s*)?$/', '', $text);

        // Bersihkan tanda baca, sisakan huruf dan angka serta spasi
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // Ubah multiple whitespace jadi 1 spasi
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Konversi nilai ke float jika merupakan representasi angka/mata uang yang valid.
     * Mendukung format:
     * - "2500"
     * - "Rp 2.500", "Rp. 2.500", "Rp2500", "Rp 2.500,-", "2.500,-", "2500,-"
     * - "2.500 Rupiah", "2500 rupiah", "IDR 2,500", "IDR 2.500"
     * - "2.500,00", "Rp 2.500,00"
     * - "2,5" (desimal koma) atau "2.5" (desimal titik)
     * - Terbilang Indonesia: "dua ribu lima ratus", "dua ribu lima ratus rupiah", dll.
     */
    public static function parseNumber(string $value): ?float
    {
        $raw = trim($value);
        if ($raw === '') {
            return null;
        }

        // Hapus tag HTML
        $raw = strip_tags($raw);

        // Hapus prefix mata uang
        $clean = preg_replace('/^(?:rp\.?|idr)\s*/i', '', $raw);

        // Hapus suffix mata uang
        $clean = preg_replace('/\s*(?:rupiah|idr|rp\.?)\s*$/i', '', $clean);

        // Hapus suffix sen kosong seperti ,- atau /- atau ,00 atau .00 jika di akhir
        $clean = preg_replace('/[,.]-\s*$/', '', $clean);
        $clean = preg_replace('/,00(?:\s*-\s*)?$/', '', $clean);

        // Hapus unit/satuan umum di akhir (cm, kg, meter, %, dll)
        $clean = preg_replace('/\s*(?:cm|m|km|kg|g|gram|ton|liter|l|ml|buah|butir|lembar|orang|ekor|derajat|°|%)\s*$/i', '', $clean);

        $clean = trim($clean);
        if ($clean === '') {
            return null;
        }

        // Cek apakah berupa terbilang kata (e.g. "dua ribu lima ratus")
        $wordVal = self::wordsToNumber($clean);
        if ($wordVal !== null) {
            return $wordVal;
        }

        // Jika ada tanda minus di depan
        $isNegative = str_starts_with($clean, '-');
        $numPart = ltrim($clean, '-+ ');

        // Pola 1: Format ribuan titik Indonesia dengan desimal koma: "2.500,50" atau "1.000.000,25"
        if (preg_match('/^\d{1,3}(?:\.\d{3})+,\d+$/', $numPart)) {
            $normalized = str_replace('.', '', $numPart);
            $normalized = str_replace(',', '.', $normalized);
            $val = (float) $normalized;
            return $isNegative ? -$val : $val;
        }

        // Pola 2: Format ribuan koma internasional dengan desimal titik: "2,500.50" atau "1,000,000.25"
        if (preg_match('/^\d{1,3}(?:,\d{3})+\.\d+$/', $numPart)) {
            $normalized = str_replace(',', '', $numPart);
            $val = (float) $normalized;
            return $isNegative ? -$val : $val;
        }

        // Pola 3: Format ribuan titik Indonesia tanpa desimal: "2.500", "25.000", "1.000.000"
        if (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $numPart)) {
            $normalized = str_replace('.', '', $numPart);
            $val = (float) $normalized;
            return $isNegative ? -$val : $val;
        }

        // Pola 4: Format ribuan koma internasional tanpa desimal: "2,500", "25,000", "1,000,000"
        if (preg_match('/^\d{1,3}(?:,\d{3})+$/', $numPart)) {
            $normalized = str_replace(',', '', $numPart);
            $val = (float) $normalized;
            return $isNegative ? -$val : $val;
        }

        // Pola 5: Desimal koma sederhana: "2,5" atau "0,75"
        if (preg_match('/^\d+,\d+$/', $numPart)) {
            $normalized = str_replace(',', '.', $numPart);
            $val = (float) $normalized;
            return $isNegative ? -$val : $val;
        }

        // Pola 6: Desimal titik standar atau integer murni: "2500" atau "2.5" atau "0.75"
        if (preg_match('/^\d+(?:\.\d+)?$/', $numPart)) {
            $val = (float) $numPart;
            return $isNegative ? -$val : $val;
        }

        return null;
    }

    /**
     * Konversi kata terbilang bahasa Indonesia ke angka.
     * Contoh: "dua ribu lima ratus" -> 2500
     */
    public static function wordsToNumber(string $text): ?float
    {
        $text = mb_strtolower(trim($text));
        // Ganti tanda baca dengan spasi
        $text = preg_replace('/[^\p{L}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', trim($text));

        if (empty($words)) {
            return null;
        }

        // Cek apakah semua kata adalah kata bilangan yang dikenali
        $knownWords = array_keys(self::WORD_UNITS);
        $multipliers = ['belas', 'puluh', 'ratus', 'ribu', 'juta', 'miliar', 'milyar', 'triliun', 'trilyun', 'koma'];
        $allValid = true;

        foreach ($words as $w) {
            if (!in_array($w, $knownWords, true) && !in_array($w, $multipliers, true)) {
                $allValid = false;
                break;
            }
        }

        if (!$allValid) {
            return null;
        }

        $total = 0.0;
        $current = 0.0;
        $inDecimal = false;
        $decimalMultiplier = 0.1;

        foreach ($words as $w) {
            if ($w === 'koma') {
                $inDecimal = true;
                continue;
            }

            if ($inDecimal) {
                if (isset(self::WORD_UNITS[$w])) {
                    $total += self::WORD_UNITS[$w] * $decimalMultiplier;
                    $decimalMultiplier *= 0.1;
                }
                continue;
            }

            if ($w === 'belas') {
                $current += 10.0;
            } elseif ($w === 'puluh') {
                $current = ($current === 0.0 ? 1.0 : $current) * 10.0;
            } elseif ($w === 'ratus') {
                $current = ($current === 0.0 ? 1.0 : $current) * 100.0;
            } elseif ($w === 'ribu') {
                $current = ($current === 0.0 ? 1.0 : $current) * 1000.0;
                $total += $current;
                $current = 0.0;
            } elseif ($w === 'juta') {
                $current = ($current === 0.0 ? 1.0 : $current) * 1000000.0;
                $total += $current;
                $current = 0.0;
            } elseif ($w === 'miliar' || $w === 'milyar') {
                $current = ($current === 0.0 ? 1.0 : $current) * 1000000000.0;
                $total += $current;
                $current = 0.0;
            } elseif ($w === 'triliun' || $w === 'trilyun') {
                $current = ($current === 0.0 ? 1.0 : $current) * 1000000000000.0;
                $total += $current;
                $current = 0.0;
            } elseif (isset(self::WORD_UNITS[$w])) {
                $val = self::WORD_UNITS[$w];
                if ($val >= 100) {
                    $total += $val;
                } else {
                    $current += $val;
                }
            }
        }

        $result = $total + $current;
        return $result > 0 || in_array('nol', $words, true) || in_array('kosong', $words, true) ? $result : null;
    }
}
