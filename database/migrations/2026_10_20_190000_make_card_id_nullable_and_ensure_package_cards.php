<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah kolom card_id pada practice_sessions agar nullable dan ber-default 'card-1'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE practice_sessions MODIFY COLUMN card_id VARCHAR(255) NULL DEFAULT 'card-1'");
        } else {
            Schema::table('practice_sessions', function (Blueprint $table) {
                $table->string('card_id')->nullable()->default('card-1')->change();
            });
        }

        // 2. Pastikan seluruh paket memiliki minimal 1 card dan semua soal memiliki card_id
        $packages = DB::table('packages')->get();
        foreach ($packages as $pkg) {
            $cards = json_decode($pkg->cards, true) ?: [];
            if (empty($cards)) {
                $cards = [
                    [
                        'id' => 'card-1',
                        'title' => 'Tugas Utama',
                        'content' => 'Kerjakan seluruh soal di bawah ini dengan teliti.',
                    ],
                ];
            }

            $primaryCardId = $cards[0]['id'] ?? 'card-1';
            $questions = json_decode($pkg->questions, true) ?: [];
            $updatedQuestions = [];

            foreach ($questions as $q) {
                if (empty($q['card_id'])) {
                    $q['card_id'] = $primaryCardId;
                }
                $updatedQuestions[] = $q;
            }

            DB::table('packages')->where('id', $pkg->id)->update([
                'cards' => json_encode($cards),
                'questions' => json_encode($updatedQuestions),
                'is_active' => true,
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
