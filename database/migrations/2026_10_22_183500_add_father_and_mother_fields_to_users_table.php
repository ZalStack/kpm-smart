<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'father_name')) {
                $table->string('father_name')->nullable()->after('parent_education');
            }
            if (! Schema::hasColumn('users', 'father_phone')) {
                $table->string('father_phone')->nullable()->after('father_name');
            }
            if (! Schema::hasColumn('users', 'father_education')) {
                $table->string('father_education')->nullable()->after('father_phone');
            }
            if (! Schema::hasColumn('users', 'father_profession')) {
                $table->string('father_profession')->nullable()->after('father_education');
            }

            if (! Schema::hasColumn('users', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_profession');
            }
            if (! Schema::hasColumn('users', 'mother_phone')) {
                $table->string('mother_phone')->nullable()->after('mother_name');
            }
            if (! Schema::hasColumn('users', 'mother_education')) {
                $table->string('mother_education')->nullable()->after('mother_phone');
            }
            if (! Schema::hasColumn('users', 'mother_profession')) {
                $table->string('mother_profession')->nullable()->after('mother_education');
            }
        });

        // Backfill data ayah dari parent_name jika father_name kosong
        DB::table('users')->whereNotNull('parent_name')->whereNull('father_name')->update([
            'father_name' => DB::raw('parent_name'),
            'father_phone' => DB::raw('parent_phone'),
            'father_education' => DB::raw('parent_education'),
            'father_profession' => DB::raw('parent_profession'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(
                [
                    'father_name', 'father_phone', 'father_education', 'father_profession',
                    'mother_name', 'mother_phone', 'mother_education', 'mother_profession',
                ],
                fn (string $c) => Schema::hasColumn('users', $c)
            );
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
