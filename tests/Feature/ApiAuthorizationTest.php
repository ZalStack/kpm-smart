<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Penjaga ini memverifikasi SECARA KESELURUHAN bahwa tidak ada endpoint API yang
 * bisa diakses tanpa autentikasi.
 *
 * Alasan test ini ada: endpoint admin pernah terbuka untuk publik dan ada test
 * yang justru menghijaukan perilaku tersebut. Test per-endpoint mudah lupa
 * ditambahkan route baru, jadi test ini memindai SELURUH tabel rute.
 */
class ApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private const ANSWER_MARKER = 'RAHASIA-JAWABAN';

    private function makePackage(): Package
    {
        return Package::create([
            'title' => 'Paket Uji',
            'description' => 'Deskripsi',
            'bidang' => 'IPA',
            'level' => 'SD',
            'is_active' => true,
            'cards' => [['id' => 'card-1', 'title' => 'Bagian A']],
            'questions' => [[
                'id' => 'q1',
                'card_id' => 'card-1',
                'question' => 'Apa itu fotosintesis?',
                'options' => ['A', 'B', 'C', 'D'],
                'correct_answer' => 'A',
                'explanation' => self::ANSWER_MARKER,
            ]],
        ]);
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string}>
     *   [method, path, uri]
     */
    private function securedApiRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            // Endpoint login memang harus bisa diakses tanpa token.
            if ($uri === 'api/v1/auth/login') {
                continue;
            }

            $method = collect($route->methods())
                ->first(fn ($m) => in_array($m, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true));

            if (! $method) {
                continue;
            }

            $routes[] = [
                $method,
                str_replace(
                    ['{package}', '{user}', '{id}', '{session}', '{announcement}'],
                    ['1', '2', '1', '1', '1'],
                    $uri
                ),
                $uri,
            ];
        }

        return $routes;
    }

    public function test_every_api_endpoint_rejects_anonymous_requests(): void
    {
        $this->makePackage();
        $violations = [];

        foreach ($this->securedApiRoutes() as [$method, $path, $uri]) {
            $response = $this->json($method, $path);

            if ($response->status() !== 401) {
                $violations[] = "{$method} /{$uri} -> HTTP {$response->status()} (harus 401)";
            }

            if (str_contains($response->getContent(), self::ANSWER_MARKER)) {
                $violations[] = "{$method} /{$uri} -> membocorkan kunci jawaban";
            }
        }

        $this->assertSame([], $violations, "Endpoint API yang tidak terkunci:\n" . implode("\n", $violations));
    }

    public function test_student_cannot_reach_admin_endpoints_or_read_other_users(): void
    {
        $this->makePackage();
        $victim = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'victim@example.test',
            'address' => 'ALAMAT-RAHASIA',
        ]);
        $student = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'student@example.test',
        ]);

        Sanctum::actingAs($student);

        $violations = [];

        foreach ($this->securedApiRoutes() as [$method, $path, $uri]) {
            $response = $this->json($method, $path . '?user_id=' . $victim->id);
            $body = $response->getContent();

            if ($response->status() === 200 && str_starts_with($uri, 'api/admin/')) {
                $violations[] = "siswa bisa mengakses endpoint admin: {$method} /{$uri}";
            }

            foreach (['victim@example.test', 'ALAMAT-RAHASIA', self::ANSWER_MARKER] as $secret) {
                if (str_contains($body, $secret)) {
                    $violations[] = "{$method} /{$uri} -> membocorkan '{$secret}'";
                }
            }
        }

        $this->assertSame([], $violations, "Pelanggaran otorisasi:\n" . implode("\n", $violations));
    }

    public function test_user_id_query_parameter_cannot_override_identity(): void
    {
        $victim = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'victim@example.test',
        ]);
        $student = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'student@example.test',
        ]);

        Sanctum::actingAs($student);

        $me = $this->getJson(route('api.auth.me') . '?user_id=' . $victim->id);
        $me->assertOk()->assertJsonPath('data.user.email', 'student@example.test');

        $profile = $this->getJson(route('api.user.profile') . '?user_id=' . $victim->id);
        $profile->assertOk()->assertJsonPath('data.email', 'student@example.test');

        $history = $this->getJson(route('api.user.practice.history') . '?user_id=' . $victim->id);
        $history->assertOk();

        $userIds = collect($history->json('data.data'))->pluck('user_id')->unique()->all();
        $this->assertNotContains($victim->id, $userIds);
    }

    public function test_admin_may_still_target_a_specific_user(): void
    {
        // Admin tetap boleh melihat data user tertentu lewat ?user_id= — itu
        // kebutuhan operasional yang sah. Yang dilarang adalah role non-admin.
        $target = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'target@example.test',
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'email' => 'admin@example.test',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson(route('api.user.profile') . '?user_id=' . $target->id)
            ->assertOk()
            ->assertJsonPath('data.email', 'target@example.test');
    }
}
