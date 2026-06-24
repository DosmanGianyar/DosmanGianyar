<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'check_in_time', 'check_out_time',
        'latitude', 'longitude', 'photo', 'check_out_photo',
        'status', 'device_info', 'is_fake_gps',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'is_fake_gps'  => 'boolean',
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'hadir'       => 'Hadir',
            'terlambat'   => 'Terlambat',
            'izin'        => 'Izin',
            'sakit'       => 'Sakit',
            'alpa'        => 'Alpa',
            'dispensasi'  => 'Dispensasi',
            default       => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'hadir'       => 'green',
            'terlambat'   => 'yellow',
            'izin'        => 'blue',
            'sakit'       => 'purple',
            'dispensasi'  => 'indigo',
            'alpa'        => 'red',
            default       => 'gray',
        };
    }
}
