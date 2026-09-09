<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        User::firstOrCreate(
            ['email' => 'resetpassword@sims.sch.id'],
            [
                'name'                  => 'Admin Reset Password',
                'nip'                   => 'admin_reset',
                'password'              => Hash::make('Dosman123'),
                'role'                  => 'admin_reset_password',
                'must_change_password' => false,
            ]
        );
    }

    public function down(): void
    {
        User::where('email', 'resetpassword@sims.sch.id')->delete();
    }
};
