<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherJournal extends Model
{
    protected $fillable = [
        'teacher_id', 'class_id', 'subject_id', 'tp_id',
        'date', 'period', 'period_end', 'learning_objectives',
        'material', 'activity', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function tp(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tp_id');
    }

    public function absences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherJournalAbsence::class, 'journal_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (TeacherJournal $journal) {
            // 1. Delete absences
            try {
                $journal->absences()->delete();
            } catch (\Throwable $e) {}

            // 2. Cascade delete matching TeacherAttendance and SessionAttendance
            $dateStr = $journal->date ? $journal->date->format('Y-m-d') : null;
            if ($dateStr && $journal->class_id) {
                $pStart = (int) ($journal->period ?? 1);
                $pEnd   = (int) ($journal->period_end ?? $pStart);

                $teacherAtts = TeacherAttendance::where('class_id', $journal->class_id)
                    ->whereDate('date', $dateStr)
                    ->where(function ($q) use ($pStart, $pEnd) {
                        $q->whereBetween('period', [$pStart, $pEnd]);
                    })
                    ->get();

                foreach ($teacherAtts as $att) {
                    $att->sessionAttendances()->delete();
                    $att->deleteQuietly();
                }

                SessionAttendance::where('class_id', $journal->class_id)
                    ->whereDate('date', $dateStr)
                    ->whereBetween('period', [$pStart, $pEnd])
                    ->delete();
            }
        });
    }
}
