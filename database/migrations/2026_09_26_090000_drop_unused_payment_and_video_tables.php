<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Membersihkan sisa skema pembayaran & video yang tidak pernah diimplementasikan.
 *
 * Latar belakang: ada 10+ migration yang membuat tabel `orders`, `videos`,
 * `video_orders`, dan `testimonials`, tapi tidak ada satupun Model, route,
 * controller, atau halaman yang memakainya. dependensi `midtrans/midtrans-php`
 * juga tidak pernah dipakai (0 referensi di app/). Semuanya sisa fitur yang
 * tidak jadi dikerjakan.
 *
 * Semua tabel tersebut dipastikan kosong (0 baris) sebelum migration ini dibuat,
 * jadi tidak ada data yang hilang.
 *
 * `practice_sessions.order_id` juga ikut dibuang: kolomnya menunjuk ke tabel
 * `orders` yang dihapus, dan tidak ada satu pun kode yang memakainya.
 */
return new class extends Migration
{
    /** Tabel yang dibuang, urut dari dependensi paling dependent. */
    private const TABLES = ['video_orders', 'videos', 'orders', 'testimonials'];

    public function up(): void
    {
        $this->dropOrderIdColumn();

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->withoutForeignKeyChecks(fn () => Schema::dropIfExists($table));
        }
    }

    /**
     * Buang kolom `practice_sessions.order_id` beserta foreign key-nya.
     *
     * Penting: constraint harus dilepas SEBELUM tabel `orders` dihapus. Kalau
     * tabelnya dihapus duluan dengan FOREIGN_KEY_CHECKS=0, definisi constraint
     * tetap tertinggal dan membuat insert berikutnya gagal karena tabel tujuan
     * tidak ada lagi.
     */
    private function dropOrderIdColumn(): void
    {
        if (! Schema::hasTable('practice_sessions') || ! Schema::hasColumn('practice_sessions', 'order_id')) {
            return;
        }

        $this->withoutForeignKeyChecks(function () {
            Schema::table('practice_sessions', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });

            Schema::table('practice_sessions', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        });
    }

    private function withoutForeignKeyChecks(callable $callback): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $callback();

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $callback();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Sengaja tidak dipulihkan: tabel-tabel ini tidak pernah dipakai, dan
        // struktur aslinya tersebar di banyak migration lama. Lebih aman untuk
        // restore dari backup database daripada menebak definisi kolomnya.
    }
};
