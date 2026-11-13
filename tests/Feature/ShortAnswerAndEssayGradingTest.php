<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortAnswerAndEssayGradingTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'bidang' => 'MTK',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function makePackageWithShortAnswer(): Package
    {
        return Package::create([
            'title' => 'PR Uang dan Isian Singkat',
            'description' => 'Latihan soal isian singkat',
            'bidang' => 'MTK',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'cards' => [
                ['id' => 'card-1', 'title' => 'Bagian A', 'order' => 1],
            ],
            'questions' => [
                [
                    'id' => 'q1',
                    'card_id' => 'card-1',
                    'question' => 'Berapa harga sebuah buku jika modalnya 2000 dan untung 500?',
                    'options' => [],
                    'correct_answer' => '2500',
                    'accepted_answers' => ['2500', 'Rp 2.500', '2.500 Rupiah'],
                    'type' => 'isian_singkat',
                ],
                [
                    'id' => 'q2',
                    'card_id' => 'card-1',
                    'question' => 'Sebutkan nama presiden pertama RI?',
                    'options' => [],
                    'correct_answer' => 'Ir. Soekarno',
                    'accepted_answers' => ['Soekarno', 'Bung Karno'],
                    'type' => 'essay',
                ],
            ],
        ]);
    }

    public function test_student_answers_with_currency_variations_are_marked_correct(): void
    {
        // Pastikan waktu di dalam window PR (bukan Jumat 19:00 - Sabtu 19:00)
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $student = $this->student();
        $package = $this->makePackageWithShortAnswer();

        $variations = [
            '2500',
            'Rp 2.500',
            '2.500 Rupiah',
            '2500 rupiah',
            'Rp. 2.500,-',
            '2.500,-',
            '2.500,00',
            'Rp 2.500,00',
            '2.500',
            'IDR 2,500',
            'dua ribu lima ratus',
        ];

        foreach ($variations as $idx => $variation) {
            $session = PracticeSession::create([
                'user_id' => $student->id,
                'package_id' => $package->id,
                'card_id' => 'card-1',
                'status' => 'in_progress',
                'total_question' => 2,
                'started_at' => now(),
            ]);

            $response = $this->actingAs($student)->post(route('user.practice.submit', $session->id), [
                'answers' => [
                    $variation,
                    'Bung Karno',
                ],
            ]);

            $response->assertSessionHasNoErrors();
            $session->refresh();

            $this->assertEquals(2, $session->correct_answer, "Variasi '{$variation}' seharusnya dinilai benar.");
            $this->assertEquals(0, $session->wrong_answer);
            $this->assertEquals(100.0, (float) $session->total_score);

            $answers = $session->answers;
            $this->assertTrue($answers[0]['is_correct']);
            $this->assertTrue($answers[1]['is_correct']);
        }
    }

    public function test_wrong_numerical_value_is_marked_wrong(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        $student = $this->student();
        $package = $this->makePackageWithShortAnswer();

        $session = PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'status' => 'in_progress',
            'total_question' => 2,
            'started_at' => now(),
        ]);

        $this->actingAs($student)->post(route('user.practice.submit', $session->id), [
            'answers' => [
                '25000', // salah!
                'Bukan Sukarno',
            ],
        ]);

        $session->refresh();
        $this->assertEquals(0, $session->correct_answer);
        $this->assertEquals(2, $session->wrong_answer);
        $this->assertEquals(0.0, (float) $session->total_score);
    }

    public function test_attempt_payload_never_leaks_accepted_answers_or_correct_answers(): void
    {
        $package = $this->makePackageWithShortAnswer();
        $attemptQuestions = $package->questionsForAttempt('card-1');

        foreach ($attemptQuestions as $q) {
            $this->assertArrayNotHasKey('correct_answer', $q);
            $this->assertArrayNotHasKey('accepted_answers', $q);
            $this->assertArrayNotHasKey('explanation', $q);
        }
    }

    public function test_admin_can_add_question_with_accepted_answers_and_essay_type(): void
    {
        $admin = $this->admin();
        $package = Package::create([
            'title' => 'Paket Baru',
            'description' => 'Deskripsi Paket',
            'bidang' => 'MTK',
            'cards' => [['id' => 'card-1', 'title' => 'Card 1', 'order' => 1]],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.packages.add-question', $package->id), [
            'card_id' => 'card-1',
            'question' => 'Berapa 1000 + 1500?',
            'type' => 'essay',
            'correct_answer' => '2500',
            'accepted_answers' => ['Rp 2.500', '2.500 Rupiah', 'dua ribu lima ratus'],
            'explanation' => '1000 + 1500 = 2500',
        ]);

        $response->assertRedirect(route('admin.packages.edit.questions', $package->id));
        $package->refresh();

        $this->assertCount(1, $package->questions);
        $savedQ = $package->questions[0];
        $this->assertEquals('essay', $savedQ['type']);
        $this->assertEquals('2500', $savedQ['correct_answer']);
        $this->assertEquals(['Rp 2.500', '2.500 Rupiah', 'dua ribu lima ratus'], $savedQ['accepted_answers']);
    }
}
