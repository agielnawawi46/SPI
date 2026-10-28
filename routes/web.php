<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ============================================
// AUTH ROUTES (Guest Only)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',[LoginController::class, 'login'])->name('login.post');
});

// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/verify/{uuid}', [\App\Http\Controllers\AssuranceController::class, 'verify'])->name('document.verify');

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth', 'check.active', 'audit.trail'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Modul PKPT
    Route::prefix('pkpt')->name('pkpt.')->group(function () {
        // PKPT Utama
        Route::get('/', [\App\Http\Controllers\Admin\PkptController::class, 'index'])->name('index')->middleware('permission:pkpt.view');
        Route::get('/create', [\App\Http\Controllers\Admin\PkptController::class, 'create'])->name('create')->middleware('permission:pkpt.create');
        Route::post('/', [\App\Http\Controllers\Admin\PkptController::class, 'store'])->name('store')->middleware('permission:pkpt.create');
        Route::get('/kertas-kerja', [\App\Http\Controllers\Admin\PkptController::class, 'kertasKerjaIndex'])->name('kertas-kerja.index')->middleware('permission:pkpt.view');
        
        // Lainnya (Auditee & Jadwal)
        Route::resource('lainnya', \App\Http\Controllers\Admin\PkptLainnyaController::class);
        
        Route::get('/{pkpt}', [\App\Http\Controllers\Admin\PkptController::class, 'show'])->name('show')->middleware('permission:pkpt.view');
        Route::get('/{pkpt}/edit', [\App\Http\Controllers\Admin\PkptController::class, 'edit'])->name('edit')->middleware('permission:pkpt.edit');
        Route::put('/{pkpt}', [\App\Http\Controllers\Admin\PkptController::class, 'update'])->name('update')->middleware('permission:pkpt.edit');
        Route::get('/{pkpt}/view-file/{attachment?}', [\App\Http\Controllers\Admin\PkptController::class, 'viewPkptFile'])->name('view-file')->middleware('permission:pkpt.view');
        Route::get('/{pkpt}/download/{attachment?}', [\App\Http\Controllers\Admin\PkptController::class, 'downloadPkptFile'])->name('download')->middleware('permission:pkpt.view');
        Route::delete('/{pkpt}/attachments/{attachment}', [\App\Http\Controllers\Admin\PkptController::class, 'destroyAttachment'])->name('destroy-attachment')->middleware('permission:pkpt.edit');
        Route::delete('/{pkpt}', [\App\Http\Controllers\Admin\PkptController::class, 'destroy'])->name('destroy')->middleware('permission:pkpt.delete');
        
        // Kertas Kerja PKPT (6 File)
        Route::get('/{pkpt}/kertas-kerja', [\App\Http\Controllers\Admin\PkptController::class, 'kertasKerja'])->name('kertas-kerja')->middleware('permission:pkpt.view');
        Route::post('/{pkpt}/kertas-kerja', [\App\Http\Controllers\Admin\PkptController::class, 'storeKertasKerja'])->name('store-kertas-kerja')->middleware('permission:pkpt.edit');
        Route::delete('/{pkpt}/kertas-kerja-hapus', [\App\Http\Controllers\Admin\PkptController::class, 'destroyKertasKerjaDocument'])->name('destroy-kertas-kerja')->middleware('permission:pkpt.delete');
        Route::delete('/{pkpt}/kertas-kerja/{attachment}', [\App\Http\Controllers\Admin\PkptController::class, 'destroyKertasKerjaAttachment'])->name('destroy-kertas-kerja-attachment')->middleware('permission:pkpt.delete');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/search', [DashboardController::class, 'search'])->name('dashboard.search');

    // Notifications (JSON API)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/unread',           [NotificationController::class, 'unread'])->name('unread');
        Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all',        [NotificationController::class, 'markAllRead'])->name('read-all');
    });

    // ============================================
    // Approval Workflow
    // ============================================
    Route::post('/approvals/{type}/{id}/submit', [\App\Http\Controllers\ApprovalController::class, 'submit'])->name('approvals.submit');
    Route::post('/approvals/document/{id}/sign-auditee', [\App\Http\Controllers\ApprovalController::class, 'signAuditee'])->name('approvals.sign_auditee');
    Route::post('/approvals/{approval}/action', [\App\Http\Controllers\ApprovalController::class, 'action'])->name('approvals.action');
    Route::post('/approvals/document/{id}/approve-direct', [\App\Http\Controllers\ApprovalController::class, 'approveDirect'])->name('approvals.approve_direct');
    Route::delete('/documents/{document}/attachments/{attachment}', [\App\Http\Controllers\DocumentAttachmentController::class, 'destroy'])->name('documents.destroy-attachment');



    // ============================================
    // Layanan Assurance Module
    // ============================================
    Route::prefix('assurance')->name('assurance.')->middleware('permission:assurance.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\AssuranceController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AssuranceController::class, 'create'])->name('create')->middleware('permission:assurance.create');
        Route::post('/', [\App\Http\Controllers\AssuranceController::class, 'store'])->name('store')->middleware('permission:assurance.create');
        Route::get('/{document}', [\App\Http\Controllers\AssuranceController::class, 'show'])->name('show');
        Route::get('/{document}/pdf', [\App\Http\Controllers\AssuranceController::class, 'exportPdf'])->name('pdf');
        Route::get('/{document}/edit', [\App\Http\Controllers\AssuranceController::class, 'edit'])->name('edit')->middleware('permission:assurance.edit');
        Route::put('/{document}', [\App\Http\Controllers\AssuranceController::class, 'update'])->name('update')->middleware('permission:assurance.edit');
        Route::delete('/{document}', [\App\Http\Controllers\AssuranceController::class, 'destroy'])->name('destroy')->middleware('permission:assurance.delete');
    });

    // ============================================
    // Advisory Module
    // ============================================
    Route::prefix('advisory')->name('advisory.')->middleware('permission:advisory.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdvisoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AdvisoryController::class, 'create'])->name('create')->middleware('permission:advisory.create');
        Route::post('/', [\App\Http\Controllers\AdvisoryController::class, 'store'])->name('store')->middleware('permission:advisory.create');
        Route::get('/{document}', [\App\Http\Controllers\AdvisoryController::class, 'show'])->name('show');
        Route::get('/{document}/pdf', [\App\Http\Controllers\AdvisoryController::class, 'exportPdf'])->name('pdf');
        Route::get('/{document}/edit', [\App\Http\Controllers\AdvisoryController::class, 'edit'])->name('edit')->middleware('permission:advisory.edit');
        Route::put('/{document}', [\App\Http\Controllers\AdvisoryController::class, 'update'])->name('update')->middleware('permission:advisory.edit');
        Route::delete('/{document}', [\App\Http\Controllers\AdvisoryController::class, 'destroy'])->name('destroy')->middleware('permission:advisory.edit');
    });

    // ============================================
    // Kegiatan Lainnya
    // ============================================
    Route::prefix('kegiatan-lainnya')->name('other-activities.')->group(function () {
        // Monitoring Tindak Lanjut (for SPI team)
        Route::get('/tindak-lanjut', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutIndex'])->name('tl.index')->middleware('permission:other_activity.view');
        Route::get('/tindak-lanjut/create', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutCreate'])->name('tl.create')->middleware('permission:other_activity.edit');
        Route::post('/tindak-lanjut', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutStore'])->name('tl.store')->middleware('permission:other_activity.edit');
        Route::get('/tindak-lanjut/{document}', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutShow'])->name('tl.show')->middleware('permission:other_activity.view');
        Route::get('/tindak-lanjut/{document}/edit', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutEdit'])->name('tl.edit')->middleware('permission:other_activity.edit');
        Route::put('/tindak-lanjut/{document}', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutUpdate'])->name('tl.update')->middleware('permission:other_activity.edit');
        Route::delete('/tindak-lanjut/{document}', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutDestroy'])->name('tl.destroy')->middleware('permission:other_activity.edit');
        Route::delete('/tindak-lanjut/follow-up/{followUp}', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutDestroyFollowUp'])->name('tl.follow-up.destroy')->middleware('permission:other_activity.edit');
        Route::get('/tindak-lanjut/{document}/pdf', [\App\Http\Controllers\OtherActivityController::class, 'tindakLanjutPdf'])->name('tl.pdf')->middleware('permission:follow_up.view');
        // Pendamping Eksternal
        Route::get('/pendamping', [\App\Http\Controllers\OtherActivityController::class, 'pendampingIndex'])->name('pendamping.index')->middleware('permission:assurance.view');
        Route::get('/pendamping/create', [\App\Http\Controllers\OtherActivityController::class, 'pendampingCreate'])->name('pendamping.create')->middleware('permission:assurance.create');
        Route::post('/pendamping', [\App\Http\Controllers\OtherActivityController::class, 'pendampingStore'])->name('pendamping.store')->middleware('permission:assurance.create');
        Route::get('/pendamping/{document}/edit', [\App\Http\Controllers\OtherActivityController::class, 'pendampingEdit'])->name('pendamping.edit')->middleware('permission:assurance.edit');
        Route::put('/pendamping/{document}', [\App\Http\Controllers\OtherActivityController::class, 'pendampingUpdate'])->name('pendamping.update')->middleware('permission:assurance.edit');
        Route::delete('/pendamping/{document}', [\App\Http\Controllers\OtherActivityController::class, 'pendampingDestroy'])->name('pendamping.destroy')->middleware('permission:assurance.edit');
        // Quality Assurance
        Route::get('/qa', [\App\Http\Controllers\OtherActivityController::class, 'qaIndex'])->name('qa.index')->middleware('permission:assurance.view');
        Route::get('/qa/create', [\App\Http\Controllers\OtherActivityController::class, 'qaCreate'])->name('qa.create')->middleware('permission:assurance.create');
        Route::post('/qa', [\App\Http\Controllers\OtherActivityController::class, 'qaStore'])->name('qa.store')->middleware('permission:assurance.create');
        Route::get('/qa/{document}', [\App\Http\Controllers\OtherActivityController::class, 'qaShow'])->name('qa.show')->middleware('permission:assurance.view');
        Route::get('/qa/{document}/pdf', [\App\Http\Controllers\OtherActivityController::class, 'qaExportPdf'])->name('qa.pdf')->middleware('permission:assurance.view');
        Route::get('/qa/{document}/edit', [\App\Http\Controllers\OtherActivityController::class, 'qaEdit'])->name('qa.edit')->middleware('permission:assurance.edit');
        Route::put('/qa/{document}', [\App\Http\Controllers\OtherActivityController::class, 'qaUpdate'])->name('qa.update')->middleware('permission:assurance.edit');
        Route::delete('/qa/{document}', [\App\Http\Controllers\OtherActivityController::class, 'qaDestroy'])->name('qa.destroy')->middleware('permission:assurance.delete');
    });

    // ============================================
    // Auditee / Tindak Lanjut (Bidang Auditee)
    // ============================================
    Route::prefix('tindak-lanjut')->name('auditee.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AuditeeController::class, 'index'])->name('index')->middleware('permission:follow_up.view');
        Route::get('/{followUp}', [\App\Http\Controllers\AuditeeController::class, 'show'])->name('show')->middleware('permission:follow_up.view');
        Route::put('/{followUp}/update', [\App\Http\Controllers\AuditeeController::class, 'update'])->name('update')->middleware('permission:follow_up.view');
        Route::post('/{followUp}/upload-evidence', [\App\Http\Controllers\AuditeeController::class, 'uploadEvidence'])->name('upload-evidence');
        Route::post('/{followUp}/submit', [\App\Http\Controllers\AuditeeController::class, 'submitEvidence'])->name('submit-evidence');
        Route::delete('/evidence/{evidence}', [\App\Http\Controllers\AuditeeController::class, 'deleteEvidence'])->name('delete-evidence');
        Route::post('/{followUp}/approve', [\App\Http\Controllers\AuditeeController::class, 'approveEvidence'])->name('approve-evidence');
        Route::post('/{followUp}/reject', [\App\Http\Controllers\AuditeeController::class, 'rejectEvidence'])->name('reject-evidence');
    });

    // ============================================
    // Laporan Kinerja
    // ============================================
    Route::prefix('laporan-kinerja')->name('performance-reports.')->middleware('permission:report.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\PerformanceReportController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\PerformanceReportController::class, 'create'])->name('create')->middleware('permission:report.create');
        Route::post('/', [\App\Http\Controllers\PerformanceReportController::class, 'store'])->name('store')->middleware('permission:report.create');
        Route::get('/{document}', [\App\Http\Controllers\PerformanceReportController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [\App\Http\Controllers\PerformanceReportController::class, 'edit'])->name('edit')->middleware('permission:report.edit');
        Route::put('/{document}', [\App\Http\Controllers\PerformanceReportController::class, 'update'])->name('update')->middleware('permission:report.edit');
    });

    // ============================================
    // Admin Module
    // ============================================
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // User Management
        Route::get('/users',                    [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create',             [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users',                   [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit',        [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',             [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',          [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/import/template',    [\App\Http\Controllers\Admin\UserController::class, 'downloadTemplate'])->name('users.import.template');
        Route::get('/users/import',             [\App\Http\Controllers\Admin\UserController::class, 'importForm'])->name('users.import');
        Route::post('/users/import',            [\App\Http\Controllers\Admin\UserController::class, 'import'])->name('users.import.post');

        // Audit Trail
        Route::get('/audit-trail',              [\App\Http\Controllers\Admin\AuditTrailController::class, 'index'])->name('audit-trail');
    });

});
