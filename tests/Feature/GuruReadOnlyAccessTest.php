<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruReadOnlyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_access_admin_dashboard_in_read_only_mode(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);

        $response = $this->actingAs($guru)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_guru_can_view_packages_index(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);

        $response = $this->actingAs($guru)->get(route('admin.packages.index'));

        $response->assertOk();
    }

    public function test_guru_is_forbidden_from_creating_packages(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);

        $response = $this->actingAs($guru)->post(route('admin.packages.store'), [
            'name' => 'Paket Baru Guru',
        ]);

        $response->assertStatus(403);
    }

    public function test_guru_is_forbidden_from_creating_users(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);

        $response = $this->actingAs($guru)->post(route('admin.users.store'), [
            'name' => 'Siswa Baru',
            'email' => 'siswa.baru@example.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_guru_appears_in_login_autocomplete_list(): void
    {
        User::factory()->create([
            'name' => 'Ustadz Ahmad Fauzi',
            'email' => 'ahmad.guru@kpm.com',
            'role' => 'guru',
            'is_active' => true,
        ]);

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('Auth/Login')
                ->where('students', fn ($students) =>
                    collect($students)->contains(fn ($s) => $s['email'] === 'ahmad.guru@kpm.com' && $s['role'] === 'guru')
                )
        );
    }
}
