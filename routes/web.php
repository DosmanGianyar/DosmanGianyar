<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendance;
use App\Http\Controllers\Guru\ConductController as GuruConduct;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Guru\DispensationController as GuruDispensation;
use App\Http\Controllers\Guru\ProfileController as GuruProfile;
use App\Http\Controllers\Guru\SarprasController as GuruSarpras;
use App\Http\Controllers\Siswa\AttendanceController as SiswaAttendance;
use App\Http\Controllers\Siswa\ConductController as SiswaConduct;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\Siswa\ExitPassController;
use App\Http\Controllers\Siswa\PermitController;
use App\Http\Controllers\Siswa\ProfileController as SiswaProfile;
use App\Http\Controllers\Siswa\SarprasController as SiswaSarpras;
use App\Http\Controllers\Admin\AttendanceReportController as AdminAttendanceReport;
use App\Http\Controllers\Admin\ScanEventController;
use App\Http\Controllers\Admin\StudentCardController;
use App\Http\Controllers\Admin\UserImportController;
use App\Http\Controllers\Guru\ExportController as GuruExport;
use App\Http\Controllers\Guru\TeacherAttendanceController as GuruTeacherAttendance;
use App\Http\Controllers\Siswa\TeacherAttendanceController as SiswaTeacherAttendance;
use App\Http\Controllers\Guru\BkController as GuruBk;
use App\Http\Controllers\Guru\GradeController as GuruGrade;
use App\Http\Controllers\Siswa\AnnouncementController as SiswaAnnouncement;
use App\Http\Controllers\Siswa\VotingController as SiswaVoting;
use App\Http\Controllers\Siswa\VotingManageController as SiswaVotingManage;
use App\Http\Controllers\Siswa\AchievementController as SiswaAchievement;
use App\Http\Controllers\Siswa\AchievementVerifyController as SiswaAchievementVerify;
use App\Http\Controllers\Siswa\KesiswaanController as SiswaKesiswaan;
use App\Http\Controllers\Siswa\KurikulumController as SiswaKurikulum;
use App\Http\Controllers\Siswa\HumasController as SiswaHumas;
use App\Http\Controllers\Siswa\PrasaranaController as SiswaPrasarana;
use App\Http\Controllers\Siswa\NotificationController as SiswaNotification;
use App\Http\Controllers\Siswa\HomeroomConsultationController as SiswaHomeroomConsultation;
use App\Http\Controllers\Guru\HomeroomConsultationController as GuruHomeroomConsultation;
use App\Http\Controllers\Siswa\ForgotAttendanceController as SiswaForgotAttendance;
use App\Http\Controllers\Guru\ForgotAttendanceController as GuruForgotAttendance;
use App\Http\Controllers\Siswa\EarlyCheckoutRequestController as SiswaEarlyCheckout;
use App\Http\Controllers\Guru\EarlyCheckoutRequestController as GuruEarlyCheckout;
use App\Http\Controllers\PublicBiodataController;
use Illuminate\Support\Facades\Route;

// ─── Halaman Utama ────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ─── Biodata Publik (akses tanpa login, untuk scan QR) ───────────────────────
Route::get('/biodata/{identifier}', [PublicBiodataController::class, 'show'])->name('public.biodata');

// ─── PWA Offline Fallback ─────────────────────────────────────────────────────
Route::get('/offline', fn() => response(view('offline'))->header('Cache-Control', 'public, max-age=86400'))->name('offline');

// ─── Admin (non-Filament) ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/import-users', [UserImportController::class, 'showForm'])->name('users.import.form');
    Route::post('/import-users', [UserImportController::class, 'import'])->name('users.import');
    Route::get('/import-users/template', [UserImportController::class, 'downloadTemplate'])->name('users.import.template');

    // Laporan Presensi
    Route::get('/attendance-report/excel', [AdminAttendanceReport::class, 'downloadExcel'])->name('attendance-report.excel');
    Route::get('/attendance-report/pdf',   [AdminAttendanceReport::class, 'downloadPdf'])->name('attendance-report.pdf');

    // Download Kartu Pelajar
    Route::get('/student-card/{user}/download', [StudentCardController::class, 'download'])->name('student-card.download');

    // Rekap Ekstrakurikuler
    Route::get('/extracurricular/session/{session}/pdf', [\App\Http\Controllers\Admin\ExtracurricularExportController::class, 'pdf'])
        ->name('extracurricular.session.pdf');
});

