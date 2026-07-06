<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    /**
     * URL foto check-in — dibubuhi query string ?v= (cache-busting) supaya
     * browser/HP tidak menampilkan foto lama yang ter-cache saat file di
     * path yang sama diganti/dihapus-lalu-diganti (nama file deterministik
     * per user+tanggal).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) return null;
        return Storage::disk('public')->url($this->photo) . '?v=' . $this->updated_at?->timestamp;
    }

    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        if (! $this->check_out_photo) return null;
        return Storage::disk('public')->url($this->check_out_photo) . '?v=' . $this->updated_at?->timestamp;
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

    /**
     * Returns the true attendance status.
     * A student who checked in but never checked out (and has no approved early checkout)
     * is counted as alpa for past days.
     */
    public function effectiveStatus(bool $hasApprovedEarlyCheckout = false): string
    {
        // no check-in → izin/sakit/dispensasi/alpa, keep raw status
        if (! $this->check_in_time) return $this->status;
        // completed checkout → hadir/terlambat as recorded
        if ($this->check_out_time) return $this->status;
        // approved early checkout → treat as present
        if ($hasApprovedEarlyCheckout) return $this->status;
        // still today → student may still check out
        if ($this->date->isToday()) return $this->status;
        // past day with no checkout → alpa
        return 'alpa';
    }
}
