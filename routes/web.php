<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\EnrollKeyController;
use App\Http\Controllers\PracticeStatisticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoPaymentController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Welcome Page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Halaman Informasi Publik (Fitur, Panduan, FAQ)
Route::get('/fitur', fn () => view('pages.features'))->name('pages.features');
Route::get('/panduan', fn () => view('pages.guide'))->name('pages.guide');
Route::get('/faq', fn () => view('pages.faq'))->name('pages.faq');

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

Route::get('/api/testimonials', [TestimonialController::class, 'getPublicTestimonials'])
    ->middleware('throttle:public-api')->name('api.testimonials');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:auth-public');

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

// Payment Finish Routes (di luar group auth, tapi wajib login di controller;
// auth middleware sebagai lapisan pertama agar tamu langsung diarahkan ke login)
Route::get('/payment/finish', [OrderController::class, 'paymentFinish'])
    ->middleware('auth')->name('payment.finish');
Route::get('/payment/error', [OrderController::class, 'paymentError'])
    ->middleware('auth')->name('payment.error');
Route::get('/orders/status', [OrderController::class, 'paymentStatus'])
    ->middleware('auth')->name('orders.status');

// Admin Routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Users Management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
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

        // Transactions Management
        Route::prefix('transactions')
            ->name('transactions.')
            ->group(function () {
                Route::get('/', [TransactionController::class, 'index'])->name('index');
                Route::get('/export/excel', [TransactionController::class, 'exportExcel'])->name('export-excel');
                Route::get('/export/pdf', [TransactionController::class, 'exportPdf'])->name('export-pdf');
                Route::get('/stats', [TransactionController::class, 'stats'])->name('stats');
                Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
            });

        // Enroll Keys Management
        Route::prefix('enroll-keys')
            ->name('enroll-keys.')
            ->group(function () {
                Route::get('/', [EnrollKeyController::class, 'index'])->name('index');
                Route::get('/{enrollKey}', [EnrollKeyController::class, 'show'])->name('show');
                Route::post('/{enrollKey}/activate', [EnrollKeyController::class, 'activate'])->name('activate');
                Route::post('/{enrollKey}/send', [EnrollKeyController::class, 'send'])->name('send');
                Route::post('/bulk-send', [EnrollKeyController::class, 'bulkSend'])->name('bulk-send');
            });

        // Reports Management
        Route::prefix('reports')
            ->name('reports.')
            ->group(function () {
                Route::get('/', [ReportController::class, 'index'])->name('index');
                Route::get('/show', [ReportController::class, 'show'])->name('show');
                Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export-excel');
                Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
            });

        // Practice Statistics Management
        Route::prefix('practice-statistics')
            ->name('practice-statistics.')
            ->group(function () {
                Route::get('/', [PracticeStatisticsController::class, 'index'])->name('index');
                Route::get('/{session}', [PracticeStatisticsController::class, 'show'])->name('show');
                Route::get('/export/excel', [PracticeStatisticsController::class, 'exportExcel'])->name('export-excel');
                Route::get('/export/pdf', [PracticeStatisticsController::class, 'exportPdf'])->name('export-pdf');
            });

        // Orders Management
        Route::get('/orders', [OrderController::class, 'adminIndex'])->name('orders.index');
        Route::post('/orders/{order}/verify', [OrderController::class, 'verify'])->name('orders.verify');
        Route::post('/orders/{order}/activate-enroll', [OrderController::class, 'activateEnrollByAdmin'])->name('orders.activate-enroll');
        Route::post('/orders/{order}/send-enroll', [OrderController::class, 'sendEnrollKey'])->name('orders.send-enroll');
        Route::get('/orders/{order}', [OrderController::class, 'adminShow'])->name('orders.show');

        Route::prefix('testimonials')
            ->name('testimonials.')
            ->group(function () {
                Route::get('/', [TestimonialController::class, 'adminIndex'])->name('index');
                Route::post('/{id}/approve', [TestimonialController::class, 'approve'])->name('approve');
                Route::post('/{id}/toggle-active', [TestimonialController::class, 'toggleActive'])->name('toggle-active');
                Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('delete');
                Route::post('/bulk-delete', [TestimonialController::class, 'bulkDelete'])->name('bulk-delete');
                Route::get('/export', [TestimonialController::class, 'export'])->name('export');
            });

        // Support Management
        Route::prefix('support')
            ->name('support.')
            ->group(function () {
                Route::get('/', [SupportController::class, 'adminIndex'])->name('index');
                Route::get('/{id}', [SupportController::class, 'adminShow'])->name('show');
                Route::post('/{id}/answer', [SupportController::class, 'adminAnswer'])->name('answer');
                Route::put('/{id}/status', [SupportController::class, 'adminUpdateStatus'])->name('update-status');
                Route::delete('/{id}', [SupportController::class, 'adminDelete'])->name('delete');
                Route::post('/bulk-delete', [SupportController::class, 'adminBulkDelete'])->name('bulk-delete');
                Route::get('/export/csv', [SupportController::class, 'adminExport'])->name('export');
            });

        // Video Management (Admin)
        Route::prefix('videos')
            ->name('videos.')
            ->group(function () {
                Route::get('/', [VideoController::class, 'index'])->name('index');
                Route::get('/create', [VideoController::class, 'create'])->name('create');
                Route::post('/', [VideoController::class, 'store'])->name('store');
                Route::get('/{video}/edit', [VideoController::class, 'edit'])->name('edit');
                Route::put('/{video}', [VideoController::class, 'update'])->name('update');
                Route::delete('/{video}', [VideoController::class, 'destroy'])->name('destroy');
                Route::post('/{video}/toggle', [VideoController::class, 'toggleActive'])->name('toggle');
            });

        // Video Orders (Admin)
        Route::prefix('video-orders')
            ->name('video-orders.')
            ->group(function () {
                Route::get('/', [VideoController::class, 'ordersIndex'])->name('index');
                Route::post('/{videoOrder}/grant', [VideoController::class, 'grantAccess'])->name('grant');
            });

        // Login Logs (Admin)
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');

        // Notifications (Admin)
        Route::get('/notifications', [NotificationController::class, 'adminIndex'])->name('notifications.index');
        Route::post('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    });

// User Routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');

    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.edit');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Packages
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/create/{package}', [OrderController::class, 'create'])->name('orders.create');
    Route::get('/orders/{order}/process-payment', [OrderController::class, 'processPayment'])->name('orders.process-payment');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->middleware('throttle:payments')->name('orders.pay');
    // Aktivasi Enroll Key hanya lewat POST (dengan rate limit) agar mutasi
    // state tidak bisa dieksekusi lewat GET yang lolos dari pembatasan.
    Route::post('/orders/{order}/activate', [OrderController::class, 'activate'])
        ->middleware('throttle:enroll-key')->name('orders.activate');
    Route::post('/orders/{order}/verify-enroll', [OrderController::class, 'verifyEnrollKey'])
        ->middleware('throttle:enroll-key')->name('orders.verify-enroll');

    // Practice
    Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
    Route::get('/practice/history', [PracticeController::class, 'history'])->name('practice.history');
    Route::get('/practice/statistics', [PracticeController::class, 'statistics'])->name('practice.statistics');
    Route::post('/practice/start/{package}', [PracticeController::class, 'start'])->name('practice.start');
    Route::post('/practice/submit/{session}', [PracticeController::class, 'submit'])->name('practice.submit');
    Route::get('/practice/{session}', [PracticeController::class, 'show'])->name('practice.show');

    Route::prefix('testimonials')
        ->name('testimonials.')
        ->group(function () {
            Route::post('/store', [TestimonialController::class, 'store'])->name('store');
            Route::get('/my-testimonial', [TestimonialController::class, 'getUserTestimonial'])->name('my');
        });

    // Video Purchase Routes
    Route::get('/videos', [VideoPaymentController::class, 'index'])->name('videos.index');
    Route::get('/videos/{video}', [VideoPaymentController::class, 'show'])->name('videos.show');
    Route::post('/videos/{video}/order', [VideoPaymentController::class, 'createOrder'])->name('videos.order');
    Route::get('/videos/{video}/pay/{videoOrder}', [VideoPaymentController::class, 'processPayment'])->name('videos.pay');
    Route::post('/videos/{video}/pay/{videoOrder}', [VideoPaymentController::class, 'pay'])
        ->middleware('throttle:payments')->name('videos.pay-process');

    // Notifications (User)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});

// Video Payment Finish
Route::get('/video-payment/finish', [VideoPaymentController::class, 'paymentFinish'])->middleware('auth')->name('videos.payment-finish');

// Notifikasi Midtrans (dikirim server-to-server tanpa session/CSRF token;
// keaslian payload diverifikasi di controller via signature SHA512 + Server Key)
Route::post('/payment/notification', [OrderController::class, 'notification'])
    ->middleware('throttle:60,1')->name('payment.notification');
Route::post('/video-payment/notification', [VideoPaymentController::class, 'notification'])
    ->middleware('throttle:60,1')->name('videos.payment-notification');
