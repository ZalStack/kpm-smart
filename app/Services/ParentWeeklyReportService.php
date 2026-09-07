<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PDO;

class ParentWeeklyReportService
{
    /**
     * Kirim progres hasil belajar mingguan PR / Latihan Soal ke Web Induk (kpm-student-smart)
     *
     * @param string $email Email siswa
     * @param int $score Nilai latihan/PR (0-100)
     * @param bool $completed Status selesai
     * @param string|null $notes Catatan
     */
    public static function report(string $email, int $score, bool $completed = true, ?string $notes = null): bool
    {
        $weekNumber = (int) date('W');
        $year = (string) date('Y');

        // 1. Coba via API HTTP jika parent app hidup
        $baseUrl = rtrim(env('PARENT_APP_URL', 'http://localhost:8000'), '/');
        try {
            $response = Http::timeout(3)->post("{$baseUrl}/api/v1/weekly-reports/update-progress", [
                'email' => $email,
                'source' => 'pr',
                'score' => $score,
                'completed' => $completed,
                'week_number' => $weekNumber,
                'year' => $year,
                'notes' => $notes,
            ]);

            if ($response->successful()) {
                return true;
            }
        } catch (\Throwable $e) {
            // API gagal, lanjut ke fallback DB langsung
        }

        // 2. Fallback: Langsung update database SQLite kpm-student-smart
        $dbPath = 'c:/laragon/www/kpm-student-smart/database/database.sqlite';
        if (!file_exists($dbPath)) {
            return false;
        }

        try {
            $pdo = new PDO("sqlite:{$dbPath}");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cari student_id dari email user
            $stmt = $pdo->prepare("
                SELECT s.id FROM students s
                JOIN users u ON u.id = s.user_id
                WHERE u.email = :email
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);
            $studentId = $stmt->fetchColumn();

            if (!$studentId) {
                return false;
            }

            $stmt = $pdo->prepare("
                INSERT INTO weekly_reports (student_id, week_number, year, pr_completed, pr_score, notes, created_at, updated_at)
                VALUES (:student_id, :week_number, :year, :completed, :score, :notes, :now, :now)
                ON CONFLICT(student_id, week_number) DO UPDATE SET
                    year = excluded.year,
                    pr_completed = excluded.pr_completed,
                    pr_score = excluded.pr_score,
                    notes = CASE WHEN weekly_reports.notes IS NULL THEN excluded.notes ELSE weekly_reports.notes || '\n' || excluded.notes END,
                    updated_at = excluded.updated_at
            ");

            $now = date('Y-m-d H:i:s');
            $stmt->execute([
                ':student_id' => $studentId,
                ':week_number' => $weekNumber,
                ':year' => $year,
                ':completed' => $completed ? 1 : 0,
                ':score' => $score,
                ':notes' => $notes,
                ':now' => $now,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('ParentWeeklyReportService PR fallback error: ' . $e->getMessage());
            return false;
        }
    }
}
