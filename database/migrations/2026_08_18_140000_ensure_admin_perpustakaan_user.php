<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        User::firstOrCreate(
            ['email' => 'perpustakaan@sims.sch.id'],
            [
                'name'                  => 'Admin Perpustakaan',
                'password'              => Hash::make('Dosman123'),
                'role'                  => 'admin_perpustakaan',
                'must_change_password' => false,
            ]
        );
    }

    public function down(): void
    {
        User::where('email', 'perpustakaan@sims.sch.id')->delete();
    }
};
