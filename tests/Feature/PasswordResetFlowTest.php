<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_pages_render_correctly(): void
    {
        // Halaman form email.
        $this->get(route('password.request'))
            ->assertStatus(200)
            ->assertSee('Lupa Kata Sandi');

        // Halaman "cek email" tanpa session -> diarahkan ke form awal.
        $this->get(route('password.sent'))
            ->assertRedirect(route('password.request'));

        // Halaman "cek email" dengan session -> tampil normal.
        $this->withSession(['reset_email_sent' => 'member@example.com'])
            ->get(route('password.sent'))
            ->assertStatus(200)
            ->assertSee('Cek Email Kamu');
    }

    public function test_reset_request_sends_email_and_stores_hashed_token(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'email' => 'member@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'member@example.com',
        ]);

        // Diarahkan ke halaman konfirmasi "cek email".
        $response->assertRedirect(route('password.sent'));
        $this->followRedirects($response)->assertSee('member@example.com');

        // Token tersimpan sebagai hash SHA-256 (64 karakter hex), bukan teks asli.
        $record = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($record);
        $this->assertEquals(64, strlen($record->token_hash));
        $this->assertTrue(ctype_xdigit($record->token_hash));

        // Email berisi tautan reset terkirim (di lokal dicatat ke log).
        Log::shouldHaveReceived('info')->once();
    }

    public function test_unknown_email_receives_same_response_to_prevent_enumeration(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'tidak-terdaftar@example.com',
        ]);

        // Respons identik dengan email terdaftar: tetap ke halaman "cek email",
        // tanpa membocorkan apakah email terdaftar atau tidak.
        $response->assertRedirect(route('password.sent'));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'tidak-terdaftar@example.com',
        ]);
    }

    public function test_valid_token_shows_form_and_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $token = str_repeat('ab', 32);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        // Token valid -> form reset tampil.
        $this->get(route('password.reset', ['token' => $token]))
            ->assertStatus(200)
            ->assertSee('Buat Password Baru');

        // Token asal-asalan -> ditolak.
        $this->get(route('password.reset', ['token' => str_repeat('ff', 32)]))
            ->assertRedirect(route('password.request'));

        // Token dengan format salah -> ditolak.
        $this->get(route('password.reset', ['token' => 'hack']))
            ->assertRedirect(route('password.request'));
    }

    public function test_expired_token_cannot_be_used(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $token = str_repeat('cd', 32);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(31),
        ]);

        $this->get(route('password.reset', ['token' => $token]))
            ->assertRedirect(route('password.request'));
    }

    public function test_password_can_be_reset_with_valid_token_only_once(): void
    {
        $user = User::factory()->create([
            'email' => 'single@example.com',
            'password' => 'old-password',
            'role' => 'user',
        ]);

        $oldRememberToken = $user->remember_token;

        $token = str_repeat('ef', 32);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        // Reset berhasil.
        $this->post(route('password.reset.submit'), [
            'token' => $token,
            'password' => 'new-secret-pass',
            'password_confirmation' => 'new-secret-pass',
        ])->assertRedirect(route('login'));

        $user->refresh();

        // Password baru bisa dipakai untuk login.
        $this->assertTrue(auth()->validate([
            'email' => 'single@example.com',
            'password' => 'new-secret-pass',
        ]));

        // Keamanan: remember_token dirotasi agar sesi lama tidak berlaku.
        $this->assertNotEquals($oldRememberToken, $user->remember_token);

        // Keamanan: token hanya bisa dipakai SEKALI (sudah dihapus).
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'single@example.com',
        ]);

        // Pemakaian ulang token yang sama -> ditolak.
        $this->post(route('password.reset.submit'), [
            'token' => $token,
            'password' => 'another-new-pass',
            'password_confirmation' => 'another-new-pass',
        ])->assertRedirect(route('password.request'));
    }

    public function test_new_request_invalidates_previous_token(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Token pertama.
        $firstToken = str_repeat('11', 32);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token_hash' => hash('sha256', $firstToken),
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        // Minta tautan baru -> token lama harus tidak berlaku.
        Log::spy();
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.sent'));

        $records = DB::table('password_reset_tokens')->where('email', $user->email)->get();
        $this->assertCount(1, $records);
        $this->assertNotEquals(hash('sha256', $firstToken), $records[0]->token_hash);

        // Token lama sudah tidak bisa dipakai.
        $this->get(route('password.reset', ['token' => $firstToken]))
            ->assertRedirect(route('password.request'));
    }
}
