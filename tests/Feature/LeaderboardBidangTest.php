<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Papan peringkat harus terpisah per bidang studi: Matematika dan IPA
 * tidak pernah tercampur, baik untuk siswa maupun admin.
 */
class LeaderboardBidangTest extends TestCase
{
    use RefreshDatabase;

    private function makeSession(User $student, float $score, string $bidang): PracticeSession
    {
        $package = Package::create([
            'title' => 'PR ' . $bidang,
            'description' => 'Paket PR ' . $bidang,
            'bidang' => $bidang,
            'is_active' => true,
            'questions' => [],
        ]);

        return PracticeSession::create([
            'user_id' => $student->id,
            'package_id' => $package->id,
            'card_id' => 'card-1',
            'total_question' => 10,
            'correct_answer' => (int) round($score / 10),
            'total_score' => $score,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'answers' => [],
        ]);
    }

    public function test_student_leaderboard_only_contains_same_bidang_students(): void
    {
        $me = User::factory()->create(['role' => 'user', 'bidang' => 'MTK']);
        $mate = User::factory()->create(['role' => 'user', 'bidang' => 'Matematika']);
        $other = User::factory()->create(['role' => 'user', 'bidang' => 'IPA']);

        $this->makeSession($mate, 90, 'MTK');
        $this->makeSession($other, 99, 'IPA');

        $response = $this->actingAs($me)->get(route('user.leaderboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Gamification/Leaderboard')
            ->where('bidangLabel', 'Matematika')
            ->has('leaderboard', 1)
            ->where('leaderboard.0.user.id', $mate->id));
    }

    public function test_admin_leaderboard_can_filter_by_bidang_without_mixing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mtk = User::factory()->create(['role' => 'user', 'bidang' => 'MTK', 'student_class' => '7A']);
        $ipa = User::factory()->create(['role' => 'user', 'bidang' => 'IPA', 'student_class' => '7A']);

        $this->makeSession($mtk, 80, 'MTK');
        $this->makeSession($ipa, 95, 'IPA');

        // Semua bidang tampil, tapi dengan label bidang masing-masing.
        $all = $this->actingAs($admin)->get(route('admin.leaderboard'));
        $all->assertOk();
        $all->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Leaderboard/AdminLeaderboardIndex')
            ->has('leaderboard', 2)
            ->where('leaderboard.0.bidang_group', 'IPA')
            ->where('leaderboard.1.bidang_group', 'Matematika'));

        // Filter bidang Matematika: siswa IPA tidak ikut tampil.
        $mtkOnly = $this->actingAs($admin)->get(route('admin.leaderboard', ['bidang' => 'Matematika']));
        $mtkOnly->assertOk();
        $mtkOnly->assertInertia(fn (Assert $page) => $page
            ->has('leaderboard', 1)
            ->where('leaderboard.0.user.id', $mtk->id)
            ->where('leaderboard.0.bidang_group', 'Matematika'));
    }

    public function test_admin_leaderboard_requires_admin_role(): void
    {
        $student = User::factory()->create(['role' => 'user']);

        $this->actingAs($student)
            ->get(route('admin.leaderboard'))
            ->assertRedirect(route('user.dashboard'));
    }
}
