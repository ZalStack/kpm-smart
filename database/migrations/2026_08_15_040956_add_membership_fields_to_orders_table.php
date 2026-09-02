<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Snapshot durasi (hari) membership yang berlaku untuk order /
            // perpanjangan ini (diambil dari paket saat order dibuat lunas).
            $table->unsignedInteger('membership_duration_days')->nullable()->after('enrollment');

            // Tanggal mulai & berakhir masa aktif membership untuk order ini.
            // Diisi otomatis oleh sistem saat pembayaran berhasil (paid).
            $table->date('membership_start')->nullable()->after('membership_duration_days');
            $table->date('membership_end')->nullable()->after('membership_start');

            $table->index('membership_end');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['membership_duration_days', 'membership_start', 'membership_end']);
        });
    }
};
