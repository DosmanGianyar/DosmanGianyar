<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePiketTeacherSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admin tetap diizinkan mengakses untuk inspeksi tanpa wajib memilih guru
        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role === 'piket') {
            if ($request->routeIs('piket.select-teacher', 'piket.select-teacher.store', 'logout')) {
                return $next($request);
            }

            $teacherId = $request->session()->get('piket_teacher_id');
            $validTeacher = $teacherId
                ? User::where('id', $teacherId)->where('role', 'guru')->exists()
                : false;

            if (! $validTeacher) {
                $request->session()->forget(['piket_teacher_id', 'piket_teacher_name', 'piket_session_id']);
                return redirect()->route('piket.select-teacher')
                    ->with('warning', 'Silakan pilih nama guru piket yang bertugas terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
