<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Announcement;
use App\Models\LoginLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected Package $package;
    protected PracticeSession $session;
    protected LeaveRequest $leaveRequest;
    protected Notification $notification;
    protected Announcement $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@kpm.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'email' => 'student@kpm.test',
            'password' => 'password',
            'role' => 'user',
            'bidang' => 'IPA',
            'level' => 'SD',
            'is_active' => true,
        ]);

        $this->package = Package::create([
            'title' => 'Tugas Sains 1',
            'description' => 'Deskripsi paket',
            'bidang' => 'IPA',
            'level' => 'SD',
            'is_active' => true,
            'cards' => [
                ['id' => 'card-1', 'title' => 'Bagian A', 'order' => 1],
            ],
            'questions' => [
                [
                    'id' => 'q1',
                    'card_id' => 'card-1',
                    'question' => 'Apa itu fotosintesis?',
                    'options' => ['A', 'B', 'C', 'D'],
                    'correct_answer' => 'A',
                    'type' => 'pilihan_ganda',
                ],
            ],
        ]);

        $this->session = PracticeSession::create([
            'user_id' => $this->user->id,
            'package_id' => $this->package->id,
            'card_id' => 'card-1',
            'total_question' => 1,
            'correct_answer' => 1,
            'wrong_answer' => 0,
            'unanswered' => 0,
            'total_score' => 100,
            'status' => 'completed',
            'started_at' => now()->subMinutes(10),
            'finished_at' => now(),
            'answers' => [
                [
                    'question' => 'Apa itu fotosintesis?',
                    'user_answer' => 'A',
                    'correct_answer' => 'A',
                    'is_correct' => true,
                ],
            ],
        ]);

        $this->leaveRequest = LeaveRequest::create([
            'user_id' => $this->user->id,
            'reason' => 'Sakit demam',
            'status' => 'pending',
        ]);

        $this->notification = Notification::create([
            'user_id' => $this->user->id,
            'type' => 'general',
            'title' => 'Info Baru',
            'message' => 'Pesan uji coba',
            'is_read' => false,
        ]);

        $this->announcement = Announcement::create([
            'created_by' => $this->admin->id,
            'title' => 'Pengumuman Libur',
            'content' => 'Besok libur nasional.',
            'is_active' => true,
        ]);
    }

    // ==========================================
    // AUTHENTICATION TESTS
    // ==========================================

    public function test_api_login_successful(): void
    {
        $response = $this->postJson(route('api.auth.login'), [
            'email' => 'student@kpm.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);
    }

    public function test_api_login_invalid_password_returns_401(): void
    {
        $response = $this->postJson(route('api.auth.login'), [
            'email' => 'student@kpm.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau password salah.',
            ]);
    }

    public function test_api_login_inactive_user_returns_403(): void
    {
        $this->user->forceFill(['is_active' => false])->save();

        $response = $this->postJson(route('api.auth.login'), [
            'email' => 'student@kpm.test',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.',
            ]);
    }

    public function test_api_me_endpoint(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.auth.me'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'student@kpm.test',
                        'role' => 'user',
                    ],
                ],
            ]);
    }

    public function test_api_me_endpoint_without_token_returns_401(): void
    {
        $response = $this->getJson(route('api.auth.me'));

        $response->assertStatus(401);
        $response->assertJsonMissingPath('data.user');
    }

    public function test_api_me_endpoint_ignores_user_id_query_parameter(): void
    {
        // Celah IDOR: dulu endpoint ini menerima ?user_id= sehingga siapa pun bisa
        // membaca profil user lain. Sekarang ?user_id= harus diabaikan sepenuhnya.
        $token = $this->user->createToken('idor-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.auth.me') . '?user_id=' . $this->admin->id);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $this->user->id)
            ->assertJsonPath('data.user.email', 'student@kpm.test')
            ->assertJsonMissing(['email' => 'admin@kpm.test']);
    }

    public function test_api_me_endpoint_with_invalid_token_returns_401(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer token-palsu-ngawur')
            ->getJson(route('api.auth.me'));

        $response->assertStatus(401);
    }

    // ==========================================
    // ADMIN ENDPOINTS (ROLE: ADMIN)
    // ==========================================

    public function test_admin_can_access_all_admin_endpoints(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Dashboard
        $this->withHeaders($headers)->getJson(route('api.admin.dashboard'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['summary', 'recent_users', 'package_stats']]);

        // 2. Users
        $this->withHeaders($headers)->getJson(route('api.admin.users.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data', 'current_page']]);

        $this->withHeaders($headers)->getJson(route('api.admin.users.show', $this->user->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['user', 'recent_sessions']]);

        // 3. Packages
        $this->withHeaders($headers)->getJson(route('api.admin.packages.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.packages.show', $this->package->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['package', 'cards_count', 'questions_count']]);

        // 4. Practice Statistics
        $this->withHeaders($headers)->getJson(route('api.admin.practice-statistics.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.practice-statistics.show', $this->session->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'user', 'package']]);

        // 5. Login Logs
        $this->withHeaders($headers)->getJson(route('api.admin.login-logs.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        // 6. Leave Requests
        $this->withHeaders($headers)->getJson(route('api.admin.leave-requests.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['counts', 'leave_requests']]);

        $this->withHeaders($headers)->getJson(route('api.admin.leave-requests.show', $this->leaveRequest->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'reason']]);

        // 7. Notifications
        $this->withHeaders($headers)->getJson(route('api.admin.notifications.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['unread_count']]);

        // 8. Announcements
        $this->withHeaders($headers)->getJson(route('api.admin.announcements.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.announcements.show', $this->announcement->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'title', 'content']]);

        // 9. Profile
        $this->withHeaders($headers)->getJson(route('api.admin.profile'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'name', 'email', 'role']]);
    }

    public function test_unauthenticated_guest_is_rejected_from_admin_api_endpoints(): void
    {
        // Dulu endpoint admin terbuka untuk publik. Sekarang semua wajib 401.
        $endpoints = [
            'api.admin.dashboard',
            'api.admin.users.index',
            'api.admin.packages.index',
            'api.admin.practice-statistics.index',
            'api.admin.login-logs.index',
            'api.admin.leave-requests.index',
            'api.admin.notifications.index',
            'api.admin.announcements.index',
            'api.admin.profile',
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson(route($endpoint))
                ->assertStatus(401, "Endpoint [{$endpoint}] harus menolak request tanpa token.");
        }
    }

    public function test_non_admin_role_is_forbidden_from_admin_api_endpoints(): void
    {
        $token = $this->user->createToken('student-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.admin.dashboard'))
            ->assertStatus(403);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.admin.users.index'))
            ->assertStatus(403);
    }

    // ==========================================
    // USER / SISWA ENDPOINTS (ROLE: USER)
    // ==========================================

    public function test_student_can_access_all_user_endpoints(): void
    {
        $token = $this->user->createToken('student-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Dashboard
        $this->withHeaders($headers)->getJson(route('api.user.dashboard'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['user', 'statistics', 'gamification', 'recent_packages']]);

        // 2. Profile
        $this->withHeaders($headers)->getJson(route('api.user.profile'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'name', 'email', 'bidang', 'level']]);

        // 3. Packages
        $this->withHeaders($headers)->getJson(route('api.user.packages.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.user.packages.show', $this->package->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['package', 'cards']]);

        // 4. Practice History, Stats, Show
        $this->withHeaders($headers)->getJson(route('api.user.practice.history'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.user.practice.statistics'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['overview', 'by_package']]);

        $this->withHeaders($headers)->getJson(route('api.user.practice.show', $this->session->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['session', 'answers', 'settings']]);

        // 5. Leaderboard
        $this->withHeaders($headers)->getJson(route('api.user.leaderboard'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['leaderboard']]);

        // 6. Analytics
        $this->withHeaders($headers)->getJson(route('api.user.analytics'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['summary', 'score_over_time']]);

        // 7. Leave Requests
        $this->withHeaders($headers)->getJson(route('api.user.leave-requests.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        // 8. Notifications
        $this->withHeaders($headers)->getJson(route('api.user.notifications.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.user.notifications.unread-count'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['unread_count']]);

        // 9. Announcements
        $this->withHeaders($headers)->getJson(route('api.user.announcements.show', $this->announcement->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'title', 'content']]);
    }

    public function test_unauthenticated_guest_is_rejected_from_user_api_endpoints(): void
    {
        $endpoints = [
            'api.user.dashboard',
            'api.user.profile',
            'api.user.packages.index',
            'api.user.practice.history',
            'api.user.practice.statistics',
            'api.user.leaderboard',
            'api.user.analytics',
            'api.user.leave-requests.index',
            'api.user.notifications.index',
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson(route($endpoint))
                ->assertStatus(401, "Endpoint [{$endpoint}] harus menolak request tanpa token.");
        }
    }

    public function test_user_endpoints_ignore_user_id_query_parameter(): void
    {
        // Siswa tidak boleh membaca data siswa lain hanya dengan menggeser user_id.
        $token = $this->user->createToken('student-token')->plainTextToken;
        $other = User::factory()->create([
            'email' => 'other@kpm.test',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.user.profile') . '?user_id=' . $other->id);

        $response->assertOk()
            ->assertJsonPath('data.email', 'student@kpm.test')
            ->assertJsonMissing(['email' => 'other@kpm.test']);
    }

    public function test_practice_history_ignores_user_id_query_parameter(): void
    {
        $token = $this->user->createToken('student-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.user.practice.history') . '?user_id=' . $this->admin->id);

        $response->assertOk();

        // Hanya sesi milik user yang login yang boleh muncul.
        $returnedUserIds = collect($response->json('data.data'))
            ->pluck('user_id')
            ->unique()
            ->all();

        $this->assertNotContains($this->admin->id, $returnedUserIds);
        $this->assertSame([$this->user->id], $returnedUserIds);
    }

    public function test_admin_package_index_does_not_expose_question_answers(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.admin.packages.index'));

        $response->assertOk();

        $first = $response->json('data.data.0');

        // Daftar paket tidak boleh memuat array questions (isi kunci jawaban),
        // cukup jumlah soalnya saja.
        $this->assertArrayNotHasKey('questions', $first);
        $this->assertArrayNotHasKey('cards', $first);
        $this->assertArrayHasKey('questions_count', $first);
        $this->assertSame(1, $first['questions_count']);
        $this->assertStringNotContainsString('correct_answer', $response->getContent());
    }

    // ==========================================
    // WRITE OPERATIONS TESTS (ADMIN & USER)
    // ==========================================

    public function test_admin_can_perform_user_write_operations(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Create User
        $createRes = $this->withHeaders($headers)->postJson(route('api.admin.users.store'), [
            'name' => 'Siswa API Test',
            'email' => 'siswa.api.test@kpm.test',
            'password' => 'secret1234',
            'role' => 'user',
            'bidang' => 'math',
            'level' => 'A1',
            'student_class' => '7',
        ]);
        $createRes->assertStatus(201)
            ->assertJsonPath('data.email', 'siswa.api.test@kpm.test');

        $userId = $createRes->json('data.id');

        // 2. Update User
        $updateRes = $this->withHeaders($headers)->putJson(route('api.admin.users.update', $userId), [
            'name' => 'Siswa API Test Updated',
            'school_name' => 'SMP IT KPM',
        ]);
        $updateRes->assertOk()
            ->assertJsonPath('data.name', 'Siswa API Test Updated')
            ->assertJsonPath('data.school_name', 'SMP IT KPM');

        // 3. Toggle Active
        $toggleRes = $this->withHeaders($headers)->postJson(route('api.admin.users.toggle-active', $userId));
        $toggleRes->assertOk()
            ->assertJsonPath('data.is_active', false);

        // 4. Update Level
        $levelRes = $this->withHeaders($headers)->putJson(route('api.admin.users.update-level', $userId), [
            'level' => 'B2',
        ]);
        $levelRes->assertOk()
            ->assertJsonPath('data.level', 'B2');

        // 5. Delete User
        $deleteRes = $this->withHeaders($headers)->deleteJson(route('api.admin.users.destroy', $userId));
        $deleteRes->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_admin_can_perform_package_write_operations(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Create Package
        $createRes = $this->withHeaders($headers)->postJson(route('api.admin.packages.store'), [
            'title' => 'Paket API Test',
            'description' => 'Deskripsi paket API test',
            'bidang' => 'science',
            'level' => 'pemula',
            'kelas' => '8',
            'questions' => [
                [
                    'id' => 'q-1',
                    'question' => 'Berapa 10 + 10?',
                    'options' => ['A' => '20', 'B' => '15'],
                    'type' => 'pilihan_ganda',
                    'correct_answer' => 'A',
                ],
            ],
        ]);
        $createRes->assertStatus(201)
            ->assertJsonPath('data.title', 'Paket API Test');

        $packageId = $createRes->json('data.id');

        // 2. Update Package
        $updateRes = $this->withHeaders($headers)->putJson(route('api.admin.packages.update', $packageId), [
            'title' => 'Paket API Test Updated',
        ]);
        $updateRes->assertOk()
            ->assertJsonPath('data.title', 'Paket API Test Updated');

        // 3. Delete Package
        $deleteRes = $this->withHeaders($headers)->deleteJson(route('api.admin.packages.destroy', $packageId));
        $deleteRes->assertOk();
        $this->assertDatabaseMissing('packages', ['id' => $packageId]);
    }

    public function test_admin_can_update_leave_request_status(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson(route('api.admin.leave-requests.update-status', $this->leaveRequest->id), [
                'status' => 'approved',
                'admin_note' => 'Izin disetujui untuk pekan ini.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.admin_note', 'Izin disetujui untuk pekan ini.');

        $this->assertSame('approved', $this->leaveRequest->fresh()->status);
    }

    public function test_admin_can_send_notifications(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;

        // Kirim ke spesifik user
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.admin.notifications.send'), [
                'target' => 'user',
                'user_id' => $this->user->id,
                'title' => 'Pengingat PR Pekanan',
                'message' => 'Jangan lupa kerjakan PR pekan ini!',
                'type' => 'info',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Pengingat PR Pekanan');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Pengingat PR Pekanan',
        ]);
    }

    public function test_admin_can_manage_announcements(): void
    {
        $token = $this->admin->createToken('admin-test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Create Announcement
        $createRes = $this->withHeaders($headers)->postJson(route('api.admin.announcements.store'), [
            'title' => 'Pengumuman Ujian',
            'content' => 'Ujian akhir semester akan diadakan pekan depan.',
            'priority' => 'penting',
            'is_active' => true,
        ]);
        $createRes->assertStatus(201)
            ->assertJsonPath('data.title', 'Pengumuman Ujian');

        $announcementId = $createRes->json('data.id');

        // 2. Update Announcement
        $updateRes = $this->withHeaders($headers)->putJson(route('api.admin.announcements.update', $announcementId), [
            'title' => 'Pengumuman Ujian Semester Genap',
        ]);
        $updateRes->assertOk()
            ->assertJsonPath('data.title', 'Pengumuman Ujian Semester Genap');

        // 3. Delete Announcement
        $deleteRes = $this->withHeaders($headers)->deleteJson(route('api.admin.announcements.destroy', $announcementId));
        $deleteRes->assertOk();
        $this->assertDatabaseMissing('announcements', ['id' => $announcementId]);
    }

    public function test_user_can_submit_leave_request(): void
    {
        $token = $this->user->createToken('student-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.user.leave-requests.store'), [
                'reason' => 'Izin tidak dapat hadir karena sakit tipes selama 3 hari.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->user->id,
            'reason' => 'Izin tidak dapat hadir karena sakit tipes selama 3 hari.',
        ]);
    }

    public function test_user_can_start_and_submit_practice_session(): void
    {
        // Paket tanpa pembatasan kelas/level/bidang agar matchesStudent() selalu allowed
        $testPackage = Package::create([
            'title' => 'PR Latihan API Open',
            'description' => 'Latihan tanpa pembatasan',
            'kelas' => '',      // kosong = tidak dibatasi kelas
            'bidang' => '',     // kosong = tidak dibatasi bidang
            'level' => '',      // kosong = tidak dibatasi level
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'is_active' => true,
            'questions' => [
                [
                    'id' => 'q-1',
                    'question' => 'Berapa 5 + 5?',
                    'options' => ['A' => '10', 'B' => '12'],
                    'type' => 'pilihan_ganda',
                    'correct_answer' => 'A',
                ],
                [
                    'id' => 'q-2',
                    'question' => 'Berapa 10 * 2?',
                    'options' => ['A' => '15', 'B' => '20'],
                    'type' => 'pilihan_ganda',
                    'correct_answer' => 'B',
                ],
            ],
        ]);

        $token = $this->user->createToken('student-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        // 1. Start Practice Session
        $startRes = $this->withHeaders($headers)->postJson(route('api.user.practice.start', $testPackage->id));
        $startRes->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['session', 'package', 'questions']]);

        // Pastikan attempt payload tidak bocor correct_answer ke siswa
        $this->assertStringNotContainsString('correct_answer', $startRes->getContent());

        $sessionId = $startRes->json('data.session.id');

        // 2. Submit Answers
        $submitRes = $this->withHeaders($headers)->postJson(route('api.user.practice.submit', $sessionId), [
            'duration_seconds' => 300,
            'answers' => [
                '0' => 'A', // Benar
                '1' => 'B', // Benar
            ],
        ]);

        $submitRes->assertOk()
            ->assertJsonPath('data.session.correct_answer', 2)
            ->assertJsonPath('data.session.wrong_answer', 0);

        // Pastikan skor disimpan di DB
        $this->assertSame('completed', \App\Models\PracticeSession::find($sessionId)->status);
    }

    public function test_user_can_mark_notifications_as_read(): void
    {
        $token = $this->user->createToken('student-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $notif = Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Test Notif',
            'message' => 'Pesan notif test',
            'type' => 'info',
        ]);

        // Mark single read
        $res = $this->withHeaders($headers)->postJson(route('api.user.notifications.read', $notif->id));
        $res->assertOk();
        $this->assertNotNull($notif->fresh()->read_at);

        // Mark all read
        $resAll = $this->withHeaders($headers)->postJson(route('api.user.notifications.read-all'));
        $resAll->assertOk();
    }

    public function test_swagger_documentation_endpoint_is_accessible(): void
    {
        // Pastikan file Swagger UI dan OpenAPI (YAML & JSON) ada di filesystem
        $this->assertFileExists(public_path('api-docs/index.html'));
        $this->assertFileExists(public_path('api-docs/openapi.yaml'));
        $this->assertFileExists(public_path('api-docs/openapi.json'));

        // Verifikasi konten file HTML mengandung elemen Swagger UI
        $htmlContent = file_get_contents(public_path('api-docs/index.html'));
        $this->assertStringContainsString('KPM Smart API Documentation', $htmlContent);
        $this->assertStringContainsString('swagger-ui', $htmlContent);

        // Verifikasi konten YAML mengandung definisi API yang benar
        $yamlContent = file_get_contents(public_path('api-docs/openapi.yaml'));
        $this->assertStringContainsString('openapi: 3.0.3', $yamlContent);
        $this->assertStringContainsString('/api/v1/auth/login', $yamlContent);
        $this->assertStringContainsString('/api/admin/v1/', $yamlContent);
        $this->assertStringContainsString('/api/user/v1/', $yamlContent);

        // Test bahwa endpoint /swagger-ui dapat diakses (route di luar api/* prefix)
        $response = $this->get('/swagger-ui');
        $response->assertOk();
    }
}