// ─── Absensi QR Kegiatan (admin + admin_kesiswaan) ────────────────────────────
Route::middleware(['auth', 'role:admin,admin_kesiswaan'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/scan-events/{scanEvent}/scanner', [ScanEventController::class, 'scanner'])->name('scan-events.scanner');
    Route::post('/scan-events/{scanEvent}/scan', [ScanEventController::class, 'scan'])->name('scan-events.scan');
    Route::get('/scan-events/{scanEvent}/list', [ScanEventController::class, 'list'])->name('scan-events.list');
    Route::delete('/scan-events/{scanEvent}/attendances/{attendance}', [ScanEventController::class, 'destroy'])->name('scan-events.attendances.destroy');
});

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit')->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Guru ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:guru,admin'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');
    Route::get('/profile', [GuruProfile::class, 'show'])->name('profile');
    Route::put('/profile', [GuruProfile::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [GuruProfile::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [GuruProfile::class, 'updatePhoto'])->name('profile.photo');

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [GuruAttendance::class, 'index'])->name('index');
        Route::get('/rekap', [GuruAttendance::class, 'rekap'])->name('rekap');
        Route::post('/manual', [GuruAttendance::class, 'manual'])->name('manual');
        Route::get('/permits', [GuruAttendance::class, 'permits'])->name('permits');
        Route::patch('/permits/{permit}/approve', [GuruAttendance::class, 'approvePermit'])->name('permits.approve');
        Route::patch('/permits/{permit}/reject', [GuruAttendance::class, 'rejectPermit'])->name('permits.reject');
        Route::get('/dispensation/create', [GuruDispensation::class, 'create'])->name('dispensation.create');
        Route::post('/dispensation', [GuruDispensation::class, 'store'])->name('dispensation.store');
    });

    // Kesiswaan
    Route::prefix('conduct')->name('conduct.')->group(function () {
        Route::get('/', [GuruConduct::class, 'index'])->name('index');
        Route::get('/pilih', [GuruConduct::class, 'choose'])->name('choose');
        Route::get('/create', [GuruConduct::class, 'create'])->name('create');
        Route::post('/', [GuruConduct::class, 'store'])->name('store');
        Route::get('/student/{student}', [GuruConduct::class, 'studentDetail'])->name('student');
    });

    // Absensi Mengajar Guru
    Route::prefix('teacher-attendance')->name('teacher-attendance.')->group(function () {
        Route::get('/', [GuruTeacherAttendance::class, 'index'])->name('index');
        Route::post('/', [GuruTeacherAttendance::class, 'store'])->name('store');
        Route::post('/manual', [GuruTeacherAttendance::class, 'storeManual'])->name('manual');
        Route::delete('/{teacherAttendance}', [GuruTeacherAttendance::class, 'destroy'])->name('destroy');
        Route::get('/api/students', [GuruTeacherAttendance::class, 'studentsByClass'])->name('api.students');
    });

    // Input Nilai (non-Filament)
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [GuruGrade::class, 'index'])->name('index');
        Route::post('/', [GuruGrade::class, 'store'])->name('store');
        Route::delete('/{grade}', [GuruGrade::class, 'destroy'])->name('destroy');
    });

    // BK (Bimbingan Konseling)
    Route::prefix('bk')->name('bk.')->group(function () {
        Route::get('/', [GuruBk::class, 'index'])->name('index');
        Route::post('/log', [GuruBk::class, 'storeLog'])->name('log.store');
    });

    // Sarpras
    Route::prefix('sarpras')->name('sarpras.')->group(function () {
        Route::get('/', [GuruSarpras::class, 'index'])->name('index');
        Route::get('/damage', [GuruSarpras::class, 'damage'])->name('damage');
        Route::patch('/damage/{report}/progress', [GuruSarpras::class, 'progressDamage'])->name('damage.progress');
        Route::patch('/damage/{report}/resolve', [GuruSarpras::class, 'resolveDamage'])->name('damage.resolve');
        Route::get('/loans', [GuruSarpras::class, 'loans'])->name('loans');
        Route::patch('/loans/{loan}/approve', [GuruSarpras::class, 'approveLoan'])->name('loans.approve');
        Route::patch('/loans/{loan}/reject', [GuruSarpras::class, 'rejectLoan'])->name('loans.reject');
        Route::patch('/loans/{loan}/return', [GuruSarpras::class, 'returnLoan'])->name('loans.return');
        Route::post('/assets/{asset}/maintenance', [GuruSarpras::class, 'storeMaintenance'])->name('maintenance.store');
    });

    // Izin Pulang Lebih Awal (review oleh guru/piket/admin)
    Route::prefix('early-checkout')->name('early-checkout.')->group(function () {
        Route::get('/', [GuruEarlyCheckout::class, 'index'])->name('index');
        Route::patch('/{earlyCheckout}/approve', [GuruEarlyCheckout::class, 'approve'])->name('approve');
        Route::patch('/{earlyCheckout}/reject', [GuruEarlyCheckout::class, 'reject'])->name('reject');
    });

    // Lupa Absen (review oleh wali kelas)
    Route::prefix('forgot-attendance')->name('forgot-attendance.')->group(function () {
        Route::get('/', [GuruForgotAttendance::class, 'index'])->name('index');
        Route::patch('/{forgotAttendance}/approve', [GuruForgotAttendance::class, 'approve'])->name('approve');
        Route::patch('/{forgotAttendance}/reject', [GuruForgotAttendance::class, 'reject'])->name('reject');
    });

    // Jurnal Bimbingan Wali Kelas
    Route::prefix('homeroom-consultation')->name('homeroom-consultation.')->group(function () {
        Route::get('/', [GuruHomeroomConsultation::class, 'index'])->name('index');
        Route::patch('/{consultation}/schedule', [GuruHomeroomConsultation::class, 'schedule'])->name('schedule');
        Route::patch('/{consultation}/complete', [GuruHomeroomConsultation::class, 'complete'])->name('complete');
        Route::patch('/{consultation}/cancel', [GuruHomeroomConsultation::class, 'cancel'])->name('cancel');
        Route::get('/export/pdf', [GuruHomeroomConsultation::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export/excel', [GuruHomeroomConsultation::class, 'exportExcel'])->name('export-excel');
    });

    // Export Laporan
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/attendance', [GuruExport::class, 'attendanceForm'])->name('attendance.form');
        Route::get('/attendance/excel', [GuruExport::class, 'attendanceExcel'])->name('attendance.excel');
        Route::get('/attendance/pdf', [GuruExport::class, 'attendancePdf'])->name('attendance.pdf');
        Route::get('/attendance/grid-pdf', [GuruExport::class, 'attendanceGridPdf'])->name('attendance.grid-pdf');
        Route::get('/attendance/grid-excel', [GuruExport::class, 'attendanceGridExcel'])->name('attendance.grid-excel');
        Route::get('/conduct', [GuruExport::class, 'conductForm'])->name('conduct.form');
        Route::get('/conduct/excel', [GuruExport::class, 'conductExcel'])->name('conduct.excel');
        Route::get('/conduct/pdf', [GuruExport::class, 'conductPdf'])->name('conduct.pdf');
        Route::get('/sarpras', [GuruExport::class, 'sarprasForm'])->name('sarpras.form');
        Route::get('/sarpras/assets/pdf', [GuruExport::class, 'assetsPdf'])->name('sarpras.assets.pdf');
        Route::get('/sarpras/damage/pdf', [GuruExport::class, 'damagePdf'])->name('sarpras.damage.pdf');
        Route::get('/teacher-attendance/pdf',   [GuruExport::class, 'teacherAttendancePdf'])->name('teacher-attendance.pdf');
        Route::get('/teacher-attendance/excel', [GuruExport::class, 'teacherAttendanceExcel'])->name('teacher-attendance.excel');
        Route::get('/grades', [GuruExport::class, 'gradesForm'])->name('grades.form');
        Route::get('/grades/pdf', [GuruExport::class, 'gradesPdf'])->name('grades.pdf');
        Route::get('/grades/excel', [GuruExport::class, 'gradesExcel'])->name('grades.excel');
    });
});

