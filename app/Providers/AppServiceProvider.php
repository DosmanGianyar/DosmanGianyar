<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Observers\AnnouncementObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Set seluruh penanggalan & Carbon ke bahasa Indonesia
        \Illuminate\Support\Carbon::setLocale('id');
        @setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'indonesia', 'id');

        // Paksa HTTPS jika di environment produksi atau dibalik SSL reverse proxy
        if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        Announcement::observe(AnnouncementObserver::class);

        // Max 5 login attempts per minute per login+IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(strtolower((string) $request->input('login')) . '|' . $request->ip())
                ->response(function () {
                    if (request()->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Terlalu banyak percobaan login gagal. Coba lagi dalam 1 menit.',
                        ], 429);
                    }

                    return back()
                        ->withErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.'])
                        ->onlyInput('login');
                });
        });
    }
}
