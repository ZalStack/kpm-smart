<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kunci jawaban tidak boleh sampai ke browser siswa SEBELUM ia mengerjakan soal.
 *
 * Kalau `correct_answer` ikut terkirim di payload Inertia, siswa bisa membuka
 * DevTools dan menghafal seluruh jawaban. Penilaian tetap dilakukan di server.
 */
class AnswerKeyExposureTest extends TestCase
{
    use RefreshDatabase;

    private const ANSWER = 'A';
    private const EXPLANATION = 'Penjelasan-rahasia-tidak-boleh-bocor';

    private function makePackage(): Package
    {
        return Package::create([
            'title' => 'Tugas IPA',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'level' => 'SD',
            'is_active' => true,
            'cards' => [['id' => 'card-1', 'title' => 'Bagian A', 'order' => 1]],
            'questions' => [[
                'id' => 'q1',
                'card_id' => 'card-1',
                'question' => 'Apa itu fotosintesis?',
                'options' => ['A', 'B', 'C', 'D'],
                'correct_answer' => self::ANSWER,
                'explanation' => self::EXPLANATION,
                'type' => 'pilihan_ganda',
            ]],
        ]);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
            'bidang' => 'IPA',
            'level' => 'SD',
            'email' => 'siswa@example.test',
            'password' => 'rahasia123',
        ]);
    }

    public function test_questions_for_attempt_strips_answers_and_explanations(): void
    {
        $questions = $this->makePackage()->questionsForAttempt('card-1');

        $this->assertCount(1, $questions);
        $this->assertArrayHasKey('question', $questions[0]);
        $this->assertArrayHasKey('options', $questions[0]);
        $this->assertArrayNotHasKey('correct_answer', $questions[0]);
        $this->assertArrayNotHasKey('explanation', $questions[0]);
    }

    public function test_questions_for_attempt_filters_by_card(): void
    {
        $package = $this->makePackage();

        $this->assertCount(0, $package->questionsForAttempt('card-tidak-ada'));
    }

    public function test_starting_practice_does_not_send_answer_key_to_student(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        $response = $this->actingAs($student)->post(route('user.practice.start', $package->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Practice/PracticeStart'));

        $questions = $response->viewData('page')['props']['questions'];

        $this->assertNotEmpty($questions);
        $this->assertArrayNotHasKey('correct_answer', $questions[0]);
        $this->assertArrayNotHasKey('explanation', $questions[0]);
        $this->assertStringNotContainsString(self::EXPLANATION, $response->getContent());
    }

    public function test_resuming_in_progress_session_does_not_send_answer_key(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'total_question' => 1,
            'status' => 'in_progress',
            'started_at' => now(),
            'answers' => [['question' => 'Apa itu fotosintesis?', 'user_answer' => 'B']],
        ]);

        $response = $this->actingAs($student)->post(route('user.practice.start', $package->id));

        $response->assertOk();
        $this->assertStringNotContainsString(self::EXPLANATION, $response->getContent());

        $questions = $response->viewData('page')['props']['questions'];
        $this->assertArrayNotHasKey('correct_answer', $questions[0]);
    }

    public function test_reviewing_an_in_progress_session_does_not_send_answer_key(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        $session = PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'total_question' => 1,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('user.practice.show', $session->id));

        $response->assertOk();
        $this->assertStringNotContainsString(self::EXPLANATION, $response->getContent());
    }

    public function test_user_dashboard_does_not_leak_answer_key(): void
    {
        $student = $this->student();
        $this->makePackage();

        $token = $student->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.user.dashboard'));

        $response->assertOk();
        $this->assertStringNotContainsString(self::EXPLANATION, $response->getContent());
        $this->assertStringNotContainsString('"correct_answer"', $response->getContent());

        $recent = $response->json('data.recent_packages');
        $this->assertNotEmpty($recent);
        $this->assertArrayNotHasKey('questions', $recent[0]);
        $this->assertArrayHasKey('total_questions', $recent[0]);
    }

    public function test_user_package_endpoints_do_not_leak_answer_key(): void
    {
        $student = $this->student();
        $package = $this->makePackage();
        $token = $student->createToken('test')->plainTextToken;

        $list = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.user.packages.index'));
        $list->assertOk();
        $this->assertStringNotContainsString(self::EXPLANATION, $list->getContent());

        $detail = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.user.packages.show', $package->id));
        $detail->assertOk();
        $this->assertStringNotContainsString(self::EXPLANATION, $detail->getContent());
    }

    public function test_grading_still_works_server_side_after_stripping(): void
    {
        // Membuktikan bahwa melepas kunci jawaban dari payload tidak merusak penilaian.
        $student = $this->student();
        $package = $this->makePackage();

        $session = PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'total_question' => 1,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($student)->post(route('user.practice.submit', $session->id), [
            'answers' => [self::ANSWER],
        ]);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Practice/PracticeResult'));

        $session->refresh();
        $this->assertSame('completed', $session->status);
        $this->assertSame(1, (int) $session->correct_answer);
        $this->assertSame(100.0, (float) $session->total_score);
    }
}
