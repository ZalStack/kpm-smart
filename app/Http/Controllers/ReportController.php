<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Package;
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

class ReportController extends Controller
{
    /**
     * Display reports dashboard with filters.
     */
    public function index(Request $request)
    {
        // Get filter values
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'all');
        $paymentStatus = $request->get('payment_status', '');
        $packageId = $request->get('package_id', '');

        // Build query
        $query = Order::with(['user', 'package', 'videoOrder.video'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        // Apply filters
        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($packageId) {
            $query->where('package_id', $packageId);
        }

        // Get data for reports
        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Summary statistics
        $totalRevenue = $transactions->where('payment_status', 'paid')->sum('total_price');
        $totalTransactions = $transactions->count();
        $paidCount = $transactions->where('payment_status', 'paid')->count();
        $pendingCount = $transactions->where('payment_status', 'pending')->count();
        $failedCount = $transactions->where('payment_status', 'failed')->count();

        // Payment method distribution
        $paymentDistribution = $transactions->where('payment_status', 'paid')
            ->groupBy('payment_type')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total_price')
                ];
            });

        // Daily statistics
        $dailyStats = $transactions->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->where('payment_status', 'paid')->sum('total_price')
            ];
        });

        // Top items (paket & video pembahasan)
        $topPackages = $transactions->where('payment_status', 'paid')
            ->groupBy(function ($t) {
                return $t->isVideoOrder() ? 'video-' . $t->video_order_id : 'package-' . $t->package_id;
            })
            ->map(function ($group) {
                return [
                    'package_name' => ($group->first()->isVideoOrder() ? '🎬 ' : '') . $group->first()->item_title,
                    'count' => $group->count(),
                    'revenue' => $group->sum('total_price')
                ];
            })
            ->sortByDesc('revenue')
            ->take(5);

        // User statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $newUsers = User::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->count();

        // Get packages for filter
        $packages = Package::where('is_active', true)->get();

        // Prepare chart data
        $chartData = [
            'labels' => $dailyStats->keys()->map(function ($date) {
                return Carbon::parse($date)->format('d/m');
            })->toArray(),
            'revenue' => $dailyStats->values()->pluck('revenue')->toArray(),
            'count' => $dailyStats->values()->pluck('count')->toArray(),
        ];

        return view('admin.reports.index', compact(
            'transactions',
            'totalRevenue',
            'totalTransactions',
            'paidCount',
            'pendingCount',
            'failedCount',
            'paymentDistribution',
            'dailyStats',
            'topPackages',
            'totalUsers',
            'activeUsers',
            'newUsers',
            'packages',
            'chartData',
            'startDate',
            'endDate',
            'reportType',
            'paymentStatus',
            'packageId'
        ));
    }

    /**
     * Show detailed report for a specific period.
     */
    public function show(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'summary');

        // Get data based on report type
        $data = [];

        switch ($reportType) {
            case 'summary':
                $data = $this->getSummaryReport($startDate, $endDate);
                break;
            case 'transactions':
                $data = $this->getTransactionReport($startDate, $endDate, $request);
                break;
            case 'users':
                $data = $this->getUserReport($startDate, $endDate);
                break;
            case 'packages':
                $data = $this->getPackageReport($startDate, $endDate);
                break;
            default:
                $data = $this->getSummaryReport($startDate, $endDate);
        }

        return view('admin.reports.show', compact('data', 'startDate', 'endDate', 'reportType'));
    }

    /**
     * Get summary report data.
     */
    private function getSummaryReport($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $transactions = Order::with(['user', 'package', 'videoOrder.video'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return [
            'total_revenue' => $transactions->where('payment_status', 'paid')->sum('total_price'),
            'total_transactions' => $transactions->count(),
            'paid_count' => $transactions->where('payment_status', 'paid')->count(),
            'pending_count' => $transactions->where('payment_status', 'pending')->count(),
            'failed_count' => $transactions->where('payment_status', 'failed')->count(),
            'transactions' => $transactions,
            'payment_distribution' => $transactions->where('payment_status', 'paid')
                ->groupBy('payment_type')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('total_price')
                    ];
                }),
        ];
    }

    /**
     * Get transaction report data.
     */
    private function getTransactionReport($startDate, $endDate, $request)
    {
        $query = Order::with(['user', 'package', 'videoOrder.video'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        if ($request->get('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->get('package_id')) {
            $query->where('package_id', $request->get('package_id'));
        }

        return [
            'transactions' => $query->orderBy('created_at', 'desc')->get(),
            'total_revenue' => (clone $query)->where('payment_status', 'paid')->sum('total_price'),
            'total_count' => (clone $query)->count(),
        ];
    }

    /**
     * Get user report data.
     */
    private function getUserReport($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $totalUsers = User::count();
        $activeUsersCount = User::where('is_active', true)->count();
        $newUsersCount = User::whereBetween('created_at', [$start, $end])->count();

        $users = User::withCount('orders')->paginate(50);

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsersCount,
            'new_users' => $newUsersCount,
            'users' => $users,
        ];
    }

    /**
     * Get package report data.
     */
    private function getPackageReport($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $packages = Package::with(['orders' => function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }])->get();

        $packageStats = $packages->map(function ($package) {
            $orders = $package->orders;
            $paidOrders = $orders->where('payment_status', 'paid');

            return [
                'package' => $package,
                'total_orders' => $orders->count(),
                'paid_orders' => $paidOrders->count(),
                'revenue' => $paidOrders->sum('total_price'),
                'pending_orders' => $orders->where('payment_status', 'pending')->count(),
                'failed_orders' => $orders->where('payment_status', 'failed')->count(),
            ];
        });

        return [
            'package_stats' => $packageStats,
            'total_revenue' => $packageStats->sum('revenue'),
            'total_orders' => $packageStats->sum('total_orders'),
        ];
    }

    /**
     * Export report to Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $reportType = $request->get('report_type', 'summary');

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('PKA Litbang')
                ->setLastModifiedBy('PKA Litbang')
                ->setTitle('Laporan ' . ucfirst($reportType))
                ->setSubject('Laporan Sistem')
                ->setDescription('Laporan ' . ucfirst($reportType));

            // Set page setup
            $sheet->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                ->setPaperSize(PageSetup::PAPERSIZE_A4);

            // Header
            $sheet->mergeCells('A1:L1');
            $sheet->setCellValue('A1', 'LAPORAN ' . strtoupper($reportType));
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Date range
            $sheet->mergeCells('A2:L2');
            $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y'));
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 11, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(2)->setRowHeight(25);

            // Export info
            $sheet->mergeCells('A3:L3');
            $sheet->setCellValue('A3', 'Dicetak: ' . Carbon::now()->format('d/m/Y H:i:s'));
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['size' => 10, 'color' => ['rgb' => '9CA3AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(3)->setRowHeight(20);

            $sheet->getRowDimension(4)->setRowHeight(10);

            // Get data based on report type
            $data = $this->getExportData($reportType, $startDate, $endDate, $request);

            // Build table
            $row = 5;
            $headers = $data['headers'];
            $rows = $data['rows'];

            // Set headers
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $col++;
            }

            // Style headers
            $headerRange = 'A' . $row . ':' . $col . $row;
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '161758']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1F2937']]]
            ]);
            $sheet->getRowDimension($row)->setRowHeight(25);
            $row++;

            // Set data
            foreach ($rows as $dataRow) {
                $col = 'A';
                foreach ($dataRow as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }

                // Alternating row colors
                $rowColor = ($row % 2 == 0) ? 'F9FAFB' : 'FFFFFF';
                $sheet->getStyle('A' . $row . ':' . $col . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]]
                ]);

                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;
            }

            // Auto fit columns
            foreach (range('A', $col) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Freeze panes
            $sheet->freezePane('A6');

            // Create and download
            $writer = new Xlsx($spreadsheet);
            $fileName = 'laporan_' . $reportType . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';

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
            Log::error('Export Excel Report error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor data. Silakan coba lagi.');
        }
    }

    /**
     * Get export data based on report type.
     */
    private function getExportData($reportType, $startDate, $endDate, $request)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        switch ($reportType) {
            case 'transactions':
                $query = Order::with(['user', 'package', 'videoOrder.video'])
                    ->whereBetween('created_at', [$start, $end]);

                if ($request->get('payment_status')) {
                    $query->where('payment_status', $request->get('payment_status'));
                }

                if ($request->get('package_id')) {
                    $query->where('package_id', $request->get('package_id'));
                }

                $transactions = $query->orderBy('created_at', 'desc')->get();

                $headers = ['No', 'Order #', 'User', 'Email', 'Package', 'Amount', 'Status', 'Payment Method', 'Payment Date', 'Payment Time', 'Created Date'];
                $rows = [];

                foreach ($transactions as $index => $t) {
                    $paymentTypeMap = [
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Kartu Kredit',
                        'gopay' => 'GoPay',
                        'shopeepay' => 'ShopeePay',
                        'qris' => 'QRIS',
                        'bca_va' => 'BCA VA',
                        'bni_va' => 'BNI VA',
                        'bri_va' => 'BRI VA',
                        'mandiri_va' => 'Mandiri VA',
                    ];
                    $paymentDisplay = $t->payment_type ? ($paymentTypeMap[$t->payment_type] ?? ucfirst(str_replace('_', ' ', $t->payment_type))) : '-';

                    $paymentDate = $t->payment_time ? Carbon::parse($t->payment_time)->format('d/m/Y') : '-';
                    $paymentTime = $t->payment_time ? Carbon::parse($t->payment_time)->format('H:i:s') : '-';

                    $rows[] = [
                        $index + 1,
                        $t->order_number,
                        $t->user?->name ?? '-',
                        $t->user?->email ?? '-',
                        $t->item_title,
                        'Rp ' . number_format($t->total_price, 0, ',', '.'),
                        ucfirst($t->payment_status),
                        $paymentDisplay,
                        $paymentDate,
                        $paymentTime,
                        $t->created_at->format('d/m/Y H:i'),
                    ];
                }
                break;

            case 'users':
                $users = User::withCount('orders')->get();
                $headers = ['No', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Registered', 'Orders Count'];
                $rows = [];

                foreach ($users as $index => $u) {
                    $rows[] = [
                        $index + 1,
                        $u->name,
                        $u->email,
                        $u->phone ?? '-',
                        ucfirst($u->role ?? 'user'),
                        $u->is_active ? 'Active' : 'Inactive',
                        $u->created_at->format('d/m/Y'),
                        $u->orders_count,
                    ];
                }
                break;

            case 'packages':
                $packages = Package::with(['orders' => function ($query) use ($start, $end) {
                    $query->whereBetween('created_at', [$start, $end]);
                }])->get();

                $headers = ['No', 'Package', 'Price', 'Total Orders', 'Paid Orders', 'Revenue', 'Pending', 'Failed'];
                $rows = [];

                foreach ($packages as $index => $p) {
                    $orders = $p->orders;
                    $paidOrders = $orders->where('payment_status', 'paid');

                    $rows[] = [
                        $index + 1,
                        $p->title,
                        'Rp ' . number_format($p->price, 0, ',', '.'),
                        $orders->count(),
                        $paidOrders->count(),
                        'Rp ' . number_format($paidOrders->sum('total_price'), 0, ',', '.'),
                        $orders->where('payment_status', 'pending')->count(),
                        $orders->where('payment_status', 'failed')->count(),
                    ];
                }
                break;

            default: // summary
                $transactions = Order::with(['user', 'package', 'videoOrder.video'])
                    ->whereBetween('created_at', [$start, $end])
                    ->get();

                $headers = ['No', 'Order #', 'User', 'Package', 'Amount', 'Status', 'Payment Method', 'Payment Date', 'Payment Time', 'Created Date'];
                $rows = [];

                foreach ($transactions as $index => $t) {
                    $paymentTypeMap = [
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Kartu Kredit',
                        'gopay' => 'GoPay',
                        'shopeepay' => 'ShopeePay',
                        'qris' => 'QRIS',
                        'bca_va' => 'BCA VA',
                        'bni_va' => 'BNI VA',
                        'bri_va' => 'BRI VA',
                        'mandiri_va' => 'Mandiri VA',
                    ];
                    $paymentDisplay = $t->payment_type ? ($paymentTypeMap[$t->payment_type] ?? ucfirst(str_replace('_', ' ', $t->payment_type))) : '-';

                    $paymentDate = $t->payment_time ? Carbon::parse($t->payment_time)->format('d/m/Y') : '-';
                    $paymentTime = $t->payment_time ? Carbon::parse($t->payment_time)->format('H:i:s') : '-';

                    $rows[] = [
                        $index + 1,
                        $t->order_number,
                        $t->user?->name ?? '-',
                        $t->item_title,
                        'Rp ' . number_format($t->total_price, 0, ',', '.'),
                        ucfirst($t->payment_status),
                        $paymentDisplay,
                        $paymentDate,
                        $paymentTime,
                        $t->created_at->format('d/m/Y H:i'),
                    ];
                }
                break;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Export report to PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $reportType = $request->get('report_type', 'summary');

            // Get data
            $data = $this->getExportData($reportType, $startDate, $endDate, $request);

            // Calculate totals
            $totalRevenue = 0;
            $paidCount = 0;
            $pendingCount = 0;

            if ($reportType === 'transactions' || $reportType === 'summary') {
                $query = Order::whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);

                if ($request->get('payment_status')) {
                    $query->where('payment_status', $request->get('payment_status'));
                }

                $totalRevenue = $query->where('payment_status', 'paid')->sum('total_price');
                $paidCount = $query->where('payment_status', 'paid')->count();
                $pendingCount = $query->where('payment_status', 'pending')->count();
            }

            $pdf = Pdf::loadView('admin.reports.export-pdf', compact(
                'data',
                'reportType',
                'startDate',
                'endDate',
                'totalRevenue',
                'paidCount',
                'pendingCount'
            ));
            $pdf->setPaper('a4', 'landscape');

            $fileName = 'laporan_' . $reportType . '_' . Carbon::now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            Log::error('Export PDF Report error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengekspor PDF. Silakan coba lagi.');
        }
    }
}
