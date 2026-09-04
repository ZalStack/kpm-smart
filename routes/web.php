<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PracticeStatisticsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\GamificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect ke Login
Route::get('/', fn () => redirect()->route('login'));

// Halaman Informasi Publik (Fitur, Panduan, FAQ)
Route::get('/fitur', fn () => Inertia::render('Pages/Features'))->name('pages.features');
Route::get('/panduan', fn () => Inertia::render('Pages/Guide'))->name('pages.guide');
Route::get('/faq', fn () => Inertia::render('Pages/Faq'))->name('pages.faq');

// Support Routes (Public - tidak perlu login, dilindungi rate limiter anti spam)
Route::post('/support/submit', [SupportController::class, 'submitQuestion'])
    ->middleware('throttle:support')->name('support.submit');
Route::get('/support/tickets', [SupportController::class, 'getTickets'])
    ->middleware('throttle:support')->name('support.tickets');

// AI Chat Routes (Public)
Route::post('/chat/send', [ChatController::class, 'sendMessage'])
    ->middleware('throttle:support')->name('chat.send');
Route::get('/chat/history', [ChatController::class, 'getHistory'])
    ->middleware('throttle:support')->name('chat.history');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:auth-public')
        ->name('password.email');

    // Halaman konfirmasi "tautan reset telah dikirim ke email"
    Route::get('/forgot-password/sent', [AuthController::class, 'showResetLinkSent'])
        ->name('password.sent');

    // STEP 2: form password baru via token dari email
    Route::get('/forgot-password/reset/{token}', [AuthController::class, 'showResetPassword'])
        ->middleware('throttle:auth-public')
        ->name('password.reset');
    Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:auth-public')
        ->name('password.reset.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Users Management (Full CRUD)
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/import-excel', [AdminUserController::class, 'showImportExcel'])->name('users.import-excel');
        Route::post('/users/import-excel', [AdminUserController::class, 'importExcel'])->name('users.import-excel.process');
        Route::post('/users/reset-imported', [AdminUserController::class, 'resetImportedUsers'])->name('users.reset-imported');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');

        // Packages Management
        Route::get('/packages', [PackageController::class, 'adminIndex'])->name('packages.index');
        Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{package}', [PackageController::class, 'adminShow'])->name('packages.detail');

        // Edit routes - separated
        Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
        Route::get('/packages/{package}/edit/informasi', [PackageController::class, 'editInformasi'])->name('packages.edit.informasi');
        Route::get('/packages/{package}/edit/cards', [PackageController::class, 'editCards'])->name('packages.edit.cards');
        Route::get('/packages/{package}/edit/questions', [PackageController::class, 'editQuestions'])->name('packages.edit.questions');

        Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
        Route::get('/packages/{package}/confirm-delete', [PackageController::class, 'confirmDelete'])->name('packages.confirm-delete');
        Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

        // Card routes
        Route::post('/packages/{package}/cards', [PackageController::class, 'addCard'])->name('packages.add-card');
        Route::delete('/packages/{package}/cards/{cardId}', [PackageController::class, 'removeCard'])->name('packages.remove-card');

        // Question routes
        Route::get('/packages/{package}/questions/create', [PackageController::class, 'createQuestion'])->name('packages.create-question');
        Route::get('/packages/{package}/questions/{questionId}/edit', [PackageController::class, 'editQuestion'])->name('packages.edit-question');
        Route::post('/packages/{package}/questions', [PackageController::class, 'addQuestion'])->name('packages.add-question');
        Route::put('/packages/{package}/questions/{questionId}', [PackageController::class, 'updateQuestion'])->name('packages.update-question');
        Route::delete('/packages/{package}/questions/{questionId}', [PackageController::class, 'removeQuestion'])->name('packages.remove-question');

        // Import PDF
        Route::get('/packages/{package}/import-pdf', [PackageController::class, 'showImportForm'])->name('packages.show-import');
        Route::post('/packages/{package}/import-pdf', [PackageController::class, 'importPdf'])->name('packages.import-pdf');

        // AJAX Realtime Toggle & Schedule Update
        Route::post('/packages/{package}/ajax/toggle-setting', [PackageController::class, 'ajaxToggleSetting'])->name('packages.ajax.toggle-setting');
        Route::post('/packages/{package}/ajax/update-schedule', [PackageController::class, 'ajaxUpdateSchedule'])->name('packages.ajax.update-schedule');

        // Image Upload for Questions
        Route::post('/packages/{package}/upload-image', [PackageController::class, 'uploadImage'])->name('packages.upload-image');

        // Practice Statistics Management
        Route::prefix('practice-statistics')
            ->name('practice-statistics.')
            ->group(function () {
                Route::get('/', [PracticeStatisticsController::class, 'index'])->name('index');
                // Export routes HARUS didaftarkan SEBELUM /{session} agar tidak tertangkap sebagai parameter
                Route::get('/export/excel', [PracticeStatisticsController::class, 'exportExcel'])->name('export-excel');
                Route::get('/export/pdf', [PracticeStatisticsController::class, 'exportPdf'])->name('export-pdf');
                Route::get('/{session}', [PracticeStatisticsController::class, 'show'])->name('show');
            });

        // Support Management
        Route::prefix('support')
            ->name('support.')
            ->group(function () {
                Route::get('/', [SupportController::class, 'adminIndex'])->name('index');
                // Rute statis HARUS didaftarkan sebelum rute dinamis /{id}
                Route::post('/bulk-delete', [SupportController::class, 'adminBulkDelete'])->name('bulk-delete');
                Route::get('/export/csv', [SupportController::class, 'adminExport'])->name('export');
                Route::get('/{id}', [SupportController::class, 'adminShow'])->name('show');
                Route::post('/{id}/answer', [SupportController::class, 'adminAnswer'])->name('answer');
                Route::put('/{id}/status', [SupportController::class, 'adminUpdateStatus'])->name('update-status');
                Route::delete('/{id}', [SupportController::class, 'adminDelete'])->name('delete');
            });

        // Login Logs (Admin)
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');

        // Leave Requests (Admin)
        Route::prefix('leave-requests')
            ->name('leave-requests.')
            ->group(function () {
                Route::get('/', [LeaveRequestController::class, 'adminIndex'])->name('index');
                Route::get('/{id}', [LeaveRequestController::class, 'adminShow'])->name('show');
                Route::put('/{id}/status', [LeaveRequestController::class, 'adminUpdateStatus'])->name('update-status');
                Route::delete('/{id}', [LeaveRequestController::class, 'adminDestroy'])->name('delete');
            });

        // Notifications (Admin)
        Route::get('/notifications', [NotificationController::class, 'adminIndex'])->name('notifications.index');
        Route::post('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
        // Rute statis HARUS sebelum /{id}/read
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

        // Admin Profile
        Route::get('/profile', [AuthController::class, 'adminShowProfile'])->name('profile.edit');
        Route::put('/profile', [AuthController::class, 'adminUpdateProfile'])->name('profile.update');
        Route::get('/profile/change-password', [AuthController::class, 'adminShowChangePassword'])->name('profile.change-password');
        Route::put('/profile/change-password', [AuthController::class, 'adminChangePassword'])->name('profile.change-password.update');
    });

