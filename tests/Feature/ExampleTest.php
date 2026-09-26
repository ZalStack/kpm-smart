<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman root mengalihkan pengunjung ke halaman login.
     *
     * Catatan: `assertStatus(200)` tidak pernah berlaku di sini karena `/` memang
     * redirect. Selain itu aplikasi ini memakai Inertia, jadi teks halaman berada
     * di payload JSON — bukan di HTML — sehingga `assertSee` tidak bisa dipakai
     * untuk memverifikasi isi halaman. Gunakan assertInertia.
     */
    public function test_the_application_redirects_root_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_health_check_endpoint_responds(): void
    {
        $this->get('/up')->assertOk();
    }
}
