<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrasi gateway pembayaran Midtrans -> Duitku:
     * - snap_token (token Snap JS) diganti duitku_reference (reference Duitku);
     * - payment_url menyimpan URL halaman pembayaran Duitku agar user bisa
     *   melanjutkan pembayaran tanpa membuat inquiry baru.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('snap_token', 'duitku_reference');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->text('payment_url')->nullable()->after('duitku_reference');
        });

        Schema::table('video_orders', function (Blueprint $table) {
            $table->renameColumn('snap_token', 'duitku_reference');
        });

        Schema::table('video_orders', function (Blueprint $table) {
            $table->text('payment_url')->nullable()->after('duitku_reference');
        });
    }

    public function down(): void
    {
        Schema::table('video_orders', function (Blueprint $table) {
            $table->dropColumn('payment_url');
            $table->renameColumn('duitku_reference', 'snap_token');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_url');
            $table->renameColumn('duitku_reference', 'snap_token');
        });
    }
};
