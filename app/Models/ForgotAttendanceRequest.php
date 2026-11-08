<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForgotAttendanceRequest extends Model
{
    public const MONTHLY_LIMIT = 10;

    protected $fillable = [
        'student_id', 'type', 'date', 'reason', 'status',
        'reviewed_by', 'reviewed_at', 'teacher_note',
    ];

    protected $casts = [
        'date'        => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    /**
     * Hitung jumlah pengajuan lupa absen siswa pada bulan & tahun tertentu.
     * Absen datang (masuk), absen pulang (pulang), maupun keduanya (semua absen)
     * dihitung 1x saja per pengajuan/tanggal, dan di-reset setiap pergantian bulan.
     */
    public static function monthlyUsedCount(int $studentId, ?int $year = null, ?int $month = null): int
    {
        $year  = $year ?? (int) now()->year;
        $month = $month ?? (int) now()->month;

        return (int) static::where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('status', ['pending', 'approved'])
            ->selectRaw('COUNT(DISTINCT date) as total')
            ->value('total');
    }

    public static function monthlyRemainingQuota(int $studentId, ?int $year = null, ?int $month = null): int
    {
        return max(0, self::MONTHLY_LIMIT - self::monthlyUsedCount($studentId, $year, $month));
    }

    public static function monthlySummaryForStudent(int $studentId, ?int $year = null, ?int $month = null): array
    {
        $year  = $year ?? (int) now()->year;
        $month = $month ?? (int) now()->month;

        $used = self::monthlyUsedCount($studentId, $year, $month);

        $approved = (int) static::where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'approved')
            ->selectRaw('COUNT(DISTINCT date) as total')
            ->value('total');

        $pending = (int) static::where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'pending')
            ->selectRaw('COUNT(DISTINCT date) as total')
            ->value('total');

        $rejected = (int) static::where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'rejected')
            ->selectRaw('COUNT(DISTINCT date) as total')
            ->value('total');

        return [
            'limit'      => self::MONTHLY_LIMIT,
            'used'       => $used,
            'remaining'  => max(0, self::MONTHLY_LIMIT - $used),
            'approved'   => $approved,
            'pending'    => $pending,
            'rejected'   => $rejected,
            'year'       => $year,
            'month'      => $month,
            'month_name' => Carbon::createFromDate($year, $month, 1)->isoFormat('MMMM Y'),
        ];
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'pulang'   => 'Lupa Absen Pulang',
            'keduanya' => 'Lupa Absen Datang & Pulang',
            default    => 'Lupa Absen Datang',
        };
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'pulang'   => 'bg-amber-100 text-amber-800 border-amber-200',
            'keduanya' => 'bg-purple-100 text-purple-800 border-purple-200',
            default    => 'bg-blue-100 text-blue-800 border-blue-200',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-700',
        };
    }
}
