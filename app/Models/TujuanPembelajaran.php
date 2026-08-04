<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TujuanPembelajaran extends Model
{
    protected $table = 'tujuan_pembelajaran';

    protected $fillable = ['teacher_id', 'subject_id', 'grade_level', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function gradeLabel(): string
    {
        return match($this->grade_level) {
            '10', 'X'  => 'Kelas 10 (X)',
            '11', 'XI' => 'Kelas 11 (XI)',
            '12', 'XII'=> 'Kelas 12 (XII)',
            default    => 'Semua Tingkatan',
        };
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function journals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherJournal::class, 'tp_id');
    }
}
