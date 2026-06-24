<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionAttendance extends Model
{
    protected $fillable = [
        'teacher_attendance_id', 'student_id', 'class_id', 'subject_id',
        'date', 'period', 'status', 'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacherAttendance(): BelongsTo
    {
        return $this->belongsTo(TeacherAttendance::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'hadir'       => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'izin'        => 'Izin',
            'sakit'       => 'Sakit',
            default       => ucfirst($this->status),
        };
    }
}
