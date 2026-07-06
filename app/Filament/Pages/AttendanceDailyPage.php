<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\Attendance;
use App\Models\EarlyCheckoutRequest;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class AttendanceDailyPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon       = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null   $navigationGroup      = 'Kesiswaan';
    protected static ?string                 $navigationParentItem = 'Presensi';
    protected static ?string                 $navigationLabel      = 'Absensi Harian';
    protected static ?string                 $title                = 'Absensi Harian Siswa';
    protected static ?int                    $navigationSort       = 14;

    public static function canAccess(): bool { return AdminAccess::can('Kesiswaan'); }

    protected string $view = 'filament.pages.attendance-daily';

    public ?int    $classId = null;
    public string  $date;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function getClasses(): \Illuminate\Support\Collection
    {
        return SchoolClass::orderByRaw("CASE grade WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'name', 'grade']);
    }

    public function getRows(): array
    {
        if (! $this->classId) return [];

        $date     = \Carbon\Carbon::parse($this->date);
        $dateStr  = $date->toDateString();

        $students = User::where('role', 'siswa')
            ->where('class_id', $this->classId)
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        if ($students->isEmpty()) return [];

        $studentIds = $students->pluck('id');

        // Attendance records for the date
        $attendances = Attendance::whereIn('user_id', $studentIds)
            ->whereDate('date', $dateStr)
            ->get()
            ->keyBy('user_id');

        // Approved permits covering this date
        $permits = Permit::whereIn('student_id', $studentIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->get(['student_id', 'type'])
            ->keyBy('student_id');

        // Approved early checkouts for this date
        $earlyCheckouts = EarlyCheckoutRequest::whereIn('student_id', $studentIds)
            ->whereDate('date', $dateStr)
            ->where('status', 'approved')
            ->pluck('student_id')
            ->flip()
            ->all();

        $rows = [];
        foreach ($students as $student) {
            $att              = $attendances->get($student->id);
            $hasEarlyCheckout = isset($earlyCheckouts[$student->id]);
            $permit           = $permits->get($student->id);

            if ($att) {
                $status   = $att->effectiveStatus($hasEarlyCheckout);
                $checkIn  = $att->check_in_time ? substr($att->check_in_time, 0, 5) : null;
                $checkOut = $att->check_out_time ? substr($att->check_out_time, 0, 5) : null;
            } elseif ($permit) {
                $status   = $permit->type;
                $checkIn  = null;
                $checkOut = null;
            } else {
                $status   = 'alpa';
                $checkIn  = null;
                $checkOut = null;
            }

            $rows[] = [
                'attendance_id' => $att?->id,
                'name'          => $student->name,
                'nis'           => $student->nis ?? '—',
                'status'        => $status,
                'check_in'      => $checkIn,
                'check_out'     => $checkOut,
                'photo_in_url'  => $att?->photo_url,
                'photo_out_url' => $att?->check_out_photo_url,
            ];
        }

        return $rows;
    }

    /**
     * TESTING ONLY — hapus method ini (+ tombol di view) setelah tahap uji coba selesai.
     */
    public function deleteAttendance(int $attendanceId): void
    {
        $attendance = Attendance::find($attendanceId);
        if (! $attendance) return;

        foreach ([$attendance->photo, $attendance->check_out_photo] as $photo) {
            if ($photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $attendance->delete();

        Notification::make()
            ->title('Absensi & foto berhasil dihapus (mode testing)')
            ->success()
            ->send();
    }

    public function getSummary(array $rows): array
    {
        $counts = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'dispensasi' => 0];
        foreach ($rows as $r) {
            if (isset($counts[$r['status']])) $counts[$r['status']]++;
        }
        return $counts;
    }
}
