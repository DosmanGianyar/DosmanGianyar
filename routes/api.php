<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ConductController;
use App\Http\Controllers\Api\EarlyCheckoutController;
use App\Http\Controllers\Api\ExtracurricularController;
use App\Http\Controllers\Api\ForgotAttendanceController;
use App\Http\Controllers\Api\HomeroomConsultationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermitController;
use App\Http\Controllers\Api\SchoolRegulationController;
use App\Http\Controllers\Api\ShiftController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SIMS Flutter App
|--------------------------------------------------------------------------
| Prefix otomatis: /api/v1/...
| Auth: Laravel Sanctum (Bearer token)
| Device validation: X-Device-ID header (middleware device.lock)
*/

Route::prefix('v1')->group(function () {

    // ── Public ────────────────────────────────────────────────────────────
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ── Protected ─────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout',             [AuthController::class, 'logout']);
        Route::get('/auth/me',                  [AuthController::class, 'me']);
        Route::put('/auth/profile',             [AuthController::class, 'updateProfile']);
        Route::put('/auth/change-password',     [AuthController::class, 'changePassword']);

        // Semua route di bawah ini wajib menyertakan X-Device-ID yang cocok
        Route::middleware('device.lock')->group(function () {

            // Shift & lokasi aktif (geofence data + server time)
            Route::get('/shifts/active', [ShiftController::class, 'active']);

            // Presensi
            Route::get('/attendance/status',   [AttendanceController::class, 'status']);
            Route::post('/attendance/checkin',  [AttendanceController::class, 'checkIn']);
            Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut']);
            Route::get('/attendance/history',   [AttendanceController::class, 'history']);

            // Notifikasi
            Route::get('/notifications',              [NotificationController::class, 'index']);
            Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead']);
            Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead']);

            // Pengumuman
            Route::get('/announcements', [AnnouncementController::class, 'index']);

            // Tata Tertib Sekolah
            Route::get('/school-regulations', [SchoolRegulationController::class, 'index']);

            // Ekstrakurikuler
            Route::get('/extracurriculars',         [ExtracurricularController::class, 'index']);
            Route::get('/extracurriculars/my',      [ExtracurricularController::class, 'myExtras']);
            Route::post('/extracurriculars/{extracurricular}/join',  [ExtracurricularController::class, 'join']);
            Route::post('/extracurriculars/{extracurricular}/leave', [ExtracurricularController::class, 'leave']);

            // Izin / Sakit / Dispensasi
            Route::get('/permits',        [PermitController::class, 'index']);
            Route::post('/permits',       [PermitController::class, 'store']);
            Route::delete('/permits/{id}',[PermitController::class, 'destroy']);

            // Lupa Absen
            Route::get('/forgot-attendance',         [ForgotAttendanceController::class, 'index']);
            Route::post('/forgot-attendance',        [ForgotAttendanceController::class, 'store']);
            Route::delete('/forgot-attendance/{id}', [ForgotAttendanceController::class, 'destroy']);

            // Izin Pulang Lebih Awal
            Route::get('/early-checkout',         [EarlyCheckoutController::class, 'index']);
            Route::post('/early-checkout',        [EarlyCheckoutController::class, 'store']);
            Route::delete('/early-checkout/{id}', [EarlyCheckoutController::class, 'destroy']);

            // Pelanggaran & Poin
            Route::get('/conduct', [ConductController::class, 'index']);

            // Prestasi
            Route::get('/achievement-categories', [AchievementController::class, 'categories']);
            Route::get('/achievements',            [AchievementController::class, 'index']);
            Route::post('/achievements',           [AchievementController::class, 'store']);

            // Bimbingan Wali Kelas
            Route::get('/homeroom-consultations',             [HomeroomConsultationController::class, 'index']);
            Route::post('/homeroom-consultations',            [HomeroomConsultationController::class, 'store']);
            Route::patch('/homeroom-consultations/{id}/cancel', [HomeroomConsultationController::class, 'cancel']);

            // Sesi Ekstrakurikuler
            Route::get('/extracurricular-sessions',                          [ExtracurricularController::class, 'sessions']);
            Route::post('/extracurricular-sessions',                         [ExtracurricularController::class, 'createSession']);
            Route::get('/extracurricular-sessions/{session}',                [ExtracurricularController::class, 'sessionDetail']);
            Route::post('/extracurricular-sessions/{session}/toggle-open',   [ExtracurricularController::class, 'toggleOpen']);
            Route::post('/extracurricular-sessions/{session}/mark',          [ExtracurricularController::class, 'markAttendance']);
        });
    });
});
