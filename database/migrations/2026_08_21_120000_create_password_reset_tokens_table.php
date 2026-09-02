<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keamanan: token reset password TIDAK pernah disimpan mentah.
     * Yang disimpan hanya hash SHA-256 dari token, sehingga jika database
     * bocor, penyerang tetap tidak bisa memakai token untuk reset.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->nullable();

            $table->index('token_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
