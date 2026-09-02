<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Profile fields
            $table->string('phone')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_class')->nullable();
            $table->string('student_major')->nullable();
            $table->string('school_name')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('address')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();

            // Role and status
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->json('notifications')->nullable();
            $table->json('activity_logs')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
