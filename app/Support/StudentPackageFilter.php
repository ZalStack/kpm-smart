<?php

namespace App\Support;

use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentPackageFilter
{
    /**
     * Dapatkan informasi bidang & role siswa.
     */
    public static function getBidangInfo($user): array
    {
        $isStudent = $user && ($user->role === 'user');
        $studentBidang = $user ? trim((string) $user->bidang) : '';
        $studentLevel  = $user ? trim((string) $user->level) : '';
        $studentClass  = $user ? trim((string) $user->student_class) : '';
        $combinedInfo  = $studentBidang . ' ' . $studentLevel;

        $isStudentMtk = (bool) preg_match('/(mtk|matematika|math)/i', $combinedInfo);
        $isStudentIpa = (bool) preg_match('/(ipa|sains|science)/i', $combinedInfo);

        $isStudentDual = ($isStudentMtk && $isStudentIpa)
            || (bool) preg_match('/(&|,|dan|\/)/i', $studentBidang)
            || str_contains(strtolower($studentBidang), 'dual')
            || str_contains(strtolower($studentBidang), 'semua');

        if ($isStudent && $isStudentDual) {
            $userBidangGroup = 'Matematika & IPA';
            $allBidang = collect(['Matematika', 'IPA']);
        } elseif ($isStudent && $isStudentMtk && ! $isStudentIpa) {
            $userBidangGroup = 'Matematika';
            $allBidang = collect(['Matematika']);
        } elseif ($isStudent && $isStudentIpa && ! $isStudentMtk) {
            $userBidangGroup = 'IPA';
            $allBidang = collect(['IPA']);
        } else {
            $userBidangGroup = null;
            $allBidang = Package::where('is_active', true)
                ->whereNotNull('bidang')
                ->where('bidang', '!=', '')
                ->distinct()
                ->pluck('bidang')
                ->sort()
                ->values();
        }

        return [
            'isStudent' => $isStudent,
            'isStudentMtk' => $isStudentMtk,
            'isStudentIpa' => $isStudentIpa,
            'isStudentDual' => $isStudentDual,
            'studentBidang' => $studentBidang,
            'studentLevel' => $studentLevel,
            'studentClass' => $studentClass,
            'userBidangGroup' => $userBidangGroup,
            'allBidang' => $allBidang,
        ];
    }

    /**
     * Terapkan filter paket PR (Bidang, Kelas, Level) pada query builder.
     */
    public static function apply($query, $user, ?string $bidangFilter = null, ?string $kelasFilter = null, ?string $levelFilter = null): void
    {
        $info = self::getBidangInfo($user);
        $isStudent = $info['isStudent'];

        // 1. FILTER BIDANG
        if ($isStudent) {
            if ($info['isStudentDual']) {
                if (! empty($bidangFilter) && strtolower($bidangFilter) !== 'semua') {
                    if (preg_match('/(mtk|matematika|math)/i', $bidangFilter)) {
                        $query->where(function ($q) {
                            $q->where(function ($sub) {
                                $sub->where('bidang', 'like', '%MTK%')
                                    ->orWhere('bidang', 'like', '%Matematika%')
                                    ->orWhere('bidang', 'like', '%Math%')
                                    ->orWhere('title', 'like', '%MTK%')
                                    ->orWhere('title', 'like', '%Matematika%')
                                    ->orWhere('title', 'like', '%Math%');
                            })->where(function ($sub) {
                                $sub->whereNull('bidang')->orWhere('bidang', 'not like', '%IPA%');
                            })->where('title', 'not like', '%IPA%');
                        });
                    } elseif (preg_match('/(ipa|sains|science)/i', $bidangFilter)) {
                        $query->where(function ($q) {
                            $q->where(function ($sub) {
                                $sub->where('bidang', 'like', '%IPA%')
                                    ->orWhere('bidang', 'like', '%Sains%')
                                    ->orWhere('bidang', 'like', '%Science%')
                                    ->orWhere('title', 'like', '%IPA%')
                                    ->orWhere('title', 'like', '%Sains%')
                                    ->orWhere('title', 'like', '%Science%');
                            })->where(function ($sub) {
                                $sub->whereNull('bidang')
                                    ->orWhere(function ($s2) {
                                        $s2->where('bidang', 'not like', '%MTK%')
                                           ->where('bidang', 'not like', '%Matematika%');
                                    });
                            })->where('title', 'not like', '%Matematika%')
                              ->where('title', 'not like', '%MTK%');
                        });
                    } else {
                        $query->where('bidang', $bidangFilter);
                    }
                } else {
                    // Siswa dual tanpa filter spesifik: ambil paket Matematika, IPA, Matematika & IPA, serta paket umum
                    $query->where(function ($q) {
                        $q->whereNull('bidang')
                          ->orWhere('bidang', '')
                          ->orWhere('bidang', 'like', '%MTK%')
                          ->orWhere('bidang', 'like', '%Matematika%')
                          ->orWhere('bidang', 'like', '%Math%')
                          ->orWhere('bidang', 'like', '%IPA%')
                          ->orWhere('bidang', 'like', '%Sains%')
                          ->orWhere('bidang', 'like', '%Science%')
                          ->orWhere('title', 'like', '%MTK%')
                          ->orWhere('title', 'like', '%Matematika%')
                          ->orWhere('title', 'like', '%IPA%')
                          ->orWhere('title', 'like', '%Sains%');
                    });
                }
            } elseif ($info['isStudentMtk'] && ! $info['isStudentIpa']) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('bidang', 'like', '%MTK%')
                            ->orWhere('bidang', 'like', '%Matematika%')
                            ->orWhere('bidang', 'like', '%Math%')
                            ->orWhere('title', 'like', '%MTK%')
                            ->orWhere('title', 'like', '%Matematika%')
                            ->orWhere('title', 'like', '%Math%');
                    })->where(function ($sub) {
                        $sub->whereNull('bidang')
                            ->orWhere('bidang', 'not like', '%IPA%');
                    })->where('title', 'not like', '%IPA%');
                });
            } elseif ($info['isStudentIpa'] && ! $info['isStudentMtk']) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('bidang', 'like', '%IPA%')
                            ->orWhere('bidang', 'like', '%Sains%')
                            ->orWhere('bidang', 'like', '%Science%')
                            ->orWhere('title', 'like', '%IPA%')
                            ->orWhere('title', 'like', '%Sains%')
                            ->orWhere('title', 'like', '%Science%');
                    })->where(function ($sub) {
                        $sub->whereNull('bidang')
                            ->orWhere(function ($s2) {
                                $s2->where('bidang', 'not like', '%MTK%')
                                    ->where('bidang', 'not like', '%Matematika%');
                            });
                    })->where('title', 'not like', '%Matematika%')
                        ->where('title', 'not like', '%MTK%');
                });
            }
        } elseif (! empty($bidangFilter) && strtolower($bidangFilter) !== 'semua') {
            $query->where('bidang', $bidangFilter);
        }

        // 2. FILTER KELAS
        if ($isStudent && ! empty($info['studentClass'])) {
            $rawClass = $info['studentClass'];
            $cleanClass = trim(str_replace(["'", '"'], '', $rawClass));
            $numClass = preg_replace('/[^0-9]/', '', $cleanClass);

            // Normalisasi Romawi (VII -> 7, dsb) jika tidak ada digit eksplisit
            if (empty($numClass)) {
                $romanMap = [
                    'XII' => '12', 'XI' => '11', 'X' => '10',
                    'IX' => '9', 'VIII' => '8', 'VII' => '7',
                    'VI' => '6', 'V' => '5', 'IV' => '4',
                    'III' => '3', 'II' => '2', 'I' => '1',
                ];
                $romanUpper = strtoupper($cleanClass);
                foreach ($romanMap as $r => $n) {
                    if ($romanUpper === $r || str_contains($romanUpper, ' ' . $r) || str_starts_with($romanUpper, $r . '-') || str_ends_with($romanUpper, '-' . $r)) {
                        $numClass = $n;
                        break;
                    }
                }
            }

            $query->where(function ($q) use ($rawClass, $cleanClass, $numClass) {
                $q->whereNull('kelas')
                  ->orWhere('kelas', '')
                  ->orWhere('kelas', $rawClass)
                  ->orWhere('kelas', $cleanClass)
                  ->orWhere('kelas', 'Kelas ' . $cleanClass)
                  ->orWhere('kelas', "'" . $cleanClass . "'");

                if (! empty($numClass)) {
                    $q->orWhere('kelas', $numClass)
                      ->orWhere('kelas', 'Kelas ' . $numClass)
                      ->orWhere('kelas', "'" . $numClass . "'");
                }
            });
        } elseif (! $isStudent && ! empty($kelasFilter) && strtolower($kelasFilter) !== 'semua') {
            $query->where('kelas', $kelasFilter);
        }

        // 3. FILTER LEVEL BERBAKAT
        if ($isStudent && ! empty($info['studentLevel'])) {
            $rawLevel = $info['studentLevel'];
            self::applyLevelFilter($query, $rawLevel);
        } elseif (! $isStudent && ! empty($levelFilter) && strtolower($levelFilter) !== 'semua') {
            $query->where(function ($q) use ($levelFilter) {
                $q->where('level', $levelFilter);
                if (preg_match('/Berbakat\s*([A-C][0-9]?)/i', $levelFilter, $m)) {
                    $lvl = $m[1];
                    $q->orWhere('level', 'like', "%Berbakat {$lvl}%");
                }
            });
        }
    }

    /**
     * Terapkan pencocokan level yang cerdas untuk siswa (termasuk siswa dual dengan level per bidang).
     */
    protected static function applyLevelFilter($query, string $studentLevel): void
    {
        $cleanLevel = trim($studentLevel);

        // Deteksi apakah siswa memiliki spesifikasi level per bidang,
        // misalnya: "Berbakat A (MTK), Berbakat B (IPA)" atau "Level 1 (MTK), Level 2 (IPA)"
        $mtkLevelTag = null;
        $ipaLevelTag = null;

        if (preg_match('/(?:Berbakat|Level)?\s*([A-C][0-9]?|[0-9]+)\s*\((?:MTK|Matematika|Math)\)/i', $cleanLevel, $mMtk)) {
            $mtkLevelTag = trim($mMtk[1]);
        } elseif (preg_match('/(?:MTK|Matematika|Math)\s*[:\-]?\s*(?:Berbakat|Level)?\s*([A-C][0-9]?|[0-9]+)/i', $cleanLevel, $mMtk)) {
            $mtkLevelTag = trim($mMtk[1]);
        }

        if (preg_match('/(?:Berbakat|Level)?\s*([A-C][0-9]?|[0-9]+)\s*\((?:IPA|Sains|Science)\)/i', $cleanLevel, $mIpa)) {
            $ipaLevelTag = trim($mIpa[1]);
        } elseif (preg_match('/(?:IPA|Sains|Science)\s*[:\-]?\s*(?:Berbakat|Level)?\s*([A-C][0-9]?|[0-9]+)/i', $cleanLevel, $mIpa)) {
            $ipaLevelTag = trim($mIpa[1]);
        }

        // Jika ada spesifikasi level terpisah per bidang
        if ($mtkLevelTag !== null || $ipaLevelTag !== null) {
            $mtkVariants = $mtkLevelTag ? self::generateLevelVariants($mtkLevelTag) : [];
            $ipaVariants = $ipaLevelTag ? self::generateLevelVariants($ipaLevelTag) : [];

            $query->where(function ($q) use ($mtkVariants, $ipaVariants, $cleanLevel) {
                // Paket umum tanpa level selalu boleh
                $q->whereNull('level')->orWhere('level', '');

                // Paket MTK
                if (! empty($mtkVariants)) {
                    $q->orWhere(function ($sub) use ($mtkVariants) {
                        $sub->where(function ($b) {
                            $b->where('bidang', 'like', '%MTK%')
                              ->orWhere('bidang', 'like', '%Matematika%')
                              ->orWhere('title', 'like', '%MTK%')
                              ->orWhere('title', 'like', '%Matematika%');
                        })->whereIn('level', $mtkVariants);
                    });
                }

                // Paket IPA
                if (! empty($ipaVariants)) {
                    $q->orWhere(function ($sub) use ($ipaVariants) {
                        $sub->where(function ($b) {
                            $b->where('bidang', 'like', '%IPA%')
                              ->orWhere('bidang', 'like', '%Sains%')
                              ->orWhere('title', 'like', '%IPA%')
                              ->orWhere('title', 'like', '%Sains%');
                        })->whereIn('level', $ipaVariants);
                    });
                }

                // Fallback exact match
                $q->orWhere('level', $cleanLevel);
            });

            return;
        }

        // Jika tidak ada spesifikasi per bidang, ekstrak seluruh token level yang ada
        // (bisa satu level "Berbakat A", atau multi level "Berbakat A, Berbakat B", "1, 2", "Level 1 & Level 2")
        $allVariants = [];

        // Ekstrak token huruf seperti A, B, B1, B2, C
        if (preg_match_all('/(?:Berbakat|Level)?\s*([A-C][0-9]?)/i', $cleanLevel, $matchesLetters)) {
            foreach ($matchesLetters[1] as $tok) {
                if (! empty($tok)) {
                    $allVariants = array_merge($allVariants, self::generateLevelVariants($tok));
                }
            }
        }

        // Ekstrak token angka seperti 1, 2, 3
        if (preg_match_all('/(?:Berbakat|Level)?\s*([0-9]+)/i', $cleanLevel, $matchesNumbers)) {
            foreach ($matchesNumbers[1] as $tok) {
                if (! empty($tok)) {
                    $allVariants = array_merge($allVariants, self::generateLevelVariants($tok));
                }
            }
        }

        $allVariants = array_values(array_unique(array_filter($allVariants)));

        $query->where(function ($q) use ($cleanLevel, $allVariants) {
            $q->whereNull('level')
              ->orWhere('level', '')
              ->orWhere('level', $cleanLevel);

            if (! empty($allVariants)) {
                $q->orWhereIn('level', $allVariants);
            }
        });
    }

    /**
     * Hasilkan variasi nama level (contoh: 'A' -> 'Berbakat A', 'Level A', 'A').
     */
    public static function generateLevelVariants(string $token): array
    {
        $tok = trim($token);
        if ($tok === '') return [];

        $upper = strtoupper($tok);
        $variants = [
            $tok,
            $upper,
            'Berbakat ' . $upper,
            'Level ' . $upper,
            'Tingkat ' . $upper,
        ];

        // Jika B1 atau B2, cocokkan juga dengan Berbakat B
        if (preg_match('/^([A-C])[0-9]$/i', $upper, $m)) {
            $base = $m[1];
            $variants[] = 'Berbakat ' . $base;
            $variants[] = 'Level ' . $base;
            $variants[] = $base;
        }

        return array_values(array_unique($variants));
    }
}
