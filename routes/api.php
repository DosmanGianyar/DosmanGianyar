<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ExtracurricularController;
use App\Http\Controllers\Api\NotificationController;
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

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',     [AuthController::class, 'me']);

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

            // Sesi Ekstrakurikuler
            Route::get('/extracurricular-sessions',                          [ExtracurricularController::class, 'sessions']);
            Route::post('/extracurricular-sessions',                         [ExtracurricularController::class, 'createSession']);
            Route::get('/extracurricular-sessions/{session}',                [ExtracurricularController::class, 'sessionDetail']);
            Route::post('/extracurricular-sessions/{session}/toggle-open',   [ExtracurricularController::class, 'toggleOpen']);
            Route::post('/extracurricular-sessions/{session}/mark',          [ExtracurricularController::class, 'markAttendance']);
        });
    });
});
