<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherJournalAbsence extends Model
{
    protected $fillable = ['journal_id', 'student_id', 'status'];

    public function journal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TeacherJournal::class, 'journal_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
