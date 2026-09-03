{{-- admin/practice-statistics/export-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Aktivitas Pengerjaan Soal</title>
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
        .status-completed { color: #059669; font-weight: 600; }
        .status-in-progress { color: #D97706; font-weight: 600; }
        .status-cancelled { color: #DC2626; font-weight: 600; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', monospace; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: 600; }
        .badge-completed { background: #D1FAE5; color: #059669; }
        .badge-in-progress { background: #FEF3C7; color: #D97706; }
        .badge-cancelled { background: #FEE2E2; color: #DC2626; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN AKTIVITAS PENGERJAAN SOAL</h1>
        <p>Periode: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Dicetak: {{ Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item"><div class="label">Total Sesi</div><div class="value">{{ number_format($totalSessions) }}</div></div>
        <div class="info-item"><div class="label">Total User</div><div class="value">{{ number_format($totalUsers) }}</div></div>
        <div class="info-item"><div class="label">Total Soal</div><div class="value">{{ number_format($totalPackages) }}</div></div>
        <div class="info-item"><div class="label">Rata-rata Nilai</div><div class="value">{{ $avgScore }}%</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>User</th>
                <th>Soal</th>
                <th>Status</th>
                <th>Soal</th>
                <th>Benar</th>
                <th>Salah</th>
                <th>Nilai</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $index => $s)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $s->user?->name ?? '-' }}</td>
                    <td>{{ $s->package?->title ?? 'Deleted' }}</td>
                    <td>
                        <span class="badge badge-{{ $s->status }}">
                            {{ ucfirst(str_replace('_', ' ', $s->status)) }}
                        </span>
                    </td>
                    <td class="text-center">{{ $s->total_question }}</td>
                    <td class="text-center">{{ $s->correct_answer }}</td>
                    <td class="text-center">{{ $s->wrong_answer }}</td>
                    <td class="text-center">{{ $s->total_score ? number_format($s->total_score, 1) : '-' }}</td>
                    <td class="text-center font-mono">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat oleh sistem KPM SMART Membership</p>
        <p>© {{ date('Y') }} KPM SMART. All rights reserved.</p>
    </div>
</body>
</html>
