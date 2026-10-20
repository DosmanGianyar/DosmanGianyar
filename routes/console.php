<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-mark alpa every weekday at 08:00 WIB (UTC+7 → 01:00 UTC)
Schedule::command('attendance:auto-alpa')->weekdays()->dailyAt('01:00');

// Auto-close expired voting sessions every minute
Schedule::command('voting:close-expired')->everyMinute();

// Daily database backup at 02:00 WIB (19:00 UTC), keep last 7
Schedule::command('db:backup --keep=7')->dailyAt('19:00');

// Pengingat Presensi Masuk (07:00 WITA) & Pulang (13:45 WITA) - Senin sampai Sabtu
Schedule::command('attendance:send-reminders checkin')
    ->days([1, 2, 3, 4, 5, 6])
    ->at('07:00')
    ->timezone('Asia/Makassar');

Schedule::command('attendance:send-reminders checkout')
    ->days([1, 2, 3, 4, 5, 6])
    ->at('13:45')
    ->timezone('Asia/Makassar');

// Auto check & pull GitHub repository updates weekly (Senin 03:00 WITA)
Schedule::command('git:check-update')->weeklyOn(1, '03:00')->timezone('Asia/Makassar');

