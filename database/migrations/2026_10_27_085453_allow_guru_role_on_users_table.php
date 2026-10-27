<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Pada SQLite, column enum dibuat sebagai varchar dengan CHECK constraint.
            // Kita ubah tipe kolom role menjadi string biasa agar nilai 'guru', 'admin', 'user' valid.
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 50)->default('user')->change();
            });
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};
