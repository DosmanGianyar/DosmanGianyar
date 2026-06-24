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
