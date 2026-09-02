<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Mailer
{
    /**
     * Keamanan: pengirim email valid (harus domain sendiri, tanpa newline).
     */
    private string $fromEmail;

    private string $fromName;

    public function __construct()
    {
        $this->fromEmail = (string) config('mail.from.address', 'no-reply@localhost');
        $this->fromName = (string) config('mail.from.name', config('app.name', 'KPM Belajar Online'));
    }

    /**
     * Kirim email HTML menggunakan fungsi mail() bawaan PHP.
     * Tidak memakai library eksternal maupun koneksi SMTP manual.
     *
     * Pengiriman nyata hanya terjadi di lingkungan non-lokal. Di lingkungan
     * lokal/testing, isi email dicatat ke storage/logs/laravel.log agar
     * alur reset password tetap bisa dites tanpa server mail.
     */
    public function sendHtml(string $toEmail, string $subject, string $view, array $data = []): bool
    {
        // Keamanan: tolak email tujuan yang mengandung newline (email header injection).
        if ($toEmail === '' || preg_match('/[\r\n]/', $toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Keamanan: sanitasi subject dari karakter kontrol/newline.
        $subject = trim(preg_replace('/[\r\n\x00-\x1F]/u', ' ', $subject));

        $html = View::make($view, $data)->render();

        if (app()->environment('local', 'development', 'testing')) {
            Log::info("MAIL [manual] => TO: {$toEmail} | SUBJECT: {$subject}\n{$html}");

            return true;
        }

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'From: ' . $this->encodedDisplayName($this->fromName) . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: MembershipSystem-Mailer',
        ]);

        // Subject & body di-encode UTF-8 base64 agar aman untuk semua klien email.
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedBody = chunk_split(base64_encode($html));

        return (bool) mail($toEmail, $encodedSubject, $encodedBody, $headers);
    }

    /**
     * Keamanan: encode nama pengirim (RFC 2047) + buang newline agar
     * tidak bisa menyuntikkan header tambahan lewat konfigurasi nama app.
     */
    private function encodedDisplayName(string $name): string
    {
        $name = trim(preg_replace('/[\r\n\x00-\x1F]/u', '', $name));

        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }
}
