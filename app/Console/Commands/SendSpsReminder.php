<?php

namespace App\Console\Commands;

use App\Services\PushNotificationService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSpsReminder extends Command
{
    protected $signature = 'push:sps-reminder
                            {--time=morning : Waktu pengiriman (morning/afternoon/evening)}';
    protected $description = 'Kirim notifikasi push pengingat mengisi SPS harian kepada siswa yang belum mengisi';

    public function handle(): int
    {
        $time = $this->option('time');
        $users = PushNotificationService::getSpsReminderUsers();

        if ($users->isEmpty()) {
            $this->info('Tidak ada siswa yang perlu diingatkan (semua sudah mengisi SPS hari ini).');
            return self::SUCCESS;
        }

        $this->info("Mengirim pengingat SPS kepada {$users->count()} siswa...");

        $messages = [
            'morning' => [
                'title' => 'Selamat Pagi! Jangan Lupa isi SPS Hari Ini',
                'body' => 'Selamat pagi! Jangan lupa untuk mengisi Sistem Penilaian Harian (SPS) hari ini. Kerjakan sekarang sebelum terlambat!',
            ],
            'afternoon' => [
                'title' => 'Pengingat SPS Hari Ini',
                'body' => 'Halo! Kamu belum mengisi SPS hari ini lho. Sisa waktu tinggal sedikit, yuk kerjakan sekarang!',
            ],
            'evening' => [
                'title' => 'Terakhir! SPS Hari Ini Belum Diisi',
                'body' => 'Hari ini hampir berakhir! Jangan lupa isi SPS hari ini sebelum tenggat waktu. Kerjakan sekarang!',
            ],
        ];

        $message = $messages[$time] ?? $messages['morning'];
        $successCount = 0;
        $failCount = 0;

        foreach ($users as $user) {
            $results = PushNotificationService::sendToUser(
                $user->id,
                $message['title'],
                $message['body'],
                [
                    'url' => '/practice',
                    'tag' => 'sps-reminder-' . today()->format('Y-m-d'),
                    'requireInteraction' => true,
                ]
            );

            // Also create in-app notification
            NotificationService::create(
                $user->id,
                'sps_reminder',
                $message['title'],
                $message['body'],
                ['date' => today()->format('Y-m-d')],
                false // Don't send email for reminder
            );

            foreach ($results as $result) {
                if ($result['status'] === 'success') {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
        }

        $this->info("Selesai! Berhasil: {$successCount}, Gagal: {$failCount}");

        return self::SUCCESS;
    }
}
