<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menjamin ikon MDI selalu dilayani dari bundel lokal, bukan dari CDN.
 *
 * Latar belakang: aplikasi memakai <Icon icon="mdi:..."> dari @iconify/vue.
 * Tanpa collection yang di-register, komponen itu memanggil
 * https://api.iconify.design saat runtime — yang membuat CSP harus longgar,
 * application bergantung pada pihak ketiga, dan ikon hilang saat API down.
 *
 * Collection dihasilkan oleh `npm run icons` (scripts/generate-icon-bundle.mjs).
 * Test ini gagal kalau ada nama ikon di source yang belum masuk collection —
 * jadi developer tidak bisa diam-diam menambahkan ikon baru lalu production
 * diam-diam kehilangan ikon tersebut.
 */
class IconBundleTest extends TestCase
{
    private const SOURCE_DIR = 'resources/js';
    private const BUNDLE = 'resources/js/icons/mdi.json';
    private const GENERATOR = 'scripts/generate-icon-bundle.mjs';

    /** @return array<string, true> */
    private function bundledIcons(): array
    {
        $path = base_path(self::BUNDLE);

        $this->assertFileExists(
            $path,
            'Collection ikon lokal belum ada. Jalankan: npm run icons'
        );

        $json = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($json, 'Collection ikon harus berupa JSON yang valid.');
        $this->assertSame('mdi', $json['prefix'] ?? null);

        $out = [];
        foreach (array_keys($json['icons'] ?? []) as $name) {
            $out[$name] = true;
        }

        $this->assertNotEmpty($out, 'Collection ikon kosong — tidak ada ikon yang dibundel.');

        return $out;
    }

