<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class PublicBiodataController extends Controller
{
    public function show(string $identifier): View
    {
        $identifier = trim($identifier);
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

        $siswa = User::where(function ($q) {
                $q->where('role', 'like', 'siswa%')
                  ->orWhere('role', 'pengelola');
            })
            ->where(function ($q) use ($identifier) {
                $q->where('qr_code_token', $identifier)
                  ->orWhere('nis', $identifier)
                  ->orWhere('nisn', $identifier);
                if (is_numeric($identifier)) {
                    $q->orWhere('id', (int) $identifier);
                }
            })
            ->with('schoolClass')
            ->firstOrFail();

        return view('public.biodata', compact('siswa'));
    }
}
