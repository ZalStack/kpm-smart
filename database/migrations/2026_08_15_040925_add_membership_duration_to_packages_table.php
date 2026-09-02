<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Durasi masa aktif membership (dalam hari) yang ditetapkan Admin
            // untuk paket ini. Contoh: 30 (30 Hari), 90 (3 Bulan), 180 (6 Bulan),
            // 365 (1 Tahun).
            $table->unsignedInteger('membership_duration_days')->default(30)->after('min_pay_amount');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('membership_duration_days');
        });
    }
};
