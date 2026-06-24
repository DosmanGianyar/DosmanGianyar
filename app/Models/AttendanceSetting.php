<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'check_in_open',
        'check_in_late',
        'check_in_close',
        'check_out_open',
        'check_out_close',
    ];

    public static function current(): static
    {
        return static::firstOrCreate([], [
            'check_in_open'  => '06:00:00',
            'check_in_late'  => '07:30:00',
            'check_in_close' => '08:00:00',
            'check_out_open' => '13:00:00',
        ]);
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
