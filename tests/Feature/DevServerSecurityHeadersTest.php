<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Mengatur perilaku CSP + header keamanan terhadap Vite dev server.
 *
 * Latar belakang: ada orang yang memakai `APP_ENV=production` di mesin lokal
 * (untuk menguji CSP seperti di server). Kalau allowance dev-server ikut
 * digabung dengan APP_ENV, seluruh aset `npm run dev` diblokir browser dan
 * halaman tidak bisa dipakai sama sekali.
 *
 * Aturan yang dikunci di sini:
 * 1. Dev server terdeteksi dari file `public/hot` (fakta filesystem), BUKAN
 *    dari APP_ENV.
 * 2. HSTS / COOP / CORP / COEP tetap terpasang di production dan TIDAK boleh
 *    dipengaruhi oleh keberadaan `public/hot`.
 * 3. Tanpa `public/hot`, tidak ada origin dev yang boleh bocor ke CSP.
 */
class DevServerSecurityHeadersTest extends TestCase
{
    private string $hotPath;

    /** Isi asli `public/hot` sebelum test dibiarkan, supaya bisa dikembalikan. */
    private ?string $hotBackup = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotPath = public_path('hot');

        // Test ini menulis/menghapus file `public/hot` yang BENDAKARYANG dibaca
        // middleware. Kalau developer sedang menjalankan `npm run dev`, file itu
        // milik mereka — harus dikembalikan apa adanya, bukan dihapus.
        $this->hotBackup = is_file($this->hotPath)
            ? (string) file_get_contents($this->hotPath)
            : null;
    }

    protected function tearDown(): void
    {
        if ($this->hotBackup !== null) {
            file_put_contents($this->hotPath, $this->hotBackup);
        } elseif (is_file($this->hotPath)) {
            @unlink($this->hotPath);
        }

        parent::tearDown();
    }

    private function writeHotFile(string $url): void
    {
        file_put_contents($this->hotPath, $url);
    }

    private function cspFor(string $url): string
    {
        return (string) $this->get($url)
            ->assertOk()
            ->headers->get('Content-Security-Policy');
    }

    public function test_hsts_and_cross_origin_headers_ignore_the_hot_file(): void
    {
        // Keberadaan `public/hot` tidak boleh mematikan HSTS. Ini regresi yang
        // pernah terjadi: HSTS dilewati begitu saja hanya karena dev server aktif.
        config(['app.env' => 'production']);
        $this->writeHotFile('http://127.0.0.1:5173');

        $response = $this->get('/login');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Embedder-Policy', 'credentialless');
    }

    public function test_dev_server_origins_are_allowed_even_when_app_env_is_production(): void
    {
        // Kasus yang paling sering dilaporkan: APP_ENV=production di mesin lokal.
        config(['app.env' => 'production']);
        $this->writeHotFile('http://127.0.0.1:5173');

        $csp = $this->cspFor('/login');

        $this->assertStringContainsString('http://127.0.0.1:5173', $csp, 'script-src harus mengizinkan dev server.');
        $this->assertStringContainsString('ws://127.0.0.1:5173', $csp, 'connect-src harus mengizinkan HMR via WebSocket.');
    }

    public function test_no_csp_in_local_environment_even_with_hot_file(): void
    {
        // Di environment non-production tidak ada CSP sama sekali, jadi
        // pertanyaan "apakah origin dev diizinkan" tidak relevan — tidak ada
        // CSP yang perlu dilonggarkan. Ini yang membuat `npm run dev` aman
        // di mesin lokal tanpa APP_ENV=production.
        config(['app.env' => 'local']);
        $this->writeHotFile('http://127.0.0.1:5173');

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeaderMissing('Content-Security-Policy');
    }

    public function test_no_dev_origins_leak_when_hot_file_is_absent(): void
    {
        config(['app.env' => 'production']);

        if (is_file($this->hotPath)) {
            @unlink($this->hotPath);
        }

        $csp = $this->cspFor('/login');

        $this->assertStringNotContainsString('127.0.0.1:5173', $csp);
        $this->assertStringNotContainsString('localhost:5173', $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
    }

    public function test_hot_file_port_is_respected(): void
    {
        config(['app.env' => 'production']);
        $this->writeHotFile('http://127.0.0.1:5174');

        $csp = $this->cspFor('/login');

        $this->assertStringContainsString('http://127.0.0.1:5174', $csp);
        $this->assertStringNotContainsString('http://127.0.0.1:5173', $csp);
    }

    public function test_csp_is_not_emitted_outside_production(): void
    {
        // Di environment non-production CSP tidak dikirim sama sekali, mengikuti
        // perilaku awal middleware.
        config(['app.env' => 'local']);

        $this->get('/')->assertHeaderMissing('Content-Security-Policy');
    }

    public function test_csp_never_contains_a_bracketed_ipv6_source(): void
    {
        // Grammar host-source di spesifikasi CSP tidak mengizinkan `[` / `]`.
        // Browser menolak sumber seperti `http://[::1]:5173` dan mencetak
        // "contains an invalid source" di console pada setiap page load.
        config(['app.env' => 'production']);
        $this->writeHotFile('http://127.0.0.1:5173');

        $csp = $this->cspFor('/login');

        $this->assertStringNotContainsString('[::1]', $csp);
        $this->assertStringNotContainsString('[', $csp);
    }

    public function test_every_csp_source_token_is_syntactically_valid(): void
    {
        // Menangkap sumber rusak apa pun (bukan hanya IPv6) sebelum sampai ke
        // browser, karena setiap sumber invalid memunculkan warning di console.
        config(['app.env' => 'production']);
        $this->writeHotFile('http://127.0.0.1:5173');

        $csp = $this->cspFor('/login');

        $keywords = [
            "'self'", "'none'", "'unsafe-inline'", "'unsafe-eval'", "'unsafe-hashes'",
            "'strict-dynamic'", "'report-sample'", "'unsafe-allow-redirects'", '*',
        ];

        foreach (explode(';', $csp) as $part) {
            $tokens = preg_split('/\s+/', trim($part), -1, PREG_SPLIT_NO_EMPTY);
            if (! $tokens) {
                continue;
            }

            array_shift($tokens); // buang nama direktif

            foreach ($tokens as $token) {
                if (in_array($token, $keywords, true)) {
                    continue;
                }

                if (str_starts_with($token, 'data:') || str_starts_with($token, 'blob:')) {
                    continue;
                }

                $this->assertMatchesRegularExpression(
                    '#^(https?|wss?)://[^\s\[\]]+$#',
                    $token,
                    "Sumber CSP tidak valid: '{$token}' (dari direktif '{$part}')"
                );
            }
        }
    }

    public function test_connect_src_stays_strict_for_all_environments(): void
    {
        // Dulu `connect-src` memuat host api.iconify.design karena <Icon> mengambil
        // ikon dari CDN. Sekarang ikon dibundel lokal (lihat IconBundleTest),
        // jadi connect-src cukup 'self' — lebih ketat, tanpa pihak ketiga.
        config(['app.env' => 'production']);

        if (is_file($this->hotPath)) {
            @unlink($this->hotPath);
        }

        $csp = $this->cspFor('/login');

        $this->assertStringContainsString("connect-src 'self'", $csp);
        $this->assertStringNotContainsString('iconify', $csp);
    }
}
