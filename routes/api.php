<?php

use App\Http\Controllers\Api\Admin\AnnouncementApiController as AdminAnnouncementController;
use App\Http\Controllers\Api\Admin\DashboardApiController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\LeaveRequestApiController as AdminLeaveRequestController;
use App\Http\Controllers\Api\Admin\LoginLogApiController as AdminLoginLogController;
use App\Http\Controllers\Api\Admin\NotificationApiController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\PackageApiController as AdminPackageController;
use App\Http\Controllers\Api\Admin\PracticeStatisticsApiController as AdminPracticeStatsController;
use App\Http\Controllers\Api\Admin\ProfileApiController as AdminProfileController;
use App\Http\Controllers\Api\Admin\UserApiController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SatelliteApiController;
use App\Http\Controllers\Api\User\AnnouncementApiController as UserAnnouncementController;
use App\Http\Controllers\Api\User\DashboardApiController as UserDashboardController;
use App\Http\Controllers\Api\User\GamificationApiController as UserGamificationController;
use App\Http\Controllers\Api\User\LeaveRequestApiController as UserLeaveRequestController;
use App\Http\Controllers\Api\User\NotificationApiController as UserNotificationController;
use App\Http\Controllers\Api\User\PackageApiController as UserPackageController;
use App\Http\Controllers\Api\User\PracticeApiController as UserPracticeController;
use App\Http\Controllers\Api\User\ProfileApiController as UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Prefix bawaan dari Laravel: /api/...
|
| Keamanan: SELURUH endpoint di bawah wajib memakai Sanctum token, kecuali
| endpoint login. Endpoint admin butuh role admin. Parameter query `?user_id=`
| hanya dipercaya untuk role admin.
|
*/

// ==========================================
// SATELLITE INTEGRATION ROUTES (API Key auth, no login required)
// ==========================================
Route::prefix('satellite')->name('api.satellite.')->middleware('api.key')->group(function () {
    // Get practice statistics for satellite sync
    Route::get('/practice-statistics', [SatelliteApiController::class, 'practiceStatistics'])
        ->name('practice-statistics');

    // Get active packages list for weekly PR count detection
    Route::get('/packages', [SatelliteApiController::class, 'packages'])
        ->name('packages');

    // Get users list for satellite sync
    Route::get('/users', [SatelliteApiController::class, 'users'])
        ->name('users');

    // Sync weekly report from satellite
    Route::post('/weekly-report/sync', [SatelliteApiController::class, 'syncWeeklyReport'])
        ->name('weekly-report.sync');

    // Realtime User sync from Web Induk
    Route::post('/users/sync', [SatelliteApiController::class, 'syncUser'])
        ->name('users.sync');
    Route::post('/users/sync-batch', [SatelliteApiController::class, 'syncBatch'])
        ->name('users.sync-batch');
    Route::post('/users/delete', [SatelliteApiController::class, 'deleteUser'])
        ->name('users.delete');
    Route::post('/reset-progress', [SatelliteApiController::class, 'resetProgress'])
        ->name('reset-progress');
    Route::post('/users/clean-orphans', [SatelliteApiController::class, 'cleanOrphans'])
        ->name('users.clean-orphans');
    Route::post('/setup-november-packages', [SatelliteApiController::class, 'setupNovemberPackages'])
        ->name('setup-november-packages');
    Route::post('/submit-sample-practice', [SatelliteApiController::class, 'submitSamplePractice'])
        ->name('submit-sample-practice');
});

// ==========================================
// AUTHENTICATION ROUTES (/api/v1/auth/...)
// ==========================================
Route::prefix('v1/auth')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:api-login')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// ==========================================
// ADMIN API ROUTES (/api/admin/v1/...)
// Wajib login sebagai admin.
// ==========================================
Route::prefix('admin/v1')->name('api.admin.')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 2. Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::put('/users/{user}/level', [AdminUserController::class, 'updateLevel'])->name('users.update-level');

    // 3. Packages Management (PR Pekanan)
    Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages.index');
    Route::post('/packages', [AdminPackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}', [AdminPackageController::class, 'show'])->name('packages.show');
    Route::put('/packages/{package}', [AdminPackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [AdminPackageController::class, 'destroy'])->name('packages.destroy');

    // 4. Practice Statistics (Statistik Siswa)
    Route::get('/practice-statistics', [AdminPracticeStatsController::class, 'index'])->name('practice-statistics.index');
    Route::get('/practice-statistics/{session}', [AdminPracticeStatsController::class, 'show'])->name('practice-statistics.show');

    // 5. Login Logs
    Route::get('/login-logs', [AdminLoginLogController::class, 'index'])->name('login-logs.index');

    // 6. Leave Requests (Pengajuan Izin)
    Route::get('/leave-requests', [AdminLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/{id}', [AdminLeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::put('/leave-requests/{id}/status', [AdminLeaveRequestController::class, 'updateStatus'])->name('leave-requests.update-status');

    // 7. Notifications (?user_id= honoured untuk admin)
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [AdminNotificationController::class, 'send'])->name('notifications.send');
    Route::get('/notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // 8. Announcements (Pengumuman)
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}', [AdminAnnouncementController::class, 'show'])->name('announcements.show');
    Route::put('/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // 9. Admin Profile (?user_id= honoured untuk admin)
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
});

// ==========================================
// USER / SISWA API ROUTES (/api/user/v1/...)
// Wajib login. Data yang dikembalikan selalu milik user yang sedang login.
// `?user_id=` hanya dipercaya bila role-nya admin (lihat BaseApiController).
// ==========================================
Route::prefix('user/v1')->name('api.user.')->middleware('auth:sanctum')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // 2. Profile
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');

    // 3. Packages (Tugas PR)
    Route::get('/packages', [UserPackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [UserPackageController::class, 'show'])->name('packages.show');

    // 4. Practice (Riwayat, Mulai, Kerjakan & Statistik)
    Route::get('/practice/history', [UserPracticeController::class, 'history'])->name('practice.history');
    Route::get('/practice/statistics', [UserPracticeController::class, 'statistics'])->name('practice.statistics');
    Route::post('/practice/start/{package}', [UserPracticeController::class, 'start'])->name('practice.start');
    Route::post('/practice/submit/{session}', [UserPracticeController::class, 'submit'])->name('practice.submit');
    Route::get('/practice/{session}', [UserPracticeController::class, 'show'])->name('practice.show');

    // 5. Leaderboard
    Route::get('/leaderboard', [UserGamificationController::class, 'leaderboard'])->name('leaderboard');

    // 6. Analytics
    Route::get('/analytics', [UserGamificationController::class, 'analytics'])->name('analytics');

    // 7. Leave Requests (Pengajuan Izin)
    Route::get('/leave-requests', [UserLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests', [UserLeaveRequestController::class, 'store'])->name('leave-requests.store');

    // 8. Notifications
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [UserNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // 9. Announcements (Pengumuman)
    Route::get('/announcements', [UserAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [UserAnnouncementController::class, 'show'])->name('announcements.show');
});
