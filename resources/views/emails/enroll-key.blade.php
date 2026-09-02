{{-- emails/enroll-key.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Key Anda - KPM Belajar Online</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#27438D,#161758); padding:28px 32px; text-align:center;">
                            <h1 style="color:#ffffff; margin:0; font-size:20px;">KPM Belajar Online</h1>
                            <p style="color:#c7d2fe; margin:6px 0 0; font-size:13px;">Enroll Key Pembelian Paket</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="color:#111827; font-size:15px; margin:0 0 12px;">Halo <strong>{{ $user->name }}</strong>,</p>
                            <p style="color:#374151; font-size:14px; line-height:1.6; margin:0 0 20px;">
                                Terima kasih telah membeli paket <strong>{{ $package->title ?? $order->item_title }}</strong>.
                                Berikut adalah Enroll Key Anda untuk mengaktifkan akses:
                            </p>

                            <!-- Key box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2ff; border:1px dashed #27438D; border-radius:12px; margin-bottom:20px;">
                                <tr>
                                    <td align="center" style="padding:18px 16px;">
                                        <span style="display:inline-block; font-size:22px; font-weight:bold; letter-spacing:2px; color:#161758; font-family:'Courier New', monospace;">{{ $key }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#374151; font-size:14px; line-height:1.6; margin:0 0 8px;">
                                Nomor Pesanan: <strong>{{ $order->order_number }}</strong>
                            </p>
                            <p style="color:#6b7280; font-size:13px; line-height:1.6; margin:0 0 20px;">
                                Masukkan key ini pada menu <em>Pesanan</em> di akun Anda untuk mulai belajar. Simpan email ini sebagai bukti kepemilikan key.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/orders') }}" style="display:inline-block; background-color:#27438D; color:#ffffff; text-decoration:none; padding:12px 32px; border-radius:10px; font-size:14px; font-weight:bold;">Aktifkan Sekarang</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb; padding:20px 32px; border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af; font-size:11px; line-height:1.6; margin:0; text-align:center;">
                                Jika Anda tidak merasa melakukan pembelian ini, abaikan email atau hubungi tim support kami.<br>
                                &copy; {{ date('Y') }} KPM Belajar Online. Semua hak dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
