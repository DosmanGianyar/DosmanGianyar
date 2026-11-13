<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ForgotAttendanceRequest;
use App\Models\PasswordResetRequest;
use App\Models\Permit;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoApproveStudentRequestsCommand extends Command
{
    protected $signature = 'requests:auto-approve {--dry-run : Simulasi tanpa menyimpan perubahan}';

    protected $description = 'Otomatis menyetujui lupa absen, reset password siswa, dan izin/sakit siswa <= 2 hari';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('Menjalankan dalam mode DRY-RUN (tidak ada data yang disimpan).');
        }

        $systemAdmin = User::where('role', 'admin')->first();

        $forgotApproved = $this->autoApproveForgotAttendance($isDryRun, $systemAdmin);
        $passwordApproved = $this->autoApprovePasswordResets($isDryRun, $systemAdmin);
        $permitsApproved = $this->autoApprovePermits($isDryRun, $systemAdmin);

        $summary = sprintf(
            'Auto-Approve Selesai: %d Lupa Absen, %d Reset Password Siswa, %d Izin/Sakit (<= 2 hari).',
            $forgotApproved,
            $passwordApproved,
            $permitsApproved
        );

        $this->info($summary);
        Log::info('[AutoApproveRequests] ' . $summary);

        return self::SUCCESS;
    }

    /**
     * 1. Konfirmasi / Setujui Otomatis Lupa Absen Siswa
     */
    private function autoApproveForgotAttendance(bool $isDryRun, ?User $systemAdmin): int
    {
        $requests = ForgotAttendanceRequest::where('status', 'pending')
            ->with(['student.schoolClass'])
            ->get();

        $count = 0;

        foreach ($requests as $request) {
            if (! $request->student) {
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY-RUN] Lupa Absen: Siswa {$request->student->name} ({$request->date->toDateString()} - {$request->type})");
                $count++;
                continue;
            }

            try {
                DB::transaction(function () use ($request, $systemAdmin) {
                    $type = $request->type ?: 'masuk';

                    // Update atau buat data absensi
                    $att = Attendance::where('user_id', $request->student_id)
                        ->whereDate('date', $request->date)
                        ->first() ?? new Attendance([
                            'user_id' => $request->student_id,
                            'date'    => $request->date->toDateString(),
                        ]);

                    $att->status          = 'hadir';
                    $att->via_lupa_absen  = true;
                    $att->lupa_absen_type = $type;

                    if ($type === 'masuk') {
                        if (! $att->check_in_time) {
                            $att->check_in_time = '07:00:00';
                        }
                    } elseif ($type === 'pulang') {
                        if (! $att->check_out_time) {
                            $att->check_out_time = '15:30:00';
                        }
                    } else {
                        if (! $att->check_in_time) {
                            $att->check_in_time = '07:00:00';
                        }
                        if (! $att->check_out_time) {
                            $att->check_out_time = '15:30:00';
                        }
                    }
                    $att->save();

                    // Tentukan reviewer: wali kelas jika ada, jika tidak fallback ke admin
                    $homeroomId = $request->student->schoolClass?->homeroom_teacher_id;
                    $reviewerId = $homeroomId ?? $systemAdmin?->id;

                    $request->update([
                        'status'       => 'approved',
                        'reviewed_by'  => $reviewerId,
                        'reviewed_at'  => now(),
                        'teacher_note' => 'Disetujui otomatis oleh sistem.',
                    ]);
                });

                // Kirim notifikasi
                NotificationService::send(
                    userId: $request->student_id,
                    title:  'Lupa Absen Disetujui Otomatis',
                    body:   'Pengajuan ' . $request->typeLabel() . ' tanggal ' . $request->date->isoFormat('D MMMM Y') . ' telah disetujui otomatis oleh sistem. Presensi dicatat sebagai Hadir.',
                    type:   'success',
                    url:    route('siswa.forgot-attendance.index'),
                );

                $count++;
            } catch (\Throwable $e) {
                Log::error("[AutoApproveRequests] Gagal approve lupa absen #{$request->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * 2. Reset Password Siswa Otomatis
     */
    private function autoApprovePasswordResets(bool $isDryRun, ?User $systemAdmin): int
    {
        $requests = PasswordResetRequest::where('status', 'pending')
            ->whereHas('user', fn ($q) => $q->whereIn('role', ['siswa', 'pengelola']))
            ->with('user')
            ->get();

        $count = 0;

        foreach ($requests as $request) {
            $user = $request->user;
            if (! $user) {
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY-RUN] Reset Password: Siswa {$user->name} ({$user->nisn})");
                $count++;
                continue;
            }

            try {
                $admin = $systemAdmin ?? $user;
                $request->approve($admin);

                $default = $user->isSiswa() ? ($user->nisn ?: 'NISN') : 'NISN';
                NotificationService::send(
                    userId: $request->user_id,
                    title:  'Password Direset Otomatis',
                    body:   "Permintaan reset password Anda telah disetujui otomatis oleh sistem. Silakan login menggunakan {$default} sebagai password default, lalu segera perbarui password di profil.",
                    type:   'success',
                );

                $count++;
            } catch (\Throwable $e) {
                Log::error("[AutoApproveRequests] Gagal approve reset password #{$request->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * 3. Izin / Sakit Siswa Otomatis (Maksimal 2 Hari, Dispensasi Tidak Diproses)
     */
    private function autoApprovePermits(bool $isDryRun, ?User $systemAdmin): int
    {
        // Hanya tipe izin dan sakit (dispensasi dilewati/manual)
        $permits = Permit::where('status', 'pending')
            ->whereIn('type', ['izin', 'sakit'])
            ->with(['student.schoolClass'])
            ->get();

        $count = 0;

        foreach ($permits as $permit) {
            if (! $permit->student || ! $permit->start_date || ! $permit->end_date) {
                continue;
            }

            // Hitung durasi hari (inklusif)
            $days = (int) $permit->start_date->diffInDays($permit->end_date) + 1;

            // Maksimal 2 hari saja (jika > 2 hari, biarkan manual untuk ditinjau guru/kesiswaan)
            if ($days > 2) {
                $this->line("  [SKIP] Permit #{$permit->id} siswa {$permit->student->name} ({$permit->typeLabel()}) berdurasi {$days} hari (> 2 hari). Memerlukan persetujuan manual.");
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY-RUN] Izin/Sakit: Siswa {$permit->student->name} ({$permit->typeLabel()}, {$days} hari)");
                $count++;
                continue;
            }

            try {
                DB::transaction(function () use ($permit, $systemAdmin) {
                    $homeroomId = $permit->student->schoolClass?->homeroom_teacher_id;
                    $approverId = $homeroomId ?? $systemAdmin?->id;

                    $permit->update([
                        'status'      => 'approved',
                        'approved_by' => $approverId,
                    ]);

                    // Sinkronisasi data absensi pada rentang tanggal izin/sakit
                    $current = $permit->start_date->copy();
                    while ($current->lte($permit->end_date)) {
                        if (! $current->isSunday()) {
                            Attendance::updateOrCreate(
                                [
                                    'user_id' => $permit->student_id,
                                    'date'    => $current->toDateString(),
                                ],
                                [
                                    'status'        => $permit->type, // 'izin' atau 'sakit'
                                    'check_in_time' => null,
                                ]
                            );
                        }
                        $current->addDay();
                    }
                });

                // Kirim notifikasi ke siswa
                NotificationService::send(
                    $permit->student_id,
                    "{$permit->typeLabel()} Disetujui Otomatis",
                    "Pengajuan {$permit->typeLabel()} kamu ({$permit->start_date->isoFormat('D MMM Y')} s/d {$permit->end_date->isoFormat('D MMM Y')}) telah disetujui otomatis oleh sistem.",
                    'success',
                    route('siswa.permit.index'),
                );

                // Kirim notifikasi ke orang tua jika ada
                if ($permit->student) {
                    NotificationService::notifyParentsOfStudent(
                        $permit->student,
                        "{$permit->typeLabel()} Ananda Disetujui Otomatis",
                        "Pengajuan {$permit->typeLabel()} untuk Ananda {$permit->student->name} ({$permit->start_date->isoFormat('D MMM Y')} s/d {$permit->end_date->isoFormat('D MMM Y')}) telah disetujui otomatis oleh sistem.",
                        'success'
                    );
                }

                $count++;
            } catch (\Throwable $e) {
                Log::error("[AutoApproveRequests] Gagal approve permit #{$permit->id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}

