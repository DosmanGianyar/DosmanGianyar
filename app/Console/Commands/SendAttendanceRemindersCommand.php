<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Permit;
use App\Models\User;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAttendanceRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:send-reminders {type : checkin or checkout}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat presensi masuk (07:00 WITA) atau presensi pulang (13:45 WITA) ke siswa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = strtolower($this->argument('type'));
        if (!in_array($type, ['checkin', 'checkout'])) {
            $this->error("Tipe pengingat harus 'checkin' atau 'checkout'.");
            return 1;
        }

        $today = Carbon::now('Asia/Makassar'); // WITA timezone for SMAN 1 Gianyar
        $todayStr = $today->toDateString();

        $this->info("Menjalankan pengingat presensi [$type] tanggal: " . $todayStr . " (WITA)");

        if ($type === 'checkin') {
            $this->sendCheckInReminders($today);
        } else {
            $this->sendCheckOutReminders($today);
        }

        return 0;
    }

    private function sendCheckInReminders(Carbon $today)
    {
        $todayStr = $today->toDateString();

        // 1. Cek apakah hari ini disetel sebagai hari libur khusus oleh admin untuk seluruh sekolah
        if (Holiday::holidayExistsFor($today, null)) {
            $this->info("Hari ini disetel sebagai Hari Libur Khusus oleh Admin. Pengingat presensi masuk dilewati.");
            Log::info("[SendAttendanceRemindersCommand] CheckIn reminder skipped: Global holiday set by admin on " . $todayStr);
            return;
        }

        // Ambil semua siswa aktif
        $students = User::whereIn('role', ['siswa', 'pengelola'])->get();

        // Filter ID siswa yang sudah ada izin/sakit/dispensasi yang disetujui hari ini
        $permittedUserIds = Permit::where('status', 'approved')
            ->whereDate('start_date', '<=', $todayStr)
            ->whereDate('end_date', '>=', $todayStr)
            ->pluck('student_id')
            ->toArray();

        // Filter ID siswa yang sudah presensi hari ini
        $checkedInUserIds = Attendance::whereDate('date', $todayStr)
            ->whereIn('status', ['hadir', 'terlambat', 'izin', 'sakit', 'dispensasi'])
            ->pluck('user_id')
            ->toArray();

        $targetUserIds = [];

        foreach ($students as $student) {
            // Check holiday or weekend off-day (admin setting libur khusus / kelas tertentu)
            if (Holiday::isOffDayFor($today, $student->class_id)) {
                continue;
            }

            // Pengecualian: sudah izin/sakit/dispen atau sudah absen
            if (in_array($student->id, $permittedUserIds) || in_array($student->id, $checkedInUserIds)) {
                continue;
            }

            $targetUserIds[] = $student->id;
        }

        if (empty($targetUserIds)) {
            $this->info("Tidak ada siswa yang perlu diberi pengingat presensi masuk (semua sudah absen, izin, atau libur).");
            return;
        }

        $title = '🔔 Pengingat Presensi Masuk';
        $body  = 'Halo! Jangan lupa melakukan presensi masuk hari ini sebelum jam 08:00 WITA. Selamat belajar & beraktivitas! 🏫';

        // 1. Simpan ke database AppNotification (History di App)
        foreach ($targetUserIds as $userId) {
            AppNotification::create([
                'user_id' => $userId,
                'title'   => $title,
                'message' => $body,
                'type'    => 'reminder_checkin',
                'is_read' => false,
            ]);
        }

        // 2. Meletupkan Push Notification FCM ke HP Android
        $sent = FcmService::sendToUsers($targetUserIds, $title, $body, ['type' => 'reminder_checkin']);

        $this->info("Berhasil memproses " . count($targetUserIds) . " siswa untuk pengingat masuk. FCM Sent: " . ($sent ? 'Ya' : 'Tidak'));
        Log::info("[SendAttendanceRemindersCommand] CheckIn reminder sent to " . count($targetUserIds) . " students.");
    }

    private function sendCheckOutReminders(Carbon $today)
    {
        $todayStr = $today->toDateString();

        // 1. Cek apakah hari ini disetel sebagai hari libur khusus oleh admin untuk seluruh sekolah
        if (Holiday::holidayExistsFor($today, null)) {
            $this->info("Hari ini disetel sebagai Hari Libur Khusus oleh Admin. Pengingat presensi pulang dilewati.");
            Log::info("[SendAttendanceRemindersCommand] CheckOut reminder skipped: Global holiday set by admin on " . $todayStr);
            return;
        }

        // Siswa yang sudah check-in hari ini
        $todayAttendances = Attendance::whereDate('date', $todayStr)
            ->whereNotNull('check_in_time')
            ->get();

        if ($todayAttendances->isEmpty()) {
            $this->info("Tidak ada siswa yang absen masuk hari ini.");
            return;
        }

        // Filter ID siswa yang sudah ada izin/sakit/dispensasi
        $permittedUserIds = Permit::where('status', 'approved')
            ->whereDate('start_date', '<=', $todayStr)
            ->whereDate('end_date', '>=', $todayStr)
            ->pluck('student_id')
            ->toArray();

        $targetUserIds = [];

        foreach ($todayAttendances as $attendance) {
            // Abaikan jika sudah absen pulang
            if ($attendance->check_out_time != null) {
                continue;
            }

            $student = $attendance->user;
            if (!$student) continue;

            // Check holiday or weekend off-day (admin setting libur khusus / kelas tertentu)
            if (Holiday::isOffDayFor($today, $student->class_id)) {
                continue;
            }

            // Abaikan jika ada izin/sakit/dispensasi
            if (in_array($student->id, $permittedUserIds)) {
                continue;
            }

            $targetUserIds[] = $student->id;
        }

        if (empty($targetUserIds)) {
            $this->info("Tidak ada siswa yang perlu diberi pengingat presensi pulang (semua sudah absen pulang atau memiliki izin).");
            return;
        }

        $title = '🔔 Pengingat Presensi Pulang';
        $body  = 'Halo! Sekarang sudah jam 13:45 WITA dan waktunya presensi pulang. Jangan lupa absen pulang melalui aplikasi ya! 👋';

        // 1. Simpan ke AppNotification
        foreach ($targetUserIds as $userId) {
            AppNotification::create([
                'user_id' => $userId,
                'title'   => $title,
                'message' => $body,
                'type'    => 'reminder_checkout',
                'is_read' => false,
            ]);
        }

        // 2. Meletupkan Push Notification FCM ke HP Android
        $sent = FcmService::sendToUsers($targetUserIds, $title, $body, ['type' => 'reminder_checkout']);

        $this->info("Berhasil memproses " . count($targetUserIds) . " siswa untuk pengingat pulang. FCM Sent: " . ($sent ? 'Ya' : 'Tidak'));
        Log::info("[SendAttendanceRemindersCommand] CheckOut reminder sent to " . count($targetUserIds) . " students.");
    }
}
