<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_admin_route(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_user_route(): void
    {
        $response = $this->get(route('user.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_role_middleware_directly_redirects_unauthenticated_user_with_error_flash(): void
    {
        $middleware = new \App\Http\Middleware\RoleMiddleware();
        $request = \Illuminate\Http\Request::create('/admin/dashboard', 'GET');
        $request->setLaravelSession(app('session.store'));

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'admin');

        $this->assertTrue($response->isRedirect(route('login')));
        $this->assertEquals('Silakan login terlebih dahulu!', session('error'));
    }

    public function test_inactive_user_is_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.');
        $this->assertFalse(auth()->check());
    }

    public function test_inactive_user_accessing_admin_route_is_checked_for_inactivity_first(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.');
        $this->assertFalse(auth()->check());
    }

    public function test_regular_user_cannot_access_admin_route_and_is_redirected_to_user_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('user.dashboard'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }

    public function test_admin_cannot_access_user_route_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('user.dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }

    public function test_active_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_active_user_can_access_user_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertOk();
    }
}
