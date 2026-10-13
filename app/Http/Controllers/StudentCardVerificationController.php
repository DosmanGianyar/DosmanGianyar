<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class StudentCardVerificationController extends Controller
{
    public function verify(?string $identifier = null): View
    {
        $identifier = trim((string) $identifier);

        // Ekstrak token / identifier jika input berupa full URL
        if (str_contains($identifier, '/verifikasi/kartu-pelajar/')) {
            $parts = explode('/verifikasi/kartu-pelajar/', $identifier);
            $after = end($parts);
            $identifier = explode('?', explode('#', $after)[0])[0];
        } elseif (str_contains($identifier, '/biodata/')) {
            $parts = explode('/biodata/', $identifier);
            $after = end($parts);
            $identifier = explode('?', explode('#', $after)[0])[0];
        } elseif (str_contains($identifier, '/')) {
            $parts = explode('/', rtrim($identifier, '/'));
            $after = end($parts);
            $identifier = explode('?', explode('#', $after)[0])[0];
        }

        $siswa = null;
        if (! empty($identifier)) {
            $siswa = User::where(function ($q) use ($identifier) {
                    $q->where('qr_code_token', $identifier);
                    $q->orWhere('nis', $identifier);
                    $q->orWhere('nisn', $identifier);
                    if (is_numeric($identifier)) {
                        $q->orWhere('id', (int) $identifier);
                    }
                })
                ->where(function ($q) {
                    $q->where('role', 'like', 'siswa%')
                      ->orWhere('role', 'pengelola');
                })
                ->with('schoolClass')
                ->first();
        }

        return view('public.verify-student-card', compact('siswa', 'identifier'));
    }
}
