<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLocation extends Model
{
    protected $fillable = [
        'name', 'latitude', 'longitude', 'radius_meters',
        'is_default', 'class_id', 'start_at', 'end_at', 'notes',
        'check_in_open', 'check_in_late', 'check_in_close', 'check_out_open',
    ];

    protected $casts = [
        'is_default'    => 'boolean',
        'start_at'      => 'datetime',
        'end_at'        => 'datetime',
        'latitude'      => 'float',
        'longitude'     => 'float',
        'radius_meters' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (AttendanceLocation $location) {
            if ($location->is_default) {
                static::where('is_default', true)
                    ->when($location->exists, fn ($q) => $q->where('id', '!=', $location->id))
                    ->update(['is_default' => false]);
            }
        });
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function isActiveNow(): bool
    {
        if ($this->is_default) {
            return true;
        }
        $now = now();
        return $this->start_at !== null
            && $this->end_at !== null
            && $this->start_at->lte($now)
            && $this->end_at->gte($now);
    }

    public function statusLabel(): string
    {
        if ($this->is_default) {
            return 'Default';
        }
        if (!$this->start_at || !$this->end_at) {
            return 'Tidak Aktif';
        }
        $now = now();
        if ($now->lt($this->start_at)) {
            return 'Mendatang';
        }
        if ($now->gt($this->end_at)) {
            return 'Selesai';
        }
        return 'Aktif';
    }

    public function statusColor(): string
    {
        return match ($this->statusLabel()) {
            'Default'    => 'info',
            'Aktif'      => 'success',
            'Mendatang'  => 'warning',
            default      => 'gray',
        };
    }

    public function hasTimeOverride(): bool
    {
        return filled($this->check_in_open)
            || filled($this->check_in_late)
            || filled($this->check_in_close)
            || filled($this->check_out_open);
    }

    /**
     * Get the active location for a given class, falling back to the school default.
     * Returns location data + any time overrides set on the location record.
     */
    public static function getForClass(?int $classId): array
    {
        if ($classId) {
            $override = static::where('class_id', $classId)
                ->where('is_default', false)
                ->where('start_at', '<=', now())
                ->where('end_at', '>=', now())
                ->latest('start_at')
                ->first();

            if ($override) {
                return [
                    'lat'           => $override->latitude,
                    'lng'           => $override->longitude,
                    'radius'        => $override->radius_meters,
                    'name'          => $override->name,
                    'check_in_open'  => $override->check_in_open,
                    'check_in_late'  => $override->check_in_late,
                    'check_in_close' => $override->check_in_close,
                    'check_out_open' => $override->check_out_open,
                ];
            }
        }

        $default = static::where('is_default', true)->first();

        return $default ? [
            'lat'           => $default->latitude,
            'lng'           => $default->longitude,
            'radius'        => $default->radius_meters,
            'name'          => $default->name,
            'check_in_open'  => null,
            'check_in_late'  => null,
            'check_in_close' => null,
            'check_out_open' => null,
        ] : [
            'lat'           => -8.542304297173528,
            'lng'           => 115.33400530740592,
            'radius'        => 50,
            'name'          => 'SMA Negeri 1 Gianyar',
            'check_in_open'  => null,
            'check_in_late'  => null,
            'check_in_close' => null,
            'check_out_open' => null,
        ];
    }
}
