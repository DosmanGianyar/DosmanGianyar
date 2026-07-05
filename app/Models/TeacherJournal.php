<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherJournal extends Model
{
    protected $fillable = [
        'teacher_id', 'class_id', 'subject_id',
        'date', 'period', 'learning_objectives',
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

    public function absences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherJournalAbsence::class, 'journal_id');
    }
}
