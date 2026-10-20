<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'parent_name')) {
                $table->string('parent_name')->nullable();
            }
            if (! Schema::hasColumn('users', 'parent_phone')) {
                $table->string('parent_phone')->nullable();
            }
            if (! Schema::hasColumn('users', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable();
            }
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->string('date_of_birth')->nullable();
            }
            if (! Schema::hasColumn('users', 'parent_profession')) {
                $table->string('parent_profession')->nullable();
            }
            if (! Schema::hasColumn('users', 'parent_education')) {
                $table->string('parent_education')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                ['place_of_birth', 'date_of_birth', 'parent_profession', 'parent_education'],
                fn (string $c) => Schema::hasColumn('users', $c)
            );
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
