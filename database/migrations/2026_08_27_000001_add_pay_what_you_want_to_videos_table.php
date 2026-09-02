<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('is_pay_what_you_want')->default(false)->after('is_active');
            $table->decimal('min_pay_amount', 10, 2)->default(0)->after('is_pay_what_you_want');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['is_pay_what_you_want', 'min_pay_amount']);
        });
    }
};