// User Routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.edit');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/change-password', [AuthController::class, 'showChangePassword'])->name('profile.change-password');
    Route::put('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change-password.update');

    // Packages
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

    // Leave Requests (User)
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');

    // Practice
    Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
    // Rute statis HARUS sebelum /{session} agar tidak tertangkap sebagai parameter
    Route::get('/practice/history', [PracticeController::class, 'history'])->name('practice.history');
    Route::get('/practice/statistics', [PracticeController::class, 'statistics'])->name('practice.statistics');
    Route::get('/practice/start/{package}', [PracticeController::class, 'startRedirect'])->name('practice.start.get');
    Route::post('/practice/start/{package}', [PracticeController::class, 'start'])->name('practice.start');
    Route::post('/practice/submit/{session}', [PracticeController::class, 'submit'])->name('practice.submit');
    Route::post('/practice/save-answers/{session}', [PracticeController::class, 'saveAnswers'])->name('practice.save-answers');
    Route::get('/practice/submit/{session}', function ($session) {
        return redirect()->route('practice.show', $session);
    });
    Route::get('/practice/{session}', [PracticeController::class, 'show'])->name('practice.show');

    // Gamification
    Route::get('/leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/analytics', [GamificationController::class, 'analytics'])->name('analytics');
    Route::get('/practice/{session}/certificate', [GamificationController::class, 'certificate'])->name('practice.certificate');

    // Notifications (User)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    // Rute statis HARUS sebelum /{id}/read
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
});
