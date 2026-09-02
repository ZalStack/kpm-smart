<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Reset Kata Sandi</title>
</head>
<body style="margin:0; padding:0; background-color:#F6F8FC; font-family:'Poppins', 'Segoe UI', Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased;">

    <!-- Preheader (teks kecil yang tampil setelah subject di inbox) -->
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        Tautan reset kata sandi kamu berlaku 30 menit.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F6F8FC; padding:24px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;">

                    <!-- Logo / Brand -->
                    <tr>
                        <td align="center" style="padding-bottom:20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;">
                                        <div style="width:44px; height:44px; background:#FCC626; border-radius:14px; text-align:center; line-height:44px; font-size:20px; font-weight:800; color:#090C2E; font-family:'Sora','Segoe UI',Arial,sans-serif;">K</div>
                                    </td>
                                    <td align="left" style="font-size:15px; font-weight:700; color:#0E1447;">KPM Belajar Online<br><span style="font-size:11px; font-weight:400; color:#94A3B8;">Platform belajar &amp; bank soal</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Kartu utama -->
                    <tr>
                        <td style="background-color:#FFFFFF; border-radius:22px; box-shadow:0 18px 44px -20px rgba(14,20,71,.25); overflow:hidden;">
                            <!-- Header gradasi -->
                            <div style="background:#0E1447; background-image:linear-gradient(150deg,#090C2E 0%,#0E1447 55%,#1B2E74 100%); padding:30px 28px; text-align:center;">
                                <div style="width:56px; height:56px; margin:0 auto 12px; background:rgba(0,162,233,.2); border:1px solid rgba(127,219,255,.35); border-radius:16px; line-height:58px;">
                                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#7FDBFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </div>
                                <h1 style="margin:0; font-size:21px; line-height:1.35; color:#FFFFFF; font-weight:700;">Reset Kata Sandi</h1>
                                <p style="margin:6px 0 0; font-size:13px; color:rgba(255,255,255,.65);">Permintaan pemulihan akses akun diterima</p>
                            </div>

                            <!-- Isi -->
                            <div style="padding:28px;">
                                <p style="margin:0 0 14px; font-size:14px; color:#334155; line-height:1.7;">
                                    Halo{{ isset($name) && $name ? ' <strong style="color:#0E1447;">' . e($name) . '</strong>' : '' }},
                                </p>
                                <p style="margin:0 0 22px; font-size:14px; color:#475569; line-height:1.75;">
                                    Kami menerima permintaan untuk mengatur ulang kata sandi akunmu
                                    (<strong style="color:#0E1447;">{{ $email }}</strong>).
                                    Klik tombol di bawah ini untuk membuat kata sandi baru.
                                </p>

                                <!-- Tombol -->
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="center" style="border-radius:14px; background-image:linear-gradient(135deg,#27438D,#0E1447);">
                                            <a href="{{ $resetUrl }}"
                                               style="display:inline-block; padding:15px 28px; font-size:15px; font-weight:600; color:#FFFFFF; text-decoration:none; border-radius:14px;">
                                                Buat Kata Sandi Baru
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin:20px 0 0; font-size:12.5px; color:#64748B; line-height:1.7;">
                                    Jika tombol tidak berfungsi, salin dan tempel tautan berikut ke browser:
                                </p>
                                <p style="margin:8px 0 0;">
                                    <a href="{{ $resetUrl }}" style="font-size:12px; color:#00A2E9; word-break:break-all; text-decoration:none;">{{ $resetUrl }}</a>
                                </p>

                                <!-- Info kedaluwarsa -->
                                <div style="margin-top:22px; padding:14px 16px; background:#FFF8E1; border:1px solid #FBEDBB; border-left:4px solid #FCC626; border-radius:12px;">
                                    <p style="margin:0; font-size:12.5px; color:#7A5B00; line-height:1.65;">
                                        <strong>&#9200; Berlaku 30 menit.</strong> Tautan hanya bisa dipakai satu kali demi keamanan akunmu.
                                    </p>
                                </div>

                                <!-- Peringatan keamanan -->
                                <div style="margin-top:12px; padding:14px 16px; background:#FEF2F2; border:1px solid #FECACA; border-left:4px solid #EF4444; border-radius:12px;">
                                    <p style="margin:0; font-size:12.5px; color:#991B1B; line-height:1.65;">
                                        <strong>Bukan kamu yang meminta?</strong> Abaikan email ini — kata sandimu tetap aman,
                                        atau segera hubungi admin jika ada aktivitas mencurigakan.
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding:20px 12px 4px;">
                            <p style="margin:0; font-size:11px; color:#94A3B8; line-height:1.7;">
                                Email ini dikirim otomatis oleh sistem {{ config('app.name') }}.<br>
                                &copy; {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
