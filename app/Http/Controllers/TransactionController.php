<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Package;
use App\Services\MidtransPaymentService;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package', 'videoOrder.video']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where(function($q) use ($escapedSearch) {
                $q->where('order_number', 'LIKE', "%{$escapedSearch}%")
                  ->orWhereHas('user', function($userQuery) use ($escapedSearch) {
                      $userQuery->where('name', 'LIKE', "%{$escapedSearch}%")
                                ->orWhere('email', 'LIKE', "%{$escapedSearch}%");
                  });
            });
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = in_array(strtolower((string) $request->get('sort_order', 'desc')), ['asc', 'desc'], true) ? strtolower($request->get('sort_order', 'desc')) : 'desc';
        $allowedSorts = ['order_number', 'total_price', 'payment_status', 'created_at', 'payment_time'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $transactions = $query->paginate(10)->withQueryString();

        $stats = [
            'total_transactions' => Order::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_price'),
            'pending_count' => Order::where('payment_status', 'pending')->count(),
            'paid_count' => Order::where('payment_status', 'paid')->count(),
            'failed_count' => Order::where('payment_status', 'failed')->count(),
        ];

        // Ambil semua payment types yang ada di database (termasuk null)
        $paymentTypes = Order::select('payment_type')
            ->distinct()
            ->whereNotNull('payment_type')
            ->pluck('payment_type')
            ->filter();

        $packages = Package::where('is_active', true)->get();

        return view('admin.transactions.index', compact(
            'transactions',
            'stats',
            'paymentTypes',
            'packages',
            'request'
        ));
    }

    public function show(Order $transaction)
    {
        $transaction->load(['user', 'package', 'videoOrder.video', 'practiceSessions']);

        $relatedTransactions = Order::where('user_id', $transaction->user_id)
            ->where('id', '!=', $transaction->id)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.transactions.show', compact('transaction', 'relatedTransactions'));
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = Order::with(['user', 'package', 'videoOrder.video']);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('payment_type')) {
                $query->where('payment_type', $request->payment_type);
            }

            $transactions = $query->orderBy('created_at', 'desc')->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getProperties()
                ->setCreator('PKA Litbang')
                ->setLastModifiedBy('PKA Litbang')
                ->setTitle('Transactions Report')
                ->setSubject('Transactions Export')
                ->setDescription('Transaction data export')
                ->setKeywords('transactions, export, excel');

            $sheet->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(PageSetup::PAPERSIZE_A4);

            // Header
            $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI');
            $sheet->mergeCells('A1:M1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(30);

            $dateRange = 'Periode: ';
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $dateRange .= Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');
            } else {
                $dateRange .= 'Semua Data';
            }
            $sheet->setCellValue('A2', $dateRange);
            $sheet->mergeCells('A2:M2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 11, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(2)->setRowHeight(25);

            $exportInfo = 'Dicetak: ' . Carbon::now()->format('d/m/Y H:i:s') . ' | Total: ' . $transactions->count() . ' transaksi';
            $sheet->setCellValue('A3', $exportInfo);
            $sheet->mergeCells('A3:M3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '9CA3AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(3)->setRowHeight(20);

            $sheet->getRowDimension(4)->setRowHeight(10);

            // Headers dengan tambahan kolom Payment Date dan Payment Time
            $headers = [
                'A' => 'NO',
                'B' => 'ORDER NUMBER',
                'C' => 'NAMA USER',
                'D' => 'EMAIL',
                'E' => 'PAKET',
                'F' => 'TOTAL HARGA',
                'G' => 'STATUS',
                'H' => 'METODE PEMBAYARAN',
                'I' => 'TRANSACTION ID',
                'J' => 'TANGGAL PEMBAYARAN',
                'K' => 'WAKTU PEMBAYARAN',
                'L' => 'TANGGAL DIBUAT',
                'M' => 'WAKTU DIBUAT'
            ];

            $headerRow = 5;
            foreach ($headers as $col => $value) {
                $sheet->setCellValue($col . $headerRow, $value);
            }

            $headerStyle = [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '161758']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1F2937']]]
            ];
            $sheet->getStyle('A5:M5')->applyFromArray($headerStyle);
            $sheet->getRowDimension($headerRow)->setRowHeight(25);

            // Data rows
            $row = 6;
            $no = 1;

            $columnWidths = [
                'A' => 6, 'B' => 18, 'C' => 22, 'D' => 28,
                'E' => 25, 'F' => 16, 'G' => 14, 'H' => 20,
                'I' => 22, 'J' => 16, 'K' => 14, 'L' => 16, 'M' => 14
            ];

            foreach ($columnWidths as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            foreach ($transactions as $transaction) {
                $statusColors = [
                    'pending' => 'FEF3C7',
                    'paid' => 'D1FAE5',
                    'failed' => 'FEE2E2'
                ];

                $statusTextColors = [
                    'pending' => 'D97706',
                    'paid' => '059669',
                    'failed' => 'DC2626'
                ];

                $statusColor = $statusColors[$transaction->payment_status] ?? 'F3F4F6';
                $statusTextColor = $statusTextColors[$transaction->payment_status] ?? '374151';

                $paymentMethodDisplay = MidtransPaymentService::methodLabel($transaction->payment_type);

                $paymentDate = $transaction->payment_time ? Carbon::parse($transaction->payment_time)->format('d/m/Y') : '-';
                $paymentTime = $transaction->payment_time ? Carbon::parse($transaction->payment_time)->format('H:i:s') : '-';

                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $transaction->order_number);
                $sheet->setCellValue('C' . $row, $transaction->user?->name ?? '-');
                $sheet->setCellValue('D' . $row, $transaction->user?->email ?? '-');
                $sheet->setCellValue('E' . $row, $transaction->item_title);
                $sheet->setCellValue('F' . $row, $transaction->total_price);
                $sheet->setCellValue('G' . $row, ucfirst($transaction->payment_status));
                $sheet->setCellValue('H' . $row, $paymentMethodDisplay);
                $sheet->setCellValue('I' . $row, $transaction->transaction_id ?? '-');
                $sheet->setCellValue('J' . $row, $paymentDate);
                $sheet->setCellValue('K' . $row, $paymentTime);
                $sheet->setCellValue('L' . $row, $transaction->created_at->format('d/m/Y'));
                $sheet->setCellValue('M' . $row, $transaction->created_at->format('H:i:s'));

                // Style untuk status
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColor]],
                    'font' => ['color' => ['rgb' => $statusTextColor], 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);

                // Style untuk price
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'numberFormat' => ['formatCode' => '#,##0'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);

                // Alternating row colors
                $rowColor = ($row % 2 == 0) ? 'F9FAFB' : 'FFFFFF';
                $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]]
                ]);

                // Center alignment untuk kolom tertentu
                $centerColumns = ['A', 'B', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
                foreach ($centerColumns as $col) {
                    $sheet->getStyle($col . $row)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                    ]);
                }

                // Left alignment untuk text columns
                $leftColumns = ['C', 'D', 'E'];
                foreach ($leftColumns as $col) {
                    $sheet->getStyle($col . $row)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
                    ]);
                }

                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;
                $no++;
            }

            // Footer
            $footerRow = $row + 2;

            $sheet->setCellValue('A' . $footerRow, 'RINGKASAN');
            $sheet->mergeCells('A' . $footerRow . ':M' . $footerRow);
            $sheet->getStyle('A' . $footerRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension($footerRow)->setRowHeight(25);

            $footerRow++;

            $summaryData = [
                ['Total Transaksi', $transactions->count()],
                ['Total Pendapatan', 'Rp ' . number_format($transactions->where('payment_status', 'paid')->sum('total_price'), 0, ',', '.')],
                ['Status Pending', $transactions->where('payment_status', 'pending')->count()],
                ['Status Paid', $transactions->where('payment_status', 'paid')->count()],
                ['Status Failed', $transactions->where('payment_status', 'failed')->count()],
            ];

            foreach ($summaryData as $index => $data) {
                $currentRow = $footerRow + $index;
                $sheet->setCellValue('A' . $currentRow, $data[0]);
                $sheet->setCellValue('B' . $currentRow, $data[1]);

                $sheet->getStyle('A' . $currentRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '374151']]
                ]);

                $sheet->getStyle('B' . $currentRow)->applyFromArray([
                    'font' => ['color' => ['rgb' => '1F2937']]
                ]);
            }

            $sheet->setAutoFilter('A5:M5');
            $sheet->freezePane('A6');

            $writer = new Xlsx($spreadsheet);
            $fileName = 'transactions_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            $callback = function () use ($writer) {
                $writer->save('php://output');
            };

            return new StreamedResponse($callback, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0, must-revalidate',
                'Pragma' => 'public',
            ]);

        } catch (\Exception $e) {
            Log::error('Export Excel error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Gagal mengekspor data. Silakan coba lagi.');
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = Order::with(['user', 'package', 'videoOrder.video']);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            if ($request->filled('payment_type')) {
                $query->where('payment_type', $request->payment_type);
            }

            $transactions = $query->orderBy('created_at', 'desc')->get();

            $pdf = Pdf::loadView('admin.transactions.export-pdf', compact('transactions'));
            $pdf->setPaper('a4', 'landscape');

            $fileName = 'transactions_' . Carbon::now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Export PDF error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor PDF. Silakan coba lagi.');
        }
    }

    public function stats(Request $request)
    {
        try {
            $period = $request->get('period', 'month');

            $stats = [];

            switch ($period) {
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    $format = 'D';
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $format = 'd M';
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    $format = 'M';
                    break;
                default:
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $format = 'd M';
            }

            $dailyData = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_price) as total_revenue')
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $paymentDistribution = Order::where('payment_status', 'paid')
                ->select('payment_type', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_type')
                ->get();

            $statusDistribution = Order::select('payment_status', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_status')
                ->get();

            $monthSql = DB::getDriverName() === 'sqlite'
                ? "strftime('%Y-%m', created_at)"
                : 'DATE_FORMAT(created_at, "%Y-%m")';

            $monthlyTrend = Order::where('payment_status', 'paid')
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->select(
                    DB::raw("{$monthSql} as month"),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total_price) as total_revenue')
                )
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'daily' => $dailyData,
                    'payment_distribution' => $paymentDistribution,
                    'status_distribution' => $statusDistribution,
                    'monthly_trend' => $monthlyTrend,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data statistik'
            ], 500);
        }
    }
}
