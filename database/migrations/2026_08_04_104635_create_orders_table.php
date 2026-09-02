<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->text('payment_notes')->nullable();
            $table->json('enrollment')->nullable();
            $table->timestamps();

            $table->index('order_number');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
