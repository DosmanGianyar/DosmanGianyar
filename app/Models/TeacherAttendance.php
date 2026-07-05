<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    protected $fillable = [
        'teacher_id', 'schedule_id', 'class_id', 'subject_id',
        'date', 'period', 'start_time', 'end_time', 'status', 'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function sessionAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SessionAttendance::class);
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
