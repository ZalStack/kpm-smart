<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Harga diskon (harga potongan). Kolom `price` yang sudah ada
            // tetap dipakai sebagai "Harga Normal".
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            $table->boolean('is_discount_active')->default(false)->after('discount_price');

            // Bayar Seikhlasnya: user boleh memasukkan nominal sendiri
            // saat checkout, minimal sebesar `min_pay_amount`.
            $table->boolean('is_pay_what_you_want')->default(false)->after('is_discount_active');
            $table->decimal('min_pay_amount', 10, 2)->default(0)->after('is_pay_what_you_want');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'discount_price',
                'is_discount_active',
                'is_pay_what_you_want',
                'min_pay_amount',
            ]);
        });
    }
};
