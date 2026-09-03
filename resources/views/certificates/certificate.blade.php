<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - KPM SMART</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; }

        .certificate {
            width: 1120px;
            height: 790px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f0f9ff 100%);
        }

        .border-decor {
            position: absolute;
            inset: 15px;
            border: 3px solid #166534;
            border-radius: 12px;
            pointer-events: none;
        }

        .border-decor::before {
            content: '';
            position: absolute;
            inset: 6px;
            border: 1px solid #86efac;
            border-radius: 8px;
        }

        .corner { position: absolute; width: 60px; height: 60px; }
        .corner::before, .corner::after {
            content: ''; position: absolute; background: #166534;
        }
        .corner-tl { top: 25px; left: 25px; }
        .corner-tl::before { width: 30px; height: 3px; top: 0; left: 0; }
        .corner-tl::after { width: 3px; height: 30px; top: 0; left: 0; }
        .corner-tr { top: 25px; right: 25px; }
        .corner-tr::before { width: 30px; height: 3px; top: 0; right: 0; }
        .corner-tr::after { width: 3px; height: 30px; top: 0; right: 0; }
        .corner-bl { bottom: 25px; left: 25px; }
        .corner-bl::before { width: 30px; height: 3px; bottom: 0; left: 0; }
        .corner-bl::after { width: 3px; height: 30px; bottom: 0; left: 0; }
        .corner-br { bottom: 25px; right: 25px; }
        .corner-br::before { width: 30px; height: 3px; bottom: 0; right: 0; }
        .corner-br::after { width: 3px; height: 30px; bottom: 0; right: 0; }

        .content { position: relative; z-index: 1; text-align: center; padding: 50px 80px; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; }

        .logo { font-size: 14px; font-weight: 700; color: #166534; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 6px; }
        .logo-sub { font-size: 9px; color: #64748b; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 25px; }

        .title { font-size: 38px; font-weight: 800; color: #166534; letter-spacing: 6px; text-transform: uppercase; margin-bottom: 5px; }
        .subtitle { font-size: 11px; color: #94a3b8; letter-spacing: 2px; margin-bottom: 30px; }

        .desc { font-size: 12px; color: #475569; line-height: 1.8; margin-bottom: 30px; max-width: 650px; }
        .desc strong { color: #166534; }

        .name { font-size: 32px; font-weight: 700; color: #0f172a; margin-bottom: 8px; border-bottom: 2px solid #166534; padding-bottom: 5px; display: inline-block; }

        .package-name { font-size: 13px; color: #64748b; margin-bottom: 25px; }
        .package-name strong { color: #166534; }

        .score-row { display: flex; gap: 40px; margin-bottom: 25px; }
        .score-item { text-align: center; }
        .score-item .label { font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .score-item .value { font-size: 20px; font-weight: 700; color: #166534; }

        .date-line { font-size: 10px; color: #94a3b8; margin-bottom: 35px; }

        .signatures { display: flex; justify-content: space-between; width: 100%; max-width: 700px; }
        .signature { text-align: center; width: 180px; }
        .signature .line { border-top: 1px solid #cbd5e1; margin-top: 45px; padding-top: 8px; }
        .signature .sig-name { font-size: 11px; font-weight: 600; color: #0f172a; }
        .signature .sig-role { font-size: 9px; color: #94a3b8; }

        .seal { position: absolute; bottom: 120px; right: 120px; width: 80px; height: 80px; border: 2px solid #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0.3; }
        .seal-text { font-size: 7px; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-align: center; line-height: 1.3; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="border-decor"></div>
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <div class="content">
            <div class="logo">KPM SMART</div>
            <div class="logo-sub">Platform Pembelajaran Digital</div>

            <div class="title">Sertifikat</div>
            <div class="subtitle">Pencapaian Penyelesaian Tugas</div>

            <div class="desc">
                Dengan ini menyatakan bahwa
            </div>

            <div class="name">{{ $user->name }}</div>

            <div class="package-name">
                telah berhasil menyelesaikan tugas <strong>{{ $package->title ?? 'Tugas' }}</strong>
            </div>

            <div class="score-row">
                <div class="score-item">
                    <div class="label">Nilai</div>
                    <div class="value">{{ number_format($session->total_score, 1) }}</div>
                </div>
                <div class="score-item">
                    <div class="label">Benar</div>
                    <div class="value">{{ $session->correct_answer }}</div>
                </div>
                <div class="score-item">
                    <div class="label">Total Soal</div>
                    <div class="value">{{ $session->total_question }}</div>
                </div>
            </div>

            <div class="date-line">
                Diterbitkan pada {{ \Carbon\Carbon::parse($session->finished_at)->isoFormat('D MMMM YYYY') }}
            </div>

            <div class="signatures">
                <div class="signature">
                    <div class="line">
                        <div class="sig-name">{{ $user->name }}</div>
                        <div class="sig-role">Peserta</div>
                    </div>
                </div>
                <div class="signature">
                    <div class="line">
                        <div class="sig-name">KPM SMART</div>
                        <div class="sig-role">Sistem</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="seal">
            <div class="seal-text">KPM<br>SMART</div>
        </div>
    </div>
</body>
</html>
