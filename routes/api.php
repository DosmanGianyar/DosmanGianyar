<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConductController;
use App\Http\Controllers\Api\EarlyCheckoutController;
use App\Http\Controllers\Api\ExtracurricularController;
use App\Http\Controllers\Api\ForgotAttendanceController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\HomeroomConsultationController;
use App\Http\Controllers\Api\KesiswaanController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermitController;
use App\Http\Controllers\Api\SchoolRegulationController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\GuruBkApiController;
use App\Http\Controllers\Api\GuruConductApiController;
use App\Http\Controllers\Api\GuruGradeApiController;
use App\Http\Controllers\Api\GuruJournalController;
use App\Http\Controllers\Api\GuruSarprasApiController;
use App\Http\Controllers\Api\GuruTeachingSessionController;
use App\Http\Controllers\Api\TeacherAttendanceController;
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
        Route::post('/auth/profile/photo',      [AuthController::class, 'updatePhoto']);
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
            Route::get('/announcements',        [AnnouncementController::class, 'index']);
            Route::get('/announcements/all',    [AnnouncementController::class, 'all']);
            Route::get('/announcements/{id}',   [AnnouncementController::class, 'show']);

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

            // Nilai / Rapor
            Route::get('/grades',         [GradeController::class, 'index']);
            Route::get('/grades/summary', [GradeController::class, 'summary']);

            // Absensi Guru Mengajar (untuk siswa)
            Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'index']);

            // Guru
            Route::get('/guru/dashboard',                                 [GuruController::class, 'dashboard']);
            Route::get('/guru/classes',                                   [GuruController::class, 'classes']);
            Route::get('/guru/attendance/daily',                          [GuruController::class, 'attendanceDaily']);
            Route::get('/guru/attendance/rekap',                          [GuruController::class, 'attendanceRekap']);
            Route::get('/guru/permits',                                   [GuruController::class, 'permits']);
            Route::post('/guru/permits/{permit}/approve',                 [GuruController::class, 'approvePermit']);
            Route::post('/guru/permits/{permit}/reject',                  [GuruController::class, 'rejectPermit']);
            Route::get('/guru/forgot-attendance',                         [GuruController::class, 'forgotAttendance']);
            Route::post('/guru/forgot-attendance/{forgotAttendance}/approve', [GuruController::class, 'approveForgotAttendance']);
            Route::post('/guru/forgot-attendance/{forgotAttendance}/reject',  [GuruController::class, 'rejectForgotAttendance']);
            Route::get('/guru/early-checkouts',                           [GuruController::class, 'earlyCheckouts']);
            Route::post('/guru/early-checkouts/{earlyCheckout}/approve',  [GuruController::class, 'approveEarlyCheckout']);
            Route::post('/guru/early-checkouts/{earlyCheckout}/reject',   [GuruController::class, 'rejectEarlyCheckout']);
            Route::get('/guru/conduct',                                   [GuruController::class, 'conduct']);

            // Guru Conduct API (catat pelanggaran & prestasi)
            Route::get('/guru/conduct-categories',                        [GuruConductApiController::class, 'categories']);
            Route::get('/guru/conduct-classes',                           [GuruConductApiController::class, 'classes']);
            Route::get('/guru/conduct-students',                          [GuruConductApiController::class, 'students']);
            Route::post('/guru/conduct-logs',                             [GuruConductApiController::class, 'store']);
            Route::get('/guru/conduct-history',                           [GuruConductApiController::class, 'history']);

            // Guru Teaching Session (absensi mengajar per sesi)
            Route::get('/guru/teaching-classes',                          [GuruTeachingSessionController::class, 'classes']);
            Route::get('/guru/teaching-sessions',                         [GuruTeachingSessionController::class, 'index']);
            Route::post('/guru/teaching-sessions',                        [GuruTeachingSessionController::class, 'store']);
            Route::get('/guru/teaching-sessions/export',                  [GuruTeachingSessionController::class, 'export']);
            Route::get('/guru/teaching-sessions/class-students/{classId}',[GuruTeachingSessionController::class, 'classStudents']);
            Route::get('/guru/teaching-sessions/{id}',                    [GuruTeachingSessionController::class, 'show']);

            // Jurnal Guru
            Route::get('/guru/journals',                                  [GuruJournalController::class, 'index']);
            Route::post('/guru/journals',                                 [GuruJournalController::class, 'store']);
            Route::get('/guru/journals/class-students/{classId}',         [GuruJournalController::class, 'classStudents']);
            Route::get('/guru/journals/{id}',                             [GuruJournalController::class, 'show']);
            Route::put('/guru/journals/{id}',                             [GuruJournalController::class, 'update']);
            Route::delete('/guru/journals/{id}',                          [GuruJournalController::class, 'destroy']);

            // Input Nilai Guru
            Route::get('/guru/grades/classes',                    [GuruGradeApiController::class, 'classes']);
            Route::get('/guru/grades/subjects',                   [GuruGradeApiController::class, 'subjects']);
            Route::get('/guru/grades/export',                     [GuruGradeApiController::class, 'export']);
            Route::get('/guru/grades',                            [GuruGradeApiController::class, 'index']);
            Route::post('/guru/grades',                           [GuruGradeApiController::class, 'store']);
            Route::delete('/guru/grades/{id}',                    [GuruGradeApiController::class, 'destroy']);

            // BK (Bimbingan Konseling) Guru
            Route::get('/guru/bk/classes',                        [GuruBkApiController::class, 'classes']);
            Route::get('/guru/bk/students',                       [GuruBkApiController::class, 'students']);
            Route::get('/guru/bk',                                [GuruBkApiController::class, 'index']);
            Route::post('/guru/bk',                               [GuruBkApiController::class, 'store']);

            // Sarpras Guru
            Route::get('/guru/sarpras/stats',                     [GuruSarprasApiController::class, 'stats']);
            Route::get('/guru/sarpras/categories',                [GuruSarprasApiController::class, 'categories']);
            Route::get('/guru/sarpras/assets',                    [GuruSarprasApiController::class, 'assets']);
            Route::get('/guru/sarpras/damage',                    [GuruSarprasApiController::class, 'damage']);
            Route::post('/guru/sarpras/damage',                   [GuruSarprasApiController::class, 'storeDamage']);
            Route::get('/guru/sarpras/loans',                     [GuruSarprasApiController::class, 'loans']);
            Route::post('/guru/sarpras/loans',                    [GuruSarprasApiController::class, 'storeLoan']);
            Route::patch('/guru/sarpras/loans/{id}/return',       [GuruSarprasApiController::class, 'returnLoan']);

            // Kesiswaan Summary
            Route::get('/kesiswaan/summary', [KesiswaanController::class, 'summary']);

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
