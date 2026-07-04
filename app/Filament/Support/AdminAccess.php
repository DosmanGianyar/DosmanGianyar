<?php

namespace App\Filament\Support;

class AdminAccess
{
    /**
     * Role → navigation group yang diizinkan.
     * null = akses ke semua group (superadmin).
     */
    private const SCOPES = [
        'admin'           => null,
        'admin_kesiswaan' => 'Kesiswaan',
        'admin_kurikulum' => 'Kurikulum',
        'admin_sarpras'   => 'Sarpras',
        'admin_humas'     => 'Humas',
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

        return $scope === null || $scope === $group;
    }

    /**
     * Cek apakah user adalah salah satu admin (termasuk sub-admin).
     */
    public static function isAnyAdmin(): bool
    {
        return array_key_exists(auth()->user()?->role, self::SCOPES);
    }
}
