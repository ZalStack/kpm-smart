<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PrBidangSortFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Package::query()->delete();

        // Buat paket Matematika
        Package::create([
            'title' => 'PR Matematika Pekan 1',
            'description' => 'Tugas Matematika',
            'bidang' => 'MTK',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'questions' => [
                ['id' => 'q1', 'question' => '1+1=?', 'options' => ['2', '3'], 'correct_answer' => '2'],
            ],
        ]);

        // Buat paket IPA
        Package::create([
            'title' => 'PR IPA Pekan 1',
            'description' => 'Tugas IPA',
            'bidang' => 'IPA',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
            'questions' => [
                ['id' => 'q2', 'question' => 'H2O adalah?', 'options' => ['Air', 'Api'], 'correct_answer' => 'Air'],
            ],
        ]);
    }

    public function test_matematika_student_only_gets_matematika_packages_and_no_ipa(): void
    {
        $studentMtk = User::factory()->create([
            'role' => 'user',
            'bidang' => 'MTK',
            'is_active' => true,
        ]);

        $response = $this->actingAs($studentMtk)->get(route('user.packages.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 1)
            ->where('packages.data.0.title', 'PR Matematika Pekan 1')
            ->where('userBidangGroup', 'Matematika')
        );
    }

    public function test_ipa_student_only_gets_ipa_packages_and_no_matematika(): void
    {
        $studentIpa = User::factory()->create([
            'role' => 'user',
            'bidang' => 'IPA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($studentIpa)->get(route('user.packages.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 1)
            ->where('packages.data.0.title', 'PR IPA Pekan 1')
            ->where('userBidangGroup', 'IPA')
        );
    }

    public function test_matematika_student_cannot_view_or_start_ipa_package(): void
    {
        $studentMtk = User::factory()->create([
            'role' => 'user',
            'bidang' => 'MTK',
            'is_active' => true,
        ]);

        $ipaPackage = Package::where('bidang', 'IPA')->first();

        // Akses detail IPA
        $resShow = $this->actingAs($studentMtk)->get(route('user.packages.show', $ipaPackage->id));
        $resShow->assertRedirect(route('user.packages.index'));
        $resShow->assertSessionHas('error', 'Maaf, tugas ini khusus untuk bidang studi IPA. Anda terdaftar pada bidang Matematika.');

        // Mulai PR IPA
        $resStart = $this->actingAs($studentMtk)->post(route('user.practice.start', $ipaPackage->id));
        $resStart->assertRedirect(route('user.packages.index'));
        $resStart->assertSessionHas('error', 'Maaf, tugas ini khusus untuk bidang studi IPA. Anda terdaftar pada bidang Matematika.');
    }

    public function test_ipa_student_cannot_view_or_start_matematika_package(): void
    {
        $studentIpa = User::factory()->create([
            'role' => 'user',
            'bidang' => 'IPA',
            'is_active' => true,
        ]);

        $mtkPackage = Package::where('bidang', 'MTK')->first();

        // Akses detail MTK
        $resShow = $this->actingAs($studentIpa)->get(route('user.packages.show', $mtkPackage->id));
        $resShow->assertRedirect(route('user.packages.index'));
        $resShow->assertSessionHas('error', 'Maaf, tugas ini khusus untuk bidang studi Matematika. Anda terdaftar pada bidang IPA.');

        // Mulai PR MTK
        $resStart = $this->actingAs($studentIpa)->post(route('user.practice.start', $mtkPackage->id));
        $resStart->assertRedirect(route('user.packages.index'));
        $resStart->assertSessionHas('error', 'Maaf, tugas ini khusus untuk bidang studi Matematika. Anda terdaftar pada bidang IPA.');
    }

    public function test_sorting_by_title_asc(): void
    {
        // Buat user umum
        $user = User::factory()->create([
            'role' => 'user',
            'bidang' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('user.packages.index', ['sort' => 'title_asc']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Packages/PackageList')
            ->has('packages.data', 2)
            ->where('packages.data.0.title', 'PR IPA Pekan 1')
            ->where('packages.data.1.title', 'PR Matematika Pekan 1')
        );
    }
}
