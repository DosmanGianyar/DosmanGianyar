<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ConductLog extends Model
{
    protected $fillable = [
        'student_id', 'teacher_id', 'type', 'category_id', 'photo', 'note',
        'description', 'severity',
        'prestasi_type', 'lomba_name', 'lomba_level', 'lomba_rank',
        'is_self_reported', 'status', 'verified_at', 'verifier_id',
        'rejection_note',
    ];

    protected $casts = [
        'is_self_reported' => 'boolean',
        'verified_at'      => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ConductCategory::class, 'category_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function isPrestasi(): bool
    {
        $t = $this->category?->type ?? $this->type;
        return in_array($t, ['prestasi', 'positif']);
    }

    public function isPelanggaran(): bool
    {
        $t = $this->category?->type ?? $this->type;
        return $t === 'pelanggaran';
    }

    public function getParsedTitleAttribute(): string
    {
        if ($this->category && !str_starts_with($this->category->name, '__sistem__')) {
            return $this->category->name;
        }

        if (!empty($this->note) && preg_match('/^\[(.*?)\]\s*(.*)$/s', $this->note, $matches)) {
            $t = trim($matches[1]);
            return ucfirst($t);
        }

        if ($this->description) {
            return $this->description;
        }

        if ($this->lomba_name) {
            return 'Lomba: ' . $this->lomba_name;
        }

        return $this->isPrestasi() ? 'Catatan Apresiasi' : 'Catatan Kedisiplinan';
    }

    public function getParsedDescriptionAttribute(): ?string
    {
        if (!empty($this->note)) {
            if (preg_match('/^\[(.*?)\]\s*(.*)$/s', $this->note, $matches)) {
                return !empty($matches[2]) ? $matches[2] : null;
            }
            return $this->note;
        }

        return $this->description;
    }

    public function displayCategoryName(): string
    {
        return $this->getParsedTitleAttribute();
    }

    /**
     * Apakah laporan SIPINTER ini merupakan laporan keterlambatan hadir/masuk sekolah?
     */
    public function isLateSchoolArrival(): bool
    {
        if (! $this->isPelanggaran()) {
            return false;
        }

        if ($this->status === 'rejected') {
            return false;
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            $this->category?->name,
            $this->note,
            $this->description,
        ])));

        if (! str_contains($haystack, 'terlambat')) {
            return false;
        }

        // Abaikan jika ini keterlambatan kembali dari izin keluar atau peminjaman buku
        if (str_contains($haystack, 'izin keluar') || str_contains($haystack, 'peminjaman')) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah siswa memiliki laporan keterlambatan aktif di SIPINTER pada tanggal tertentu.
     */
    public static function hasLateReportForDate(int $studentId, Carbon|string $date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $logs = static::with('category')
            ->where('student_id', $studentId)
            ->whereDate('created_at', $dateStr)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'rejected');
            })
            ->get();

        return $logs->contains(fn (self $log) => $log->isLateSchoolArrival());
    }

    /**
     * Sinkronisasi status keterlambatan absensi siswa pada tanggal tertentu berdasarkan laporan SIPINTER.
     */
    public static function syncAttendanceForStudentDate(int $studentId, Carbon|string $date, ?string $fallbackTime = null): void
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $logs = static::with('category')
            ->where('student_id', $studentId)
            ->whereDate('created_at', $dateStr)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'rejected');
            })
            ->orderBy('created_at')
            ->get();

        /** @var self|null $lateLog */
        $lateLog = $logs->first(fn (self $log) => $log->isLateSchoolArrival());

        $attendance = Attendance::where('user_id', $studentId)
            ->whereDate('date', $dateStr)
            ->first();

        if ($lateLog) {
            $checkInTime = $fallbackTime ?? $lateLog->created_at?->format('H:i:s') ?? now()->format('H:i:s');
            if ($attendance) {
                $updates = [];
                if (in_array($attendance->status, ['hadir', 'alpa', 'terlambat'], true)) {
                    $updates['status'] = 'terlambat';
                }
                if (! $attendance->check_in_time) {
                    $updates['check_in_time'] = $checkInTime;
                }
                if (! empty($updates)) {
                    $attendance->update($updates);
                }
            } else {
                Attendance::create([
                    'user_id'       => $studentId,
                    'date'          => $dateStr,
                    'check_in_time' => $checkInTime,
                    'status'        => 'terlambat',
                ]);
            }
        } else {
            if ($attendance && $attendance->status === 'terlambat') {
                if ($attendance->photo || $attendance->via_lupa_absen || $attendance->check_out_time) {
                    $attendance->update(['status' => 'hadir']);
                } else {
                    $attendance->delete();
                }
            }
        }
    }
}