    /**
     * Kumpulkan nama ikon dari seluruh sumber, dengan komentar dibuang.
     *
     * Mirrors logika scripts/generate-icon-bundle.mjs. Kalau logikanya berbeda,
     * test ini akan salah melapor.
     *
     * @return array<int, string>
     */
    private function iconNamesInSource(): array
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path(self::SOURCE_DIR), \FilesystemIterator::SKIP_DOTS)
        );

        $names = [];

        foreach ($files as $file) {
            $path = $file->getPathname();

            if (! preg_match('/\.(vue|js|ts|jsx|tsx)$/', $path)) {
                continue;
            }

            $content = (string) file_get_contents($path);

            $content = preg_replace('#/\*[\s\S]*?\*/#', ' ', $content) ?? $content;
            $content = preg_replace('#<!--[\s\S]*?-->#', ' ', $content) ?? $content;
            $content = preg_replace('/^[ \t]*(?:\/\/|\*)[^\n]*$/m', ' ', $content) ?? $content;

            preg_match_all('/\bmdi:([a-zA-Z0-9-]+)/', $content, $matches);

            foreach ($matches[1] as $name) {
                $names[$name] = true;
            }
        }

        $names = array_keys($names);
        sort($names);

        return $names;
    }

    public function test_every_icon_used_in_source_is_in_the_local_bundle(): void
    {
        $bundled = $this->bundledIcons();
        $used = $this->iconNamesInSource();

        $this->assertNotEmpty($used, 'Tidak ada nama ikon mdi: yang terdeteksi — pola scanner mungkin rusak.');

        $missing = array_values(array_filter($used, fn (string $n) => ! isset($bundled[$n])));

        $this->assertSame(
            [],
            $missing,
            "Ikon dipakai di source tapi tidak ada di collection lokal:\n  - mdi:"
            . implode("\n  - mdi:", $missing)
            . "\n\nJalankan: npm run icons"
        );
    }

    public function test_bundle_does_not_contain_unused_icons(): void
    {
        // Bundle harus berisi tepat ikon yang dipakai. Sisa ikon membengkakkan
        // ukuran JS tanpa manfaat.
        $bundled = array_keys($this->bundledIcons());
        $used = $this->iconNamesInSource();

        $unused = array_values(array_diff($bundled, $used));

        $this->assertSame(
            [],
            $unused,
            "Collection lokal berisi ikon yang tidak lagi dipakai:\n  - mdi:"
            . implode("\n  - mdi:", $unused)
            . "\n\nJalankan: npm run icons"
        );
    }

    public function test_bundle_stays_small(): void
    {
        // Collection penuh MDI berukuran ~1,5 MB dan akan memperlambat load.
        // Bundle di bawah 120 KB berarti pemindaian ikon masih bekerja.
        $size = (int) filesize(base_path(self::BUNDLE));

        $this->assertLessThan(
            120 * 1024,
            $size,
            "Collection ikon terlalu besar ({$size} byte)..Collection penuh MDI akan "
            . 'membengkakkan bundel — jalankan `npm run icons` untuk memangkas.'
        );
    }

    public function test_local_collection_is_registered_before_app_mounts(): void
    {
        // Kalau import ini hilang atau dipindah setelah mount, @iconify/vue akan
        // fallback ke request jaringan dan CSP yang ketat akan memblokirnya.
        $app = (string) file_get_contents(base_path('resources/js/app.js'));

        $this->assertStringContainsString(
            "./icons",
            $app,
            'resources/js/app.js harus meng-import collection ikon lokal.'
        );

        $importPos = strpos($app, "./icons");
        // Cari PANGGILAN createInertiaApp({, bukan baris import-nya — kalau
        // tidak, posisi import selalu lebih dulu dan assertion ini tidak berarti.
        $mountPos = strpos($app, 'createInertiaApp({');

        $this->assertNotFalse($importPos, 'Import collection ikon tidak ditemukan di app.js.');
        $this->assertNotFalse($mountPos, 'Panggilan createInertiaApp({ tidak ditemukan di app.js.');
        $this->assertLessThan(
            $mountPos,
            $importPos,
            'Import collection ikon harus berada sebelum createInertiaApp().'
        );
    }

    public function test_csp_does_not_whitelist_the_iconify_cdn(): void
    {
        // Ini tujuan akhirnya: tidak ada host Iconify di CSP, karena tidak ada
        // lagi request ke sana sama sekali.
        //
        // Header diuji lewat respons nyata, bukan dengan membaca teks middleware,
        // supaya komentar di dalam kode tidak ikut terhitung.
        config(['app.env' => 'production']);

        $csp = (string) $this->get('/login')
            ->assertOk()
            ->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $csp, 'CSP harus dikirim di environment production.');

        foreach (['api.iconify.design', 'api.simplesvg.com', 'api.unisvg.com', 'iconify'] as $host) {
            $this->assertStringNotContainsString(
                $host,
                $csp,
                "CSP tidak boleh mengizinkan host Iconify '{$host}' — ikon dilayani dari collection lokal."
            );
        }
    }

    public function test_every_icon_import_uses_the_offline_entry_point(): void
    {
        // Entry point `@iconify/vue` memuat ~29 KB kode jaringan (fetch, API
        // host list) dan akan memanggil api.iconify.design untuk ikon yang belum
        // terdaftar. Entry point `/offline` ekspor API yang sama persis
        // (`Icon`, `addCollection`, `addIcon`) tanpa kode jaringan sama sekali.
        //
        // Kalau ada file yang masih import entry point utama, aplikasi diam-diam
        // bergantung pada CDN lagi.
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path(self::SOURCE_DIR), \FilesystemIterator::SKIP_DOTS)
        );

        $offenders = [];
        $checked = 0;

        foreach ($files as $file) {
            $path = $file->getPathname();

            if (! preg_match('/\.(vue|js|ts)$/', $path)) {
                continue;
            }

            $content = (string) file_get_contents($path);
            $checked++;

            // Cocokkan import yang TEPAT ke entry point utama, bukan /offline.
            if (preg_match('#from\s+[\'"]@iconify/vue[\'"]#', $content)) {
                $offenders[] = str_replace(base_path() . '/', '', $path);
            }
        }

        $this->assertGreaterThan(0, $checked, 'Tidak ada file sumber yang diperiksa.');

        $this->assertSame(
            [],
            $offenders,
            "File berikut masih import '@iconify/vue' (entry point yang bisa memanggil API):\n  - "
            . implode("\n  - ", $offenders)
            . "\n\nGanti dengan '@iconify/vue/offline'."
        );
    }

    public function test_offline_entry_point_is_what_gets_bundled(): void
    {
        // Guard terakhir: pastikan build benar-benar memakai build offline.
        // Dicek dari file paketnya, bukan dari asumsi.
        $offline = base_path('node_modules/@iconify/vue/dist/offline.mjs');

        if (! is_file($offline)) {
            $this->markTestSkipped('Package @iconify/vue belum terinstall.');
        }

        $source = (string) file_get_contents($offline);

        $this->assertDoesNotMatchRegularExpression(
            '#api\.iconify\.design|api\.simplesvg|api\.unisvg#',
            $source,
            'Entry point offline @iconify/vue seharusnya tidak memuat host API sama sekali.'
        );
    }

    public function test_generator_script_exists_and_is_runnable(): void
    {
        $this->assertFileExists(
            base_path(self::GENERATOR),
            'Generator collection ikon harus ada agar bundle bisa dibuat ulang.'
        );

        $package = json_decode((string) file_get_contents(base_path('package.json')), true);

        $this->assertArrayHasKey('icons', $package['scripts'] ?? [], 'package.json harus punya script "icons".');
    }
}
