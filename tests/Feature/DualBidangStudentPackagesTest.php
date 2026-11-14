<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DualBidangStudentPackagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Package::query()->delete();
    }

    public function test_dual_bidang_student_sees_both_mtk_and_ipa_matching_kelas_and_level(): void
    {
        $dualStudent = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika & IPA',
            'student_class' => 'Kelas 7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        // Paket yang harus muncul:
        $mtkPkg = Package::create([
            'title' => 'PR Matematika Pekan 1',
            'description' => 'Deskripsi',
            'bidang' => 'MTK',
            'kelas' => 'Kelas 7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        $ipaPkg = Package::create([
            'title' => 'PR IPA Pekan 1',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'kelas' => 'Kelas 7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        // Paket beda kelas (Kelas 8): TIDAK BOLEH MUNCUL
        Package::create([
            'title' => 'PR MTK Kelas 8',
            'description' => 'Deskripsi',
            'bidang' => 'MTK',
            'kelas' => 'Kelas 8',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        // Paket beda level (Berbakat B): TIDAK BOLEH MUNCUL
        Package::create([
            'title' => 'PR IPA Level B',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'kelas' => 'Kelas 7',
            'level' => 'Berbakat B',
            'is_active' => true,
        ]);

        // Akses halaman daftar PR
        $res = $this->actingAs($dualStudent)->get(route('user.packages.index'));
        $res->assertOk();
        $res->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 2)
            ->where('userBidangGroup', 'Matematika & IPA')
        );
        $titles = collect($res->inertiaProps('packages.data'))->pluck('title')->toArray();
        $this->assertContains('PR Matematika Pekan 1', $titles);
        $this->assertContains('PR IPA Pekan 1', $titles);

        // Akses dashboard web
        $resDash = $this->actingAs($dualStudent)->get(route('user.dashboard'));
        $resDash->assertOk();
        $resDash->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/UserDashboard')
            ->has('packages', 2)
        );
        $dashTitles = collect($resDash->inertiaProps('packages'))->pluck('title')->toArray();
        $this->assertContains('PR Matematika Pekan 1', $dashTitles);
        $this->assertContains('PR IPA Pekan 1', $dashTitles);
    }

    public function test_dual_bidang_student_with_split_levels_sees_correct_package_for_each_bidang(): void
    {
        // Siswa dual dengan level MTK = Berbakat A, dan level IPA = Berbakat B
        $dualStudent = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika & IPA',
            'student_class' => '7',
            'level' => 'Berbakat A (MTK), Berbakat B (IPA)',
            'is_active' => true,
        ]);

        // Paket MTK Level A untuk Kelas 7 -> Harus muncul
        $mtkPkgA = Package::create([
            'title' => 'PR Matematika Level A',
            'description' => 'Deskripsi',
            'bidang' => 'MTK',
            'kelas' => '7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        // Paket IPA Level B untuk Kelas 7 -> Harus muncul
        $ipaPkgB = Package::create([
            'title' => 'PR IPA Level B',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'kelas' => '7',
            'level' => 'Berbakat B',
            'is_active' => true,
        ]);

        // Paket MTK Level B -> TIDAK BOLEH MUNCUL (siswa level MTK-nya A)
        Package::create([
            'title' => 'PR Matematika Level B',
            'description' => 'Deskripsi',
            'bidang' => 'MTK',
            'kelas' => '7',
            'level' => 'Berbakat B',
            'is_active' => true,
        ]);

        // Paket IPA Level A -> TIDAK BOLEH MUNCUL (siswa level IPA-nya B)
        Package::create([
            'title' => 'PR IPA Level A',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'kelas' => '7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        $res = $this->actingAs($dualStudent)->get(route('user.packages.index'));
        $res->assertOk();
        $res->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 2)
        );

        // Pastikan matchesStudent mengizinkan akses ke kedua paket yang cocok
        $this->assertTrue($mtkPkgA->matchesStudent($dualStudent)['allowed']);
        $this->assertTrue($ipaPkgB->matchesStudent($dualStudent)['allowed']);
    }

    public function test_dual_bidang_student_can_filter_by_specific_bidang(): void
    {
        $dualStudent = User::factory()->create([
            'role' => 'user',
            'bidang' => 'Matematika & IPA',
            'student_class' => 'Kelas 7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        Package::create([
            'title' => 'PR Matematika Pekan 1',
            'description' => 'Deskripsi',
            'bidang' => 'MTK',
            'kelas' => 'Kelas 7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        Package::create([
            'title' => 'PR IPA Pekan 1',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'kelas' => 'Kelas 7',
            'level' => 'Berbakat A',
            'is_active' => true,
        ]);

        // Filter Matematika saja
        $resMtk = $this->actingAs($dualStudent)->get(route('user.packages.index', ['bidang' => 'Matematika']));
        $resMtk->assertOk();
        $resMtk->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 1)
            ->where('packages.data.0.title', 'PR Matematika Pekan 1')
        );

        // Filter IPA saja
        $resIpa = $this->actingAs($dualStudent)->get(route('user.packages.index', ['bidang' => 'IPA']));
        $resIpa->assertOk();
        $resIpa->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 1)
            ->where('packages.data.0.title', 'PR IPA Pekan 1')
        );
    }
}
