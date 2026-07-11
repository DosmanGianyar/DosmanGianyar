<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OrangtuaSyncService
{
    public static function normalizePhone(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        } elseif (! str_starts_with($digits, '0')) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    /**
     * Buat/perbarui akun orangtua untuk sebuah nomor HP, lalu hubungkan ke semua
     * siswa yang parent_phone-nya cocok (mendukung kakak-adik satu nomor HP).
     */
    public static function syncFromPhone(?string $rawPhone): ?User
    {
        $phone = self::normalizePhone($rawPhone);

        if (! $phone) {
            return null;
        }

        $students = User::whereIn('role', ['siswa', 'pengelola'])
            ->get(['id', 'name', 'parent_name', 'parent_phone'])
            ->filter(fn (User $s) => self::normalizePhone($s->parent_phone) === $phone);

        if ($students->isEmpty()) {
            return null;
        }

        $parent = User::where('role', 'orangtua')->where('phone', $phone)->first();

        if (! $parent) {
            $firstStudent = $students->first();
            $parent = User::create([
                'name'     => $firstStudent->parent_name ?: ('Orangtua ' . $firstStudent->name),
                'email'    => $phone . '@ortu.sims.sch.id',
                'password' => Hash::make($phone),
                'role'     => 'orangtua',
                'phone'    => $phone,
            ]);
        }

        $parent->children()->syncWithoutDetaching($students->pluck('id'));

        return $parent;
    }

    /**
     * Sinkronisasi seluruh siswa sekaligus dalam satu pass (satu query, dikelompokkan
     * per nomor HP di PHP) — dipakai untuk sinkronisasi retroaktif/massal (mis. setelah
     * import Excel) agar tidak perlu scan tabel siswa berulang kali per baris.
     */
    public static function syncAll(): int
    {
        $groups = User::whereIn('role', ['siswa', 'pengelola'])
            ->whereNotNull('parent_phone')
            ->where('parent_phone', '!=', '')
            ->get(['id', 'name', 'parent_name', 'parent_phone'])
            ->groupBy(fn (User $s) => self::normalizePhone($s->parent_phone))
            ->filter(fn ($group, $phone) => filled($phone));

        $synced = 0;

        foreach ($groups as $phone => $students) {
            $parent = User::where('role', 'orangtua')->where('phone', $phone)->first();

            if (! $parent) {
                $first  = $students->first();
                $parent = User::create([
                    'name'     => $first->parent_name ?: ('Orangtua ' . $first->name),
                    'email'    => $phone . '@ortu.sims.sch.id',
                    'password' => Hash::make($phone),
                    'role'     => 'orangtua',
                    'phone'    => $phone,
                ]);
            }

            $parent->children()->syncWithoutDetaching($students->pluck('id'));
            $synced++;
        }

        return $synced;
    }

    /**
     * Sinkronisasi ulang untuk SATU siswa: lepas tautan ke akun orangtua lama yang
     * nomornya sudah tidak cocok lagi (mis. parent_phone diedit/dikosongkan admin),
     * lalu hubungkan ke akun orangtua yang sesuai nomor terbaru (kalau ada).
     * Mencegah akun orangtua lama tetap bisa mengakses data anak setelah nomor diganti.
     */
    public static function resyncStudent(User $student): void
    {
        $newPhone = self::normalizePhone($student->parent_phone);

        $student->parentAccounts()
            ->when(
                $newPhone,
                fn ($q) => $q->where('phone', '!=', $newPhone),
            )
            ->get()
            ->each(fn (User $oldParent) => $student->parentAccounts()->detach($oldParent->id));

        if ($newPhone) {
            self::syncFromPhone($newPhone);
        }
    }
}
