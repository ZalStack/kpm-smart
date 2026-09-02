<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_active');
        });

        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->index('card_id');
            $table->index('status');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->index('is_approved');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropIndex(['card_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['is_active']);
        });
    }
};
