<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_login_is_throttled_after_too_many_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'victim@example.com',
            'password' => 'correct-password',
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
        ]);

        foreach (range(1, 5) as $i) {
            $this->post(route('login'), [
                'email' => 'victim@example.com',
                'password' => 'wrong-password-' . $i,
            ]);
        }

        // Percobaan ke-6 meski password benar tetap ditolak karena lockout.
        $response = $this->post(route('login'), [
            'email' => 'victim@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());
    }

    public function test_login_success_clears_throttle_and_regenerates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'correct-password',
            'role' => 'user',
            'is_active' => true,
            'is_verified' => true,
        ]);

        // Satu percobaan gagal tidak mengunci akun selamanya.
        $this->post(route('login'), [
            'email' => 'member@example.com',
            'password' => 'wrong-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'member@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertTrue(auth()->check());
    }

    public function test_api_login_is_rate_limited(): void
    {
        // Tanpa rate limit, /api/v1/auth/login bisa di-brute-force tanpa batas.
        User::factory()->create([
            'email' => 'api-victim@example.com',
            'password' => 'correct-password',
            'role' => 'user',
            'is_active' => true,
        ]);

        foreach (range(1, 5) as $i) {
            $this->postJson(route('api.auth.login'), [
                'email' => 'api-victim@example.com',
                'password' => 'wrong-password-' . $i,
            ])->assertStatus(401);
        }

        // Percobaan ke-6 harus diblokir rate limiter, bukan lagi 401 biasa.
        $this->postJson(route('api.auth.login'), [
            'email' => 'api-victim@example.com',
            'password' => 'correct-password',
        ])->assertStatus(429);
    }

    public function test_seeder_is_blocked_in_production(): void
    {
        // Seeder membuat admin@pkalitbang.id / password123. Kalau boleh jalan di
        // production, siapa pun yang tahu repo ini bisa login sebagai admin.
        //
        // `app()->environment()` membaca $app['env'], bukan config('app.env'),
        // jadi environment harus diganti lewat detectEnvironment().
        $this->app->detectEnvironment(fn () => 'production');

        $this->assertTrue($this->app->environment('production'));

        // Seeder memakai $this->command untuk mencetak peringatan, dan output
        // konsol di dalam test dimock oleh Laravel.
        $this->withoutMockingConsoleOutput();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'admin@pkalitbang.id']);
        $this->assertDatabaseMissing('users', ['email' => 'user@test.com']);
    }

    public function test_production_env_template_never_enables_debug(): void
    {
        $template = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_DEBUG=false', $template);
        $this->assertStringNotContainsString('APP_DEBUG=true', $template);
        $this->assertStringNotContainsString('APP_KEY=base64:', $template);
    }
}
