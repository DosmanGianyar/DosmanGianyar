<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        User::firstOrCreate(
            ['email' => 'prestasi@sims.sch.id'],
            [
                'name'                  => 'Admin Prestasi',
                'password'              => Hash::make('Dosman123'),
                'role'                  => 'admin_prestasi',
                'must_change_password' => false,
            ]
        );
    }

    public function down(): void
    {
        User::where('email', 'prestasi@sims.sch.id')->delete();
    }
};
