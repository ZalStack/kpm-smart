<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\DashboardApiController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\UserApiController as AdminUserController;
use App\Http\Controllers\Api\Admin\PackageApiController as AdminPackageController;
use App\Http\Controllers\Api\Admin\PracticeStatisticsApiController as AdminPracticeStatsController;
use App\Http\Controllers\Api\Admin\SupportApiController as AdminSupportController;
use App\Http\Controllers\Api\Admin\LoginLogApiController as AdminLoginLogController;
use App\Http\Controllers\Api\Admin\LeaveRequestApiController as AdminLeaveRequestController;
use App\Http\Controllers\Api\Admin\NotificationApiController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\AnnouncementApiController as AdminAnnouncementController;
use App\Http\Controllers\Api\Admin\ProfileApiController as AdminProfileController;

use App\Http\Controllers\Api\User\DashboardApiController as UserDashboardController;
use App\Http\Controllers\Api\User\ProfileApiController as UserProfileController;
use App\Http\Controllers\Api\User\PackageApiController as UserPackageController;
use App\Http\Controllers\Api\User\PracticeApiController as UserPracticeController;
use App\Http\Controllers\Api\User\GamificationApiController as UserGamificationController;
use App\Http\Controllers\Api\User\LeaveRequestApiController as UserLeaveRequestController;
use App\Http\Controllers\Api\User\NotificationApiController as UserNotificationController;
use App\Http\Controllers\Api\User\AnnouncementApiController as UserAnnouncementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Prefix bawaan dari Laravel: /api/...
|
| GET endpoints: TIDAK membutuhkan autentikasi (public)
| POST/auth:    Membutuhkan autentikasi Sanctum token
*/

// ==========================================
// AUTHENTICATION ROUTES (/api/v1/auth/...)
// ==========================================
Route::prefix('v1/auth')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// ==========================================
// ADMIN API ROUTES (/api/admin/v1/...)
// GET: PUBLIC (no auth required)
// ==========================================
Route::prefix('admin/v1')->name('api.admin.')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 2. Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');

    // 3. Packages Management (Soal Tugas)
    Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [AdminPackageController::class, 'show'])->name('packages.show');

    // 4. Practice Statistics (Statistik Siswa)
    Route::get('/practice-statistics', [AdminPracticeStatsController::class, 'index'])->name('practice-statistics.index');
    Route::get('/practice-statistics/{session}', [AdminPracticeStatsController::class, 'show'])->name('practice-statistics.show');

    // 5. Support Tickets
    Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
    Route::get('/support/{id}', [AdminSupportController::class, 'show'])->name('support.show');

    // 6. Login Logs
    Route::get('/login-logs', [AdminLoginLogController::class, 'index'])->name('login-logs.index');

    // 7. Leave Requests (Pengajuan Izin)
    Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/{id}', [AdminLeaveRequestController::class, 'show'])->name('leave-requests.show');

    // 8. Notifications (gunakan ?user_id=xxx untuk filter)
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // 9. Announcements (Pengumuman)
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [AdminAnnouncementController::class, 'show'])->name('announcements.show');

    // 10. Admin Profile (gunakan ?user_id=xxx untuk filter)
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
});

// ==========================================
// USER / SISWA API ROUTES (/api/user/v1/...)
// GET: PUBLIC (no auth required)
// Gunakan ?user_id=xxx untuk data spesifik user
// ==========================================
Route::prefix('user/v1')->name('api.user.')->group(function () {
    // 1. Dashboard (gunakan ?user_id=xxx)
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // 2. Profile (gunakan ?user_id=xxx)
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');

    // 3. Packages (Tugas PR) — gunakan ?user_id=xxx untuk melihat progress
    Route::get('/packages', [UserPackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [UserPackageController::class, 'show'])->name('packages.show');

    // 4. Practice (Riwayat & Statistik) — gunakan ?user_id=xxx
    Route::get('/practice/history', [UserPracticeController::class, 'history'])->name('practice.history');
    Route::get('/practice/statistics', [UserPracticeController::class, 'statistics'])->name('practice.statistics');
    Route::get('/practice/{session}', [UserPracticeController::class, 'show'])->name('practice.show');

    // 5. Leaderboard — gunakan ?user_id=xxx untuk highlight posisi user
    Route::get('/leaderboard', [UserGamificationController::class, 'leaderboard'])->name('leaderboard');

    // 6. Analytics — gunakan ?user_id=xxx
    Route::get('/analytics', [UserGamificationController::class, 'analytics'])->name('analytics');

    // 7. Leave Requests (Pengajuan Izin) — gunakan ?user_id=xxx
    Route::get('/leave-requests', [UserLeaveRequestController::class, 'index'])->name('leave-requests.index');

    // 8. Notifications — gunakan ?user_id=xxx
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [UserNotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // 9. Announcements (Pengumuman)
    Route::get('/announcements', [UserAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [UserAnnouncementController::class, 'show'])->name('announcements.show');
});
