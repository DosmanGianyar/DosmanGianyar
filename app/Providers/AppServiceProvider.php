<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\ConductLog;
use App\Observers\AnnouncementObserver;
use App\Observers\ConductLogObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Announcement::observe(AnnouncementObserver::class);
        ConductLog::observe(ConductLogObserver::class);

        // Max 5 login attempts per minute per login+IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(strtolower($request->input('login')) . '|' . $request->ip())
                ->response(function () {
                    return back()
                        ->withErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.'])
                        ->onlyInput('login');
                });
        });
    }
}
