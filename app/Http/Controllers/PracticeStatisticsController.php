<?php

namespace App\Http\Controllers;

use App\Models\PracticeSession;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PracticeStatisticsController extends Controller
{
    /**
     * Display practice statistics dashboard.
     */
    public function index(Request $request)
    {
        return view('admin.practice-statistics.index');
    }

    /**
     * Show detailed practice session.
     */
    public function show(PracticeSession $session)
    {
        $session->load(['user', 'package', 'order']);
        $results = $session->answers ?? [];

        $hideExplanation = false;
        $timeLimitMinutes = null;
        if ($session->package) {
            $hideExplanation = $session->package->hide_explanation ?? false;
            $timeLimitMinutes = $session->package->time_limit_minutes ?? null;
        }

        return view('admin.practice-statistics.show', compact('session', 'results', 'hideExplanation', 'timeLimitMinutes'));
    }

    /**
     * Export practice statistics to Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $packageId = $request->get('package_id', '');
            $status = $request->get('status', '');

            $query = PracticeSession::with(['user', 'package'])
                ->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);

            if ($packageId) {
                $query->where('package_id', $packageId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $sessions = $query->orderBy('created_at', 'desc')->get();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('PKA Litbang')
                ->setLastModifiedBy('PKA Litbang')
                ->setTitle('Laporan Aktivitas Pengerjaan Soal')
                ->setSubject('Practice Statistics Export')
                ->setDescription('Data aktivitas pengerjaan soal');

            // Set page setup
            $sheet->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

            // Header
            $sheet->setCellValue('A1', 'LAPORAN AKTIVITAS PENGERJAAN SOAL');
            $sheet->mergeCells('A1:L1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Date range
            $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y'));
            $sheet->mergeCells('A2:L2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 11, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);

            // Export info
            $sheet->setCellValue('A3', 'Dicetak: ' . Carbon::now()->format('d/m/Y H:i:s') . ' | Total: ' . $sessions->count() . ' sesi');
            $sheet->mergeCells('A3:L3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '9CA3AF']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);

            $sheet->getRowDimension(4)->setRowHeight(10);

            // Headers
            $headers = [
                'A' => 'NO',
                'B' => 'USER',
                'C' => 'EMAIL',
                'D' => 'PAKET',
                'E' => 'CARD',
                'F' => 'STATUS',
                'G' => 'TOTAL SOAL',
                'H' => 'BENAR',
                'I' => 'SALAH',
                'J' => 'TIDAK DIJAWAB',
                'K' => 'NILAI',
                'L' => 'TANGGAL'
            ];

            $headerRow = 5;
            foreach ($headers as $col => $value) {
                $sheet->setCellValue($col . $headerRow, $value);
            }

            $headerStyle = [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '161758']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);
            $sheet->getRowDimension($headerRow)->setRowHeight(25);

            // Data rows
            $row = 6;
            $no = 1;

            $columnWidths = [
                'A' => 6, 'B' => 22, 'C' => 28, 'D' => 25,
                'E' => 15, 'F' => 14, 'G' => 12, 'H' => 12,
                'I' => 12, 'J' => 14, 'K' => 12, 'L' => 18
            ];

            foreach ($columnWidths as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            foreach ($sessions as $session) {
                $statusColors = [
                    'completed' => 'D1FAE5',
                    'in_progress' => 'FEF3C7',
                    'cancelled' => 'FEE2E2'
                ];

                $statusTextColors = [
                    'completed' => '059669',
                    'in_progress' => 'D97706',
                    'cancelled' => 'DC2626'
                ];

                $statusColor = $statusColors[$session->status] ?? 'F3F4F6';
                $statusTextColor = $statusTextColors[$session->status] ?? '374151';

                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $session->user?->name ?? '-');
                $sheet->setCellValue('C' . $row, $session->user?->email ?? '-');
                $sheet->setCellValue('D' . $row, $session->package?->title ?? 'Paket Dihapus');
                $sheet->setCellValue('E' . $row, $session->card_id ?? '-');
                $sheet->setCellValue('F' . $row, ucfirst(str_replace('_', ' ', $session->status)));
                $sheet->setCellValue('G' . $row, $session->total_question);
                $sheet->setCellValue('H' . $row, $session->correct_answer);
                $sheet->setCellValue('I' . $row, $session->wrong_answer);
                $sheet->setCellValue('J' . $row, $session->unanswered);
                $sheet->setCellValue('K' . $row, $session->total_score);
                $sheet->setCellValue('L' . $row, $session->created_at->format('d/m/Y H:i'));

                // Style for status
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColor]],
                    'font' => ['color' => ['rgb' => $statusTextColor], 'bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                // Alternating row colors
                $rowColor = ($row % 2 == 0) ? 'F9FAFB' : 'FFFFFF';
                $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowColor]],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);

                // Center alignment for numeric columns
                $centerColumns = ['A', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
                foreach ($centerColumns as $col) {
                    $sheet->getStyle($col . $row)->applyFromArray([
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;
                $no++;
            }

            // Footer - Summary
            $footerRow = $row + 2;
            $sheet->setCellValue('A' . $footerRow, 'RINGKASAN');
            $sheet->mergeCells('A' . $footerRow . ':L' . $footerRow);
            $sheet->getStyle('A' . $footerRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F2937']]
            ]);
            $footerRow++;

            $summaryData = [
                ['Total Sesi', $sessions->count()],
                ['Total User', $sessions->unique('user_id')->count()],
                ['Total Paket', $sessions->unique('package_id')->count()],
                ['Total Soal Dikerjakan', $sessions->sum('total_question')],
                ['Total Jawaban Benar', $sessions->sum('correct_answer')],
                ['Rata-rata Nilai', number_format($sessions->where('status', 'completed')->avg('total_score') ?? 0, 2)],
            ];

            foreach ($summaryData as $index => $data) {
                $currentRow = $footerRow + $index;
                $sheet->setCellValue('A' . $currentRow, $data[0]);
                $sheet->setCellValue('B' . $currentRow, $data[1]);
                $sheet->getStyle('A' . $currentRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '374151']]
                ]);
            }

            $sheet->setAutoFilter('A5:L5');
            $sheet->freezePane('A6');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'aktivitas_pengerjaan_soal_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            $callback = function () use ($writer) {
                $writer->save('php://output');
            };

            return new \Symfony\Component\HttpFoundation\StreamedResponse($callback, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0, must-revalidate',
                'Pragma' => 'public',
            ]);

        } catch (\Exception $e) {
            Log::error('Export Practice Statistics Excel error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data. Silakan coba lagi.');
        }
    }

    /**
     * Export practice statistics to PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $packageId = $request->get('package_id', '');
            $status = $request->get('status', '');

            $query = PracticeSession::with(['user', 'package'])
                ->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);

            if ($packageId) {
                $query->where('package_id', $packageId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $sessions = $query->orderBy('created_at', 'desc')->get();

            $totalSessions = $sessions->count();
            $totalUsers = $sessions->unique('user_id')->count();
            $totalPackages = $sessions->unique('package_id')->count();
            $totalQuestions = $sessions->sum('total_question');
            $totalCorrect = $sessions->sum('correct_answer');
            $avgScore = number_format($sessions->where('status', 'completed')->avg('total_score') ?? 0, 2);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.practice-statistics.export-pdf', compact(
                'sessions',
                'startDate',
                'endDate',
                'totalSessions',
                'totalUsers',
                'totalPackages',
                'totalQuestions',
                'totalCorrect',
                'avgScore'
            ));
            $pdf->setPaper('a4', 'landscape');

            $fileName = 'aktivitas_pengerjaan_soal_' . Carbon::now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor PDF. Silakan coba lagi.');
        }
    }
}
