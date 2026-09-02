{{-- admin/reports/export-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ ucfirst($reportType) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { font-size: 10px; color: #0f172a; padding: 15px; background: #fff; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #161758; padding-bottom: 12px; }
        .header h1 { font-size: 18px; color: #161758; font-weight: 700; }
        .header p { font-size: 11px; color: #64748b; margin-top: 4px; }
        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 15px; background: #f1f5f9; padding: 10px; border-radius: 6px; }
        .info-item { text-align: center; }
        .info-item .label { font-size: 8px; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
        .info-item .value { font-size: 12px; font-weight: 600; color: #0f172a; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 9px; }
        table th { background: #161758; color: white; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; font-weight: 600; }
        table td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        table tr:nth-child(even) { background: #f1f5f9; }
        .status-pending { color: #D97706; font-weight: 600; }
        .status-paid { color: #059669; font-weight: 600; }
        .status-failed { color: #DC2626; font-weight: 600; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; }
        .text-center { text-align: center; }
        .text-muted { color: #64748b; }
        .font-mono { font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN {{ strtoupper($reportType) }}</h1>
        <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Dicetak: {{ Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item"><div class="label">Total Data</div><div class="value">{{ count($data['rows']) }}</div></div>
        <div class="info-item"><div class="label">Total Pendapatan</div><div class="value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div></div>
        <div class="info-item"><div class="label">Jenis</div><div class="value">{{ ucfirst($reportType) }}</div></div>
        <div class="info-item"><div class="label">Status</div><div class="value">Paid: {{ $paidCount ?? 0 }} | Pending: {{ $pendingCount ?? 0 }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($data['headers'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td class="{{ in_array($cell, ['Pending', 'Paid', 'Failed']) ? 'status-' . strtolower($cell) : '' }} {{ is_string($cell) && (strpos($cell, ':') !== false || preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $cell)) ? 'font-mono text-center' : '' }}">
                            {{ $cell }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat oleh sistem KPM Belajar Online Membership</p>
        <p>© {{ date('Y') }} KPM Belajar Online. All rights reserved.</p>
    </div>
</body>
</html>
