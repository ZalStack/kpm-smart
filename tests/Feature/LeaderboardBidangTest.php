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

    public function test_students_without_bidang_are_not_mixed_with_matematika_or_ipa(): void
    {
        $me = User::factory()->create(['role' => 'user', 'bidang' => null]);
        $mate = User::factory()->create(['role' => 'user', 'bidang' => '']);
        $mtk = User::factory()->create(['role' => 'user', 'bidang' => 'MTK']);
        $ipa = User::factory()->create(['role' => 'user', 'bidang' => 'IPA']);

        $this->makeSession($mate, 70, 'Lainnya');
        $this->makeSession($mtk, 95, 'MTK');
        $this->makeSession($ipa, 99, 'IPA');

        $response = $this->actingAs($me)->get(route('user.leaderboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Gamification/Leaderboard')
            ->where('bidangLabel', 'Lainnya')
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

    public function test_student_leaderboard_defaults_to_student_own_class_level_and_bidang(): void
    {
        $me = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika',
            'student_class' => '4A',
            'level' => '1',
        ]);

        $peerSame = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika',
            'student_class' => '4A',
            'level' => '1',
        ]);

        $peerDiffClass = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika',
            'student_class' => '5B',
            'level' => '1',
        ]);

        $peerDiffLevel = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika',
            'student_class' => '4A',
            'level' => '2',
        ]);

        $peerDiffBidang = User::factory()->create([
            'role' => 'user',
            'bidang' => 'IPA',
            'student_class' => '4A',
            'level' => '1',
        ]);

        $this->makeSession($me, 85, 'MTK');
        $this->makeSession($peerSame, 92, 'MTK');
        $this->makeSession($peerDiffClass, 95, 'MTK');
        $this->makeSession($peerDiffLevel, 98, 'MTK');
        $this->makeSession($peerDiffBidang, 100, 'IPA');

        // Saat siswa login dan membuka leaderboard tanpa parameter,
        // peringkat otomatis tersegmentasi sesuai kelas 4A, level 1, bidang Matematika.
        $response = $this->actingAs($me)->get(route('user.leaderboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Gamification/Leaderboard')
            ->where('bidangLabel', 'Matematika')
            ->where('selectedKelas', '4A')
            ->where('selectedLevel', '1')
            ->where('isMyScope', true)
            ->has('leaderboard', 2)
            ->where('leaderboard.0.user.id', $peerSame->id)
            ->where('leaderboard.1.user.id', $me->id)
            ->where('currentUserRank.rank', 2));
    }

    public function test_student_leaderboard_can_be_filtered_by_class_and_level_interactively(): void
    {
        $me = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika',
            'student_class' => '4A',
            'level' => '1',
        ]);

        $peerDiffClass = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika',
            'student_class' => '5B',
            'level' => '1',
        ]);

        $this->makeSession($me, 85, 'MTK');
        $this->makeSession($peerDiffClass, 95, 'MTK');

        // Siswa memilih filter kelas 5B
        $response = $this->actingAs($me)->get(route('user.leaderboard', [
            'bidang' => 'Matematika',
            'kelas' => '5B',
            'level' => '1',
        ]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('selectedKelas', '5B')
            ->has('leaderboard', 1)
            ->where('leaderboard.0.user.id', $peerDiffClass->id));

        // Siswa memilih melihat semua kelas
        $responseAllKelas = $this->actingAs($me)->get(route('user.leaderboard', [
            'bidang' => 'Matematika',
            'kelas' => 'all',
            'level' => '1',
        ]));

        $responseAllKelas->assertOk();
        $responseAllKelas->assertInertia(fn (Assert $page) => $page
            ->where('selectedKelas', '')
            ->has('leaderboard', 2));
    }

    public function test_admin_leaderboard_can_filter_by_class_level_and_bidang(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $s1 = User::factory()->create(['role' => 'user', 'bidang' => 'MTK', 'student_class' => '7A', 'level' => '1']);
        $s2 = User::factory()->create(['role' => 'user', 'bidang' => 'MTK', 'student_class' => '7A', 'level' => '2']);
        $s3 = User::factory()->create(['role' => 'user', 'bidang' => 'IPA', 'student_class' => '7A', 'level' => '1']);

        $this->makeSession($s1, 80, 'MTK');
        $this->makeSession($s2, 90, 'MTK');
        $this->makeSession($s3, 95, 'IPA');

        // Admin filter: Matematika, Kelas 7A, Level 2
        $res = $this->actingAs($admin)->get(route('admin.leaderboard', [
            'bidang' => 'Matematika',
            'kelas' => '7A',
            'level' => '2',
        ]));

        $res->assertOk();
        $res->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Leaderboard/AdminLeaderboardIndex')
            ->where('selectedBidang', 'Matematika')
            ->where('selectedKelas', '7A')
            ->where('selectedLevel', '2')
            ->has('leaderboard', 1)
            ->where('leaderboard.0.user.id', $s2->id)
            ->where('leaderboard.0.level', '2'));
    }
}

