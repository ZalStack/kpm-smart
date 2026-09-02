<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('video_order_id')
                ->nullable()
                ->unique()
                ->constrained('video_orders')
                ->nullOnDelete()
                ->after('package_id');

            $table->string('type')->default('package')->index()->after('order_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->change();
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->change();
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->cascadeOnDelete();

            $table->dropConstrainedForeignId('video_order_id');
            $table->dropColumn('type');
        });
    }
};
