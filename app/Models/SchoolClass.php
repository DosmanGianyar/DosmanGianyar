<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name', 'grade', 'major', 'homeroom_teacher_id'];

    protected static function booted(): void
    {
        static::addGlobalScope('realClass', function ($builder) {
            $builder->where(function ($q) {
                $q->whereNull('grade')
                  ->orWhere('grade', '!=', '0');
            });
        });

        static::updated(function (SchoolClass $class) {
            if ($class->wasChanged('name')) {
                // Perbarui nama ruang obrolan grup kelas
                ChatRoom::where('school_class_id', $class->id)
                    ->where('type', 'class_group')
                    ->update(['name' => 'Grup Kelas ' . $class->name]);
            }
        });

        static::deleting(function (SchoolClass $class) {
            // Hapus ruang obrolan grup kelas jika kelas dihapus
            ChatRoom::where('school_class_id', $class->id)
                ->where('type', 'class_group')
                ->delete();
        });
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')->whereIn('role', ['siswa', 'pengelola']);
    }

    public function getFullNameAttribute(): string
    {
        return "Kelas {$this->name}";
    }
}
