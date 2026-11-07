<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AiKnowledgeController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SystemConfigurationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VillageController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset via WhatsApp OTP Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtp'])->name('password.otp.send');
Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify');
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'resendOtp'])->name('password.otp.resend');
Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

use App\Http\Controllers\Api\TelegramConnectionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SpatialAnalyticsController;

// Public Root -> Portal
Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/portal/apbdes', [PublicController::class, 'apbdes'])->name('public.apbdes');
Route::get('/portal/statistik', [PublicController::class, 'statistik'])->name('public.statistik');
Route::get('/portal/pembangunan', [PublicController::class, 'pembangunan'])->name('public.pembangunan');

// Dashboard Routes (Protected)
Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Residents Management
    Route::middleware(['role:SUPER_ADMIN,ADMIN_DESA,RW,RT'])->group(function () {
        Route::get('/dashboard/residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::get('/dashboard/residents/create', [ResidentController::class, 'create'])->name('residents.create');
        Route::post('/dashboard/residents', [ResidentController::class, 'store'])->name('residents.store');
        Route::get('/dashboard/residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
        Route::get('/dashboard/residents/{resident}/edit', [ResidentController::class, 'edit'])->name('residents.edit');
        Route::put('/dashboard/residents/{resident}', [ResidentController::class, 'update'])->name('residents.update');
    });

    // Document Requests
    Route::get('/dashboard/documents', [DocumentRequestController::class, 'index'])->name('documents.index');
    Route::get('/dashboard/documents/create', [DocumentRequestController::class, 'create'])->name('documents.create');
    Route::post('/dashboard/documents', [DocumentRequestController::class, 'store'])->name('documents.store');
    Route::get('/dashboard/documents/{id}', [DocumentRequestController::class, 'show'])->name('documents.show');
    Route::patch('/dashboard/documents/{id}/status', [DocumentRequestController::class, 'updateStatus'])->name('documents.update');
    Route::get('/dashboard/documents/{id}/print', [DocumentRequestController::class, 'print'])->name('documents.print');

    // AI Chat
    Route::get('/dashboard/ai', [AiController::class, 'index'])->name('ai.index');
    Route::post('/dashboard/ai/chat', [AiController::class, 'chat'])->name('ai.chat');

    // Super Admin Only
    Route::middleware(['role:SUPER_ADMIN'])->group(function () {
        Route::resource('/dashboard/villages', VillageController::class)->except(['show']);
    });

    // Super Admin, Admin Desa, RW, RT
    Route::middleware(['role:SUPER_ADMIN,ADMIN_DESA,RW,RT'])->group(function () {
        Route::get('/dashboard/areas', [AreaController::class, 'index'])->name('areas.index');
        Route::patch('/dashboard/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
        Route::resource('/dashboard/users', UserController::class)->except(['show']);
        Route::get('/dashboard/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/dashboard/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Super Admin, Admin Desa
    Route::middleware(['role:SUPER_ADMIN,ADMIN_DESA'])->group(function () {
        Route::post('/dashboard/areas/rw', [AreaController::class, 'storeRw'])->name('areas.rw.store');
        Route::delete('/dashboard/areas/rw/{rw}', [AreaController::class, 'destroyRw'])->name('areas.rw.destroy');
        Route::post('/dashboard/areas/rt', [AreaController::class, 'storeRt'])->name('areas.rt.store');
        Route::delete('/dashboard/areas/rt/{rt}', [AreaController::class, 'destroyRt'])->name('areas.rt.destroy');

        Route::get('/dashboard/spatial-analytics', [SpatialAnalyticsController::class, 'index'])->name('spatial-analytics.index');

        Route::resource('/dashboard/budgets', BudgetController::class)->except(['show']);
        Route::resource('/dashboard/developments', DevelopmentController::class)->except(['show']);
    });

    // Settings (All Authenticated Users)
    Route::get('/dashboard/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/dashboard/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/dashboard/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password.update');

    // Telegram Connection (Web route to use same auth session)
    Route::post('/dashboard/settings/telegram/generate-token', [TelegramConnectionController::class, 'generateToken']);
    Route::post('/dashboard/settings/telegram/disconnect', [SettingController::class, 'disconnectTelegram'])->name('settings.telegram.disconnect');

    // WhatsApp Connection
    Route::post('/dashboard/settings/whatsapp/connect', [SettingController::class, 'connectWhatsApp'])->name('settings.whatsapp.connect');
    Route::post('/dashboard/settings/whatsapp/disconnect', [SettingController::class, 'disconnectWhatsApp'])->name('settings.whatsapp.disconnect');

    // Family
    Route::get('/dashboard/my-family', [FamilyController::class, 'myFamily'])->name('families.my-family');

    // Complaints (Semua bisa akses, controller menangani logika role)
    Route::get('/dashboard/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/dashboard/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/dashboard/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/dashboard/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::patch('/dashboard/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('complaints.update-status');

    // Emergencies
    Route::post('/dashboard/emergencies', [EmergencyController::class, 'store'])->name('emergencies.store');
    Route::patch('/dashboard/emergencies/{emergency}/resolve', [EmergencyController::class, 'resolve'])->name('emergencies.resolve');

    // Notifications
    Route::get('/dashboard/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/dashboard/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/dashboard/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Polls / Musyawarah
    Route::get('/dashboard/polls', [PollController::class, 'index'])->name('polls.index');
    Route::get('/dashboard/polls/create', [PollController::class, 'create'])->name('polls.create');
    Route::post('/dashboard/polls', [PollController::class, 'store'])->name('polls.store');
    Route::get('/dashboard/polls/{poll}', [PollController::class, 'show'])->name('polls.show');
    Route::post('/dashboard/polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');
    Route::patch('/dashboard/polls/{poll}/close', [PollController::class, 'close'])->name('polls.close');

    // Announcements
    Route::get('/dashboard/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::middleware(['role:SUPER_ADMIN,ADMIN_DESA'])->group(function () {
        Route::get('/dashboard/announcements/manage', [AnnouncementController::class, 'manage'])->name('announcements.manage');
        Route::get('/dashboard/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/dashboard/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/dashboard/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/dashboard/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/dashboard/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });
    Route::get('/dashboard/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

    // Pusat Konfigurasi & AI Knowledge (Super Admin)
    Route::middleware(['role:SUPER_ADMIN'])->group(function () {
        Route::get('/dashboard/configuration', [SystemConfigurationController::class, 'index'])->name('configuration.index');
        Route::post('/dashboard/configuration', [SystemConfigurationController::class, 'update'])->name('configuration.update');
        Route::post('/dashboard/configuration/test-ai', [SystemConfigurationController::class, 'testAi'])->name('configuration.test-ai');
        Route::post('/dashboard/configuration/test-telegram', [SystemConfigurationController::class, 'testTelegram'])->name('configuration.test-telegram');
        Route::post('/dashboard/configuration/test-whatsapp', [SystemConfigurationController::class, 'testWhatsapp'])->name('configuration.test-whatsapp');
        Route::post('/dashboard/configuration/test-email', [SystemConfigurationController::class, 'testEmail'])->name('configuration.test-email');

        Route::post('/dashboard/ai-knowledge/config', [AiKnowledgeController::class, 'updateConfig'])->name('ai-knowledge.config');
        Route::post('/dashboard/ai-knowledge/test-connection', [AiKnowledgeController::class, 'testConnection'])->name('ai-knowledge.test');
        Route::resource('/dashboard/ai-knowledge', AiKnowledgeController::class)->except(['show']);
    });

});
