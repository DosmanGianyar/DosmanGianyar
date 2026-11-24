<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private const OLD_EMAIL = 'piket@sims.sch.id';
    private const NEW_EMAIL = 'piket@sman1-gianyar.sch.id';

    public function up(): void
    {
        $existingTarget = User::where('email', self::NEW_EMAIL)->first();
        if ($existingTarget && $existingTarget->role !== 'piket') {
            throw new RuntimeException(sprintf(
                'Email %s sudah digunakan oleh akun lain (id=%d, role=%s).',
                self::NEW_EMAIL,
                $existingTarget->id,
                $existingTarget->role
            ));
        }

        $oldPiket = User::where('email', self::OLD_EMAIL)->first();
        if ($oldPiket && !$existingTarget) {
            $oldPiket->forceFill([
                'email'                => self::NEW_EMAIL,
                'nip'                  => 'piket',
                'role'                 => 'piket',
                'must_change_password' => false,
            ])->save();

            return;
        }

        User::firstOrCreate(
            ['email' => self::NEW_EMAIL],
            [
                'name'                 => 'Meja Piket SMAN 1 Gianyar',
                'nip'                  => 'piket',
                'password'             => Hash::make('Dosman123'),
                'role'                 => 'piket',
                'must_change_password' => false,
            ]
        );
    }

    public function down(): void
    {
        $newPiket = User::where('email', self::NEW_EMAIL)
            ->where('role', 'piket')
            ->first();

        if ($newPiket && !User::where('email', self::OLD_EMAIL)->exists()) {
            $newPiket->forceFill([
                'email' => self::OLD_EMAIL,
            ])->save();
        }
    }
};
