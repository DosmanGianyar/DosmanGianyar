<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extracurricular extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'description',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'extracurricular_teachers', 'extracurricular_id', 'teacher_id')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'extracurricular_students', 'extracurricular_id', 'student_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function leaders(): BelongsToMany
    {
        return $this->students()->wherePivotIn('role', ['ketua', 'wakil_ketua']);
    }
}
