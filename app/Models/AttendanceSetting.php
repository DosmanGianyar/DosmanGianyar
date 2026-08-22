<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'day_of_week',
        'day_name',
        'check_in_open',
        'check_in_late',
        'check_in_close',
        'check_out_open',
        'check_out_close',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get setting for a specific day of week (1 = Monday, 7 = Sunday).
     */
    public static function forDay(int $dayOfWeek): static
    {
        $setting = static::where('day_of_week', $dayOfWeek)->first();

        if (! $setting) {
            $defaultDayNames = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
            ];
            $checkOut = $dayOfWeek === 6 ? '11:00:00' : '13:30:00';

            $setting = static::create([
                'day_of_week'    => $dayOfWeek,
                'day_name'       => $defaultDayNames[$dayOfWeek] ?? 'Hari',
                'check_in_open'  => '05:00:00',
                'check_in_late'  => '07:15:00',
                'check_in_close' => '08:00:00',
                'check_out_open' => $checkOut,
                'is_active'      => $dayOfWeek !== 7,
            ]);
        }

        return $setting;
    }

    /**
     * Get setting for today.
     */
    public static function forToday(): static
    {
        return static::forDay((int) now()->dayOfWeekIso);
    }

    /**
     * Backward compatibility wrapper for existing code.
     */
    public static function current(): static
    {
        return static::forToday();
    }

    /**
     * Reset all 7 daily settings to default values.
     */
    public static function resetToDefault(): void
    {
        $days = [
            1 => ['name' => 'Senin',  'check_out' => '13:30:00', 'active' => true],
            2 => ['name' => 'Selasa', 'check_out' => '13:30:00', 'active' => true],
            3 => ['name' => 'Rabu',   'check_out' => '13:30:00', 'active' => true],
            4 => ['name' => 'Kamis',  'check_out' => '13:30:00', 'active' => true],
            5 => ['name' => 'Jumat',  'check_out' => '13:30:00', 'active' => true],
            6 => ['name' => 'Sabtu',  'check_out' => '11:00:00', 'active' => true],
            7 => ['name' => 'Minggu', 'check_out' => '12:00:00', 'active' => false],
        ];

        foreach ($days as $num => $day) {
            static::updateOrCreate(
                ['day_of_week' => $num],
                [
                    'day_name'       => $day['name'],
                    'check_in_open'  => '05:00:00',
                    'check_in_late'  => '07:15:00',
                    'check_in_close' => '08:00:00',
                    'check_out_open' => $day['check_out'],
                    'check_out_close'=> null,
                    'is_active'      => $day['active'],
                ]
            );
        }
    }

    public function formattedOpen(): string
    {
        return substr($this->check_in_open, 0, 5);
    }

    public function formattedLate(): string
    {
        return substr($this->check_in_late, 0, 5);
    }

    public function formattedClose(): string
    {
        return substr($this->check_in_close, 0, 5);
    }

    public function formattedCheckOutOpen(): string
    {
        return substr($this->check_out_open, 0, 5);
    }
}
