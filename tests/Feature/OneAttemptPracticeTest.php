<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Siswa hanya boleh mengerjakan setiap PR pekanan 1 kali.
 *
 * Klik kartu PR pada daftar langsung masuk ke bagian soal (tanpa halaman
 * kartu perantara), sesi lama dilanjutkan, dan hasil akhir tidak bisa
 * ditulis ulang lewat pengumpulan ganda.
 */
class OneAttemptPracticeTest extends TestCase
{
    use RefreshDatabase;

    private function makePackage(): Package
    {
        return Package::create([
            'title' => 'PR Matematika Pekan 1',
            'description' => 'Deskripsi',
            'bidang' => 'MTK',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'cards' => [
                ['id' => 'card-1', 'title' => 'Bagian A', 'order' => 1],
                ['id' => 'card-2', 'title' => 'Bagian B', 'order' => 2],
            ],
            'questions' => [
                [
                    'id' => 'q1',
                    'card_id' => 'card-1',
                    'question' => '1 + 1 = ?',
                    'options' => ['2', '3', '4', '5'],
                    'correct_answer' => '2',
                    'type' => 'pilihan_ganda',
                ],
                [
                    'id' => 'q2',
                    'card_id' => 'card-2',
                    'question' => '2 + 2 = ?',
                    'options' => ['3', '4', '5', '6'],
                    'correct_answer' => '4',
                    'type' => 'pilihan_ganda',
                ],
            ],
        ]);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
            'bidang' => 'MTK',
            'email' => 'siswa-pr@example.test',
        ]);
    }

    public function test_starting_pr_goes_straight_to_questions_and_creates_one_session(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        $response = $this->actingAs($student)->post(route('user.practice.start', $package->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Practice/PracticeStart')
            ->has('questions', 2)
            ->where('questions.0.question', '1 + 1 = ?'));

        // Satu sesi memuat seluruh soal PR (semua kartu) karena halaman
        // pemilihan kartu sudah tidak dipakai.
        $this->assertSame(1, PracticeSession::where('user_id', $student->id)->count());
        $this->assertNull(PracticeSession::where('user_id', $student->id)->first()->card_id);
    }

    public function test_starting_again_resumes_the_same_active_session(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        $this->actingAs($student)->post(route('user.practice.start', $package->id));
        $this->actingAs($student)->post(route('user.practice.start', $package->id));

        // Tidak boleh ada sesi kedua yang terbuka untuk PR yang sama.
        $this->assertSame(1, PracticeSession::where('user_id', $student->id)->count());
    }

    public function test_completed_pr_cannot_be_attempted_again(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        $session = PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'total_question' => 1,
            'correct_answer' => 1,
            'total_score' => 100,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'answers' => [],
        ]);

        $response = $this->actingAs($student)->post(route('user.practice.start', $package->id));

        // Diarahkan langsung ke hasil, bukan membuat pengerjaan baru.
        $response->assertRedirect(route('user.practice.show', $session->id));
        $this->assertSame(1, PracticeSession::where('user_id', $student->id)->count());
    }

    public function test_single_attempt_covers_every_card_in_one_score(): void
    {
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response(['ok' => true])]);

        $student = $this->student();
        $package = $this->makePackage();

        $this->actingAs($student)->post(route('user.practice.start', $package->id));

        $session = PracticeSession::where('user_id', $student->id)->firstOrFail();

        // Tanpa pemilihan kartu: satu sesi memuat seluruh soal PR.
        $this->assertNull($session->card_id);

        $this->actingAs($student)->post(route('user.practice.submit', $session->id), [
            'answers' => ['2', '4'],
            'duration_seconds' => 60,
        ]);

        $session->refresh();
        $this->assertSame('completed', $session->status);
        $this->assertSame(2, (int) $session->correct_answer);
        $this->assertEqualsWithDelta(100.0, (float) $session->total_score, 0.01);

        // Pengumpulan kedua tidak mengubah nilai.
        $this->actingAs($student)->post(route('user.practice.submit', $session->id), [
            'answers' => ['3', '3'],
        ]);

        $session->refresh();
        $this->assertEqualsWithDelta(100.0, (float) $session->total_score, 0.01);
    }

    public function test_completed_session_cannot_be_resubmitted(): void
    {
        $student = $this->student();
        $package = $this->makePackage();

        $session = PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'total_question' => 1,
            'correct_answer' => 1,
            'total_score' => 100,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'finished_at' => now()->subMinutes(10),
            'answers' => [],
        ]);

        $response = $this->actingAs($student)->post(route('user.practice.submit', $session->id), [
            'answers' => ['3'],
        ]);

        $response->assertRedirect(route('user.practice.show', $session->id));

        $session->refresh();
        $this->assertSame('completed', $session->status);
        $this->assertSame(100.0, (float) $session->total_score);
    }

    public function test_pr_list_shows_attempt_status_and_safe_payload(): void
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
            'answers' => [],
        ]);

        $response = $this->actingAs($student)->get(route('user.packages.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Packages/PackageList')
            ->where('packageStatuses.' . $package->id . '.status', 'in_progress'));

        // Daftar PR tidak mengirim daftar soal (berisi kunci jawaban) ke browser.
        $this->assertStringNotContainsString('correct_answer', $response->getContent());
    }
}