// ─── Siswa ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:siswa,pengelola'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboard::class, 'index'])->name('dashboard');
    Route::get('/kesiswaan', [SiswaKesiswaan::class, 'index'])->name('kesiswaan');
    Route::get('/kurikulum', [SiswaKurikulum::class, 'index'])->name('kurikulum');
    Route::get('/kurikulum/rapor', [SiswaKurikulum::class, 'rapor'])->name('kurikulum.rapor');
    Route::get('/kurikulum/absensi-guru', [SiswaTeacherAttendance::class, 'index'])->name('teacher-attendance.index');

    // Bimbingan Wali Kelas
    Route::prefix('bimbingan-wali')->name('homeroom-consultation.')->group(function () {
        Route::get('/', [SiswaHomeroomConsultation::class, 'index'])->name('index');
        Route::post('/', [SiswaHomeroomConsultation::class, 'store'])->name('store');
        Route::patch('/{consultation}/cancel', [SiswaHomeroomConsultation::class, 'cancel'])->name('cancel');
    });
    Route::get('/humas', [SiswaHumas::class, 'index'])->name('humas');
    Route::get('/humas/events/{event}', [SiswaHumas::class, 'eventShow'])->name('humas.event.show');
    Route::get('/humas/gallery', [SiswaHumas::class, 'galleryIndex'])->name('humas.gallery.index');
    Route::get('/humas/gallery/{gallery}', [SiswaHumas::class, 'galleryShow'])->name('humas.gallery.show');
    Route::get('/prasarana', [SiswaPrasarana::class, 'index'])->name('prasarana');
    Route::get('/profile', [SiswaProfile::class, 'show'])->name('profile');
    Route::put('/profile', [SiswaProfile::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [SiswaProfile::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/photo', [SiswaProfile::class, 'updatePhoto'])->name('profile.photo');

    // Attendance (selfie + history)
    Route::get('/attendance/location', [SiswaAttendance::class, 'locationCheck'])->name('attendance.location');
    Route::get('/attendance', [SiswaAttendance::class, 'show'])->name('attendance.show');
    Route::post('/attendance', [SiswaAttendance::class, 'store'])->name('attendance.store');
    Route::post('/attendance/checkout', [SiswaAttendance::class, 'storeCheckOut'])->name('attendance.checkout');
    Route::get('/attendance/history', [SiswaAttendance::class, 'history'])->name('attendance.history');

    // Permits (Izin, Sakit & Dispensasi)
    Route::get('/permits', [PermitController::class, 'index'])->name('permit.index');
    Route::get('/permits/create', [PermitController::class, 'create'])->name('permit.create');
    Route::post('/permits', [PermitController::class, 'store'])->name('permit.store');
    Route::get('/permits/{permit}/edit', [PermitController::class, 'edit'])->name('permit.edit');
    Route::put('/permits/{permit}', [PermitController::class, 'update'])->name('permit.update');
    Route::delete('/permits/{permit}', [PermitController::class, 'destroy'])->name('permit.destroy');

    // Exit Pass
    Route::get('/exit-pass', [ExitPassController::class, 'show'])->name('exit-pass.show');
    Route::post('/exit-pass', [ExitPassController::class, 'store'])->name('exit-pass.store');
    Route::patch('/exit-pass/checkin', [ExitPassController::class, 'checkin'])->name('exit-pass.checkin');

    // Poin
    Route::get('/conduct', [SiswaConduct::class, 'index'])->name('conduct.index');

    // Sarpras
    Route::prefix('sarpras')->name('sarpras.')->group(function () {
        Route::get('/catalog', [SiswaSarpras::class, 'catalog'])->name('catalog');
        Route::get('/scan', [SiswaSarpras::class, 'scan'])->name('scan');
        Route::get('/asset/{qrCode}', [SiswaSarpras::class, 'show'])->name('asset.show');
        Route::get('/damage/create/{asset}', [SiswaSarpras::class, 'createDamage'])->name('damage.create');
        Route::post('/damage', [SiswaSarpras::class, 'storeDamage'])->name('damage.store');
        Route::get('/loan/create/{asset}', [SiswaSarpras::class, 'createLoan'])->name('loan.create');
        Route::post('/loan', [SiswaSarpras::class, 'storeLoan'])->name('loan.store');
        Route::get('/loans', [SiswaSarpras::class, 'myLoans'])->name('loans');
    });

    // E-Voting (semua siswa)
    Route::prefix('voting')->name('voting.')->group(function () {
        Route::get('/', [SiswaVoting::class, 'index'])->name('index');
        Route::get('/manage', [SiswaVotingManage::class, 'index'])->name('manage.index');
        Route::get('/manage/create', [SiswaVotingManage::class, 'create'])->name('manage.create');
        Route::post('/manage', [SiswaVotingManage::class, 'store'])->name('manage.store');
        Route::get('/manage/{session}/edit', [SiswaVotingManage::class, 'edit'])->name('manage.edit');
        Route::put('/manage/{session}', [SiswaVotingManage::class, 'update'])->name('manage.update');
        Route::get('/manage/{session}', [SiswaVotingManage::class, 'show'])->name('manage.show');
        Route::post('/manage/{session}/candidate', [SiswaVotingManage::class, 'storeCandidate'])->name('manage.candidate.store');
        Route::delete('/manage/{session}/candidate/{candidate}', [SiswaVotingManage::class, 'removeCandidate'])->name('manage.candidate.remove');
        Route::patch('/manage/{session}/activate', [SiswaVotingManage::class, 'activate'])->name('manage.activate');
        Route::patch('/manage/{session}/close', [SiswaVotingManage::class, 'close'])->name('manage.close');
        Route::get('/{session}', [SiswaVoting::class, 'show'])->name('show');
        Route::post('/{session}/vote', [SiswaVoting::class, 'vote'])->name('vote');
    });

    // Prestasi
    Route::prefix('achievements')->name('achievements.')->group(function () {
        Route::get('/', [SiswaAchievement::class, 'index'])->name('index');
        Route::get('/report', [SiswaAchievement::class, 'report'])->name('report');
        Route::get('/create', [SiswaAchievement::class, 'create'])->name('create');
        Route::post('/', [SiswaAchievement::class, 'store'])->name('store');
        Route::get('/verify', [SiswaAchievementVerify::class, 'index'])->name('verify');
        Route::patch('/{achievement}/approve', [SiswaAchievementVerify::class, 'approve'])->name('approve');
        Route::patch('/{achievement}/reject', [SiswaAchievementVerify::class, 'reject'])->name('reject');
        Route::get('/{achievement}', [SiswaAchievement::class, 'show'])->name('show');
    });

    // Notifikasi
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [SiswaNotification::class, 'index'])->name('index');
        Route::patch('/read-all', [SiswaNotification::class, 'markAllRead'])->name('read-all');
        Route::patch('/{notification}/read', [SiswaNotification::class, 'markRead'])->name('read');
    });

    // Izin Pulang Lebih Awal (ajuan siswa)
    Route::prefix('early-checkout')->name('early-checkout.')->group(function () {
        Route::get('/', [SiswaEarlyCheckout::class, 'index'])->name('index');
        Route::get('/create', [SiswaEarlyCheckout::class, 'create'])->name('create');
        Route::post('/', [SiswaEarlyCheckout::class, 'store'])->name('store');
        Route::delete('/{earlyCheckout}', [SiswaEarlyCheckout::class, 'destroy'])->name('destroy');
    });

    // Lupa Absen (ajuan siswa)
    Route::prefix('forgot-attendance')->name('forgot-attendance.')->group(function () {
        Route::get('/', [SiswaForgotAttendance::class, 'index'])->name('index');
        Route::get('/create', [SiswaForgotAttendance::class, 'create'])->name('create');
        Route::post('/', [SiswaForgotAttendance::class, 'store'])->name('store');
        Route::delete('/{forgotAttendance}', [SiswaForgotAttendance::class, 'destroy'])->name('destroy');
    });

    // Pengumuman
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [SiswaAnnouncement::class, 'index'])->name('index');
        Route::get('/manage', [SiswaAnnouncement::class, 'manageIndex'])->name('manage');
        Route::get('/create', [SiswaAnnouncement::class, 'create'])->name('create');
        Route::post('/', [SiswaAnnouncement::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [SiswaAnnouncement::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [SiswaAnnouncement::class, 'update'])->name('update');
        Route::delete('/{announcement}', [SiswaAnnouncement::class, 'destroy'])->name('destroy');
        Route::get('/{announcement}', [SiswaAnnouncement::class, 'show'])->name('show');
    });
});
