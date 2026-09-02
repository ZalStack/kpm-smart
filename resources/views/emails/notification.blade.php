<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F6F8FC;font-family:'Poppins',Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F6F8FC;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0E1447,#27438D);padding:30px 40px;border-radius:20px 20px 0 0;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#FCC626,#FFB020);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;color:#090C2E;">K</div>
                                            <div>
                                                <div style="color:#fff;font-weight:700;font-size:16px;">KPM Belajar Online</div>
                                                <div style="color:rgba(255,255,255,0.5);font-size:11px;">Notifikasi Sistem</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background:#ffffff;padding:40px;">
                            <h2 style="margin:0 0 8px;color:#0E1447;font-size:20px;font-weight:700;">{{ $notification->title }}</h2>
                            <p style="margin:0 0 20px;color:#64748B;font-size:13px;">Hai, {{ $user->name }}!</p>

                            <div style="background:#F7F9FE;border:1px solid #E5EAF4;border-radius:12px;padding:20px;margin-bottom:24px;">
                                <p style="margin:0;color:#1B1E34;font-size:14px;line-height:1.7;">{{ $notification->message }}</p>
                            </div>

                            @if(!empty($notification->data['action_url']))
                            <table cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#27438D,#0E1447);border-radius:10px;">
                                        <a href="{{ $notification->data['action_url'] }}" style="display:inline-block;padding:12px 28px;color:#ffffff;font-weight:600;font-size:14px;text-decoration:none;">Lihat Detail →</a>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <p style="margin:0;color:#94A3B8;font-size:12px;line-height:1.6;">
                                Jika kamu tidak merasa melakukan aktivitas ini, silakan hubungi tim support kami.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#F7F9FE;padding:24px 40px;border-radius:0 0 20px 20px;border-top:1px solid #E5EAF4;">
                            <p style="margin:0;text-align:center;color:#94A3B8;font-size:11px;">
                                © {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
