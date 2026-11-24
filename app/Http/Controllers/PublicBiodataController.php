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

        $viewer = auth()->user();
        $staffRoles = [
            'guru',
            'pegawai',
            'piket',
            'admin',
            'superadmin',
            'admin_kesiswaan',
            'admin_kurikulum',
            'admin_prestasi',
        ];

        if ($viewer && in_array($viewer->role, ['siswa', 'pengelola'], true)) {
            abort_if($siswa->id !== $viewer->id, 403, 'Anda hanya dapat melihat biodata Anda sendiri.');
        } elseif ($viewer && $viewer->role === 'orangtua') {
            abort_unless($viewer->children()->where('users.id', $siswa->id)->exists(), 403, 'Anda hanya dapat melihat biodata anak Anda sendiri.');
        } elseif (! $viewer || ! in_array($viewer->role, $staffRoles, true)) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return view('public.biodata', compact('siswa'));
    }
}
