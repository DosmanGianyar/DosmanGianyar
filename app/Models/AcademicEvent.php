<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AcademicEvent extends Model
{
    protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'type', 'color', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'uts'      => 'UTS',
            'uas'      => 'UAS',
            'ujian'    => 'Ujian',
            'libur'    => 'Libur',
            'kegiatan' => 'Kegiatan',
            'upacara'  => 'Upacara',
            default    => 'Lainnya',
        };
    }

    public function colorClass(): string
    {
        return match($this->color) {
            'green'  => 'bg-green-100 text-green-700',
            'red'    => 'bg-red-100 text-red-700',
            'yellow' => 'bg-yellow-100 text-yellow-700',
            'purple' => 'bg-purple-100 text-purple-700',
            'orange' => 'bg-orange-100 text-orange-700',
            default  => 'bg-blue-100 text-blue-700',
        };
    }

    public function dotClass(): string
    {
        return match($this->color) {
            'green'  => 'bg-green-500',
            'red'    => 'bg-red-500',
            'yellow' => 'bg-yellow-500',
            'purple' => 'bg-purple-500',
            'orange' => 'bg-orange-500',
            default  => 'bg-blue-500',
        };
    }
}
