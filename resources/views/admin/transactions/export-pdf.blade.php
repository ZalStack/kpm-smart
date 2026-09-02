{{-- admin/transactions/export-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 8px; }
        table th { background: #161758; color: white; padding: 5px 6px; text-align: left; font-size: 7px; text-transform: uppercase; font-weight: 600; }
        table td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8px; }
        table tr:nth-child(even) { background: #f1f5f9; }
        .status-pending { color: #D97706; font-weight: 600; }
        .status-paid { color: #059669; font-weight: 600; }
        .status-failed { color: #DC2626; font-weight: 600; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; }
        .text-center { text-align: center; }
        .text-muted { color: #64748b; }
        .font-mono { font-family: 'Courier New', monospace; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: 600; }
        .badge-pending { background: #FEF3C7; color: #D97706; }
        .badge-paid { background: #D1FAE5; color: #059669; }
        .badge-failed { background: #FEE2E2; color: #DC2626; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TRANSAKSI</h1>
        <p>Periode: {{ request('start_date') ? Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') : 'Semua' }} - {{ request('end_date') ? Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') : 'Semua' }}</p>
        <p>Dicetak: {{ Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item"><div class="label">Total Transaksi</div><div class="value">{{ $transactions->count() }}</div></div>
        <div class="info-item"><div class="label">Total Pendapatan</div><div class="value">Rp {{ number_format($transactions->where('payment_status', 'paid')->sum('total_price'), 0, ',', '.') }}</div></div>
        <div class="info-item"><div class="label">Berhasil</div><div class="value">{{ $transactions->where('payment_status', 'paid')->count() }}</div></div>
        <div class="info-item"><div class="label">Tertunda</div><div class="value">{{ $transactions->where('payment_status', 'pending')->count() }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Pesanan #</th>
                <th>User</th>
                <th>Paket</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Metode</th>
                <th>Tanggal Pembayaran</th>
                <th>Waktu Pembayaran</th>
                <th>Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $t)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $t->order_number }}</td>
                    <td>{{ $t->user?->name ?? '-' }}</td>
                    <td>{{ $t->item_title }}</td>
                    <td class="text-center">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-{{ $t->payment_status }}">
                            {{ ucfirst($t->payment_status) }}
                        </span>
                    </td>
                    <td>{{ $t->payment_type ? ucfirst(str_replace('_', ' ', $t->payment_type)) : '-' }}</td>
                    <td class="font-mono text-center">{{ $t->payment_time ? Carbon\Carbon::parse($t->payment_time)->format('d/m/Y') : '-' }}</td>
                    <td class="font-mono text-center">{{ $t->payment_time ? Carbon\Carbon::parse($t->payment_time)->format('H:i:s') : '-' }}</td>
                    <td class="font-mono text-center">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat oleh sistem KPM Belajar Online Membership</p>
        <p>© {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.</p>
    </div>
</body>
</html>
