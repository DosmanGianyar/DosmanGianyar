<?php

namespace App\Filament\Support;

class AdminAccess
{
    /**
     * Role → navigation group yang diizinkan.
     * null = akses ke semua group (superadmin).
     */
    private const SCOPES = [
        'admin'              => null,
        'admin_kesiswaan'    => ['Kesiswaan', 'Presensi Siswa', 'Kedisiplinan & Tata Tertib', 'SIPINTER (Pendidikan Karakter)', 'Prestasi & Ekskul', 'Kesiswaan & Layanan'],
        'admin_kurikulum'    => ['Kurikulum'],
        'admin_sarpras'      => ['Sarpras', 'Perpustakaan'],
        'admin_humas'        => ['Humas'],
        'admin_perpustakaan' => ['Perpustakaan'],
    ];

    /**
     * Cek apakah user yang sedang login boleh mengakses resource
     * pada navigation group tertentu.
     */
    public static function can(string $group): bool
    {
        $role = auth()->user()?->role;

        if (! array_key_exists($role, self::SCOPES)) {
            return false;
        }

        $scope = self::SCOPES[$role];

        if ($scope === null) {
            return true;
        }

        if (is_array($scope)) {
            return in_array($group, $scope, true);
        }

        return $scope === $group;
    }

    /**
     * Cek apakah user adalah salah satu admin (termasuk sub-admin).
     */
    public static function isAnyAdmin(): bool
    {
        return array_key_exists(auth()->user()?->role, self::SCOPES);
    }
}
