<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename duitku_reference -> payment_reference di orders dan video_orders
 * agar tidak ada nama gateway lama yang tersisa di skema database.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rename di tabel orders (jika kolom masih bernama lama)
        if (Schema::hasColumn('orders', 'duitku_reference')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('duitku_reference', 'payment_reference');
            });
        }

        // Rename di tabel video_orders
        if (Schema::hasColumn('video_orders', 'duitku_reference')) {
            Schema::table('video_orders', function (Blueprint $table) {
                $table->renameColumn('duitku_reference', 'payment_reference');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_reference')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('payment_reference', 'duitku_reference');
            });
        }

        if (Schema::hasColumn('video_orders', 'payment_reference')) {
            Schema::table('video_orders', function (Blueprint $table) {
                $table->renameColumn('payment_reference', 'duitku_reference');
            });
        }
    }
};
