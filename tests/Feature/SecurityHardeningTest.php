<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
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

    public function test_public_support_endpoint_is_rate_limited(): void
    {
        RateLimiter::clear('support:127.0.0.1');

        foreach (range(1, 10) as $i) {
            $this->post(route('support.submit'), [
                'question' => 'Pertanyaan uji coba nomor ' . $i,
            ])->assertStatus(200);
        }

        // Permintaan ke-11 dalam satu menit diblokir rate limiter.
        $this->post(route('support.submit'), [
            'question' => 'Pertanyaan setelah batas limit',
        ])->assertStatus(429);
    }
}
