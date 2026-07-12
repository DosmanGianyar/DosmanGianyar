<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user?->must_change_password && ! $request->routeIs('siswa.profile', 'siswa.profile.password', 'logout')) {
            return redirect()->route('siswa.profile')
                ->with('warning', 'Anda wajib mengganti password sebelum melanjutkan.');
        }

        return $next($request);
    }
}
