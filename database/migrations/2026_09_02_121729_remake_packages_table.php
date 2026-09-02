<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn([
                'discount_price',
                'is_discount_active',
                'is_pay_what_you_want',
                'min_pay_amount',
                'membership_duration_days',
                'jenjang',
                'hide_explanation',
                'time_limit_minutes',
            ]);
        });

        Schema::table('packages', function (Blueprint $table) {
            // Tambah kolom baru
            $table->string('bidang', 100)->nullable()->after('kelas');
            $table->string('level', 50)->nullable()->after('bidang');
            $table->date('start_date')->nullable()->after('level');
            $table->date('end_date')->nullable()->after('start_date');
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->boolean('show_answer_key')->default(false)->after('end_time');
            $table->boolean('show_explanation')->default(true)->after('show_answer_key');
            $table->boolean('show_score')->default(true)->after('show_explanation');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'bidang', 'level', 'start_date', 'end_date',
                'start_time', 'end_time',
                'show_answer_key', 'show_explanation', 'show_score',
            ]);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            $table->boolean('is_discount_active')->default(false)->after('discount_price');
            $table->boolean('is_pay_what_you_want')->default(false)->after('is_discount_active');
            $table->decimal('min_pay_amount', 10, 2)->default(0)->after('is_pay_what_you_want');
            $table->unsignedInteger('membership_duration_days')->default(30)->after('min_pay_amount');
            $table->string('jenjang', 50)->nullable()->after('kelas');
            $table->boolean('hide_explanation')->default(false)->after('is_active');
            $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('hide_explanation');
        });
    }
};
