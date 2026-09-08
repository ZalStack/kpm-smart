<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\SupportTicket;
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
    protected SupportTicket $supportTicket;
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

        $this->supportTicket = SupportTicket::create([
            'name' => 'Budi',
            'email' => 'budi@test.com',
            'question' => 'Bagaimana cara mengerjakan soal?',
            'status' => 'pending',
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

    public function test_api_me_endpoint_without_token_returns_200(): void
    {
        $response = $this->getJson(route('api.auth.me'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                ],
            ]);
    }

    public function test_api_me_endpoint_with_user_id_query(): void
    {
        $response = $this->getJson(route('api.auth.me', ['user_id' => $this->user->id]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $this->user->id,
                        'email' => $this->user->email,
                    ],
                ],
            ]);
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

        // 5. Support
        $this->withHeaders($headers)->getJson(route('api.admin.support.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.support.show', $this->supportTicket->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'question']]);

        // 6. Login Logs
        $this->withHeaders($headers)->getJson(route('api.admin.login-logs.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        // 7. Leave Requests
        $this->withHeaders($headers)->getJson(route('api.admin.leave-requests.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['counts', 'leave_requests']]);

        $this->withHeaders($headers)->getJson(route('api.admin.leave-requests.show', $this->leaveRequest->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'reason']]);

        // 8. Notifications
        $this->withHeaders($headers)->getJson(route('api.admin.notifications.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['unread_count']]);

        // 9. Announcements
        $this->withHeaders($headers)->getJson(route('api.admin.announcements.index'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);

        $this->withHeaders($headers)->getJson(route('api.admin.announcements.show', $this->announcement->id))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'title', 'content']]);

        // 10. Profile
        $this->withHeaders($headers)->getJson(route('api.admin.profile'))
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['id', 'name', 'email', 'role']]);
    }

    public function test_unauthenticated_guest_can_access_admin_api_endpoints(): void
    {
        $response = $this->getJson(route('api.admin.dashboard'));

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['summary', 'recent_users', 'package_stats']]);
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

    public function test_unauthenticated_guest_can_access_user_api_endpoints(): void
    {
        $response = $this->getJson(route('api.user.dashboard'));

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['user', 'statistics', 'gamification', 'recent_packages']]);
    }
}
