<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->boolean('access_granted')->default(false);
            $table->date('access_start')->nullable();
            $table->date('access_end')->nullable();
            $table->timestamps();

            $table->index('order_number');
            $table->index('payment_status');
            $table->index('user_id');
            $table->index('video_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_orders');
    }
};
