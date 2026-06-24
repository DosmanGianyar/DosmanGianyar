<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('siswa')->after('email'); // admin|guru|siswa|siswa_pengelola
            $table->string('photo')->nullable()->after('role');
            $table->string('phone')->nullable()->after('photo');

            // Siswa
            $table->string('nis', 20)->nullable()->unique()->after('phone');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete()->after('nis');
            $table->string('parent_name')->nullable()->after('class_id');
            $table->string('parent_phone', 20)->nullable()->after('parent_name');
            $table->date('birth_date')->nullable()->after('parent_phone');
            $table->string('address')->nullable()->after('birth_date');

            // Guru
            $table->string('nip', 30)->nullable()->unique()->after('address');
            $table->string('subject')->nullable()->after('nip'); // Mata pelajaran
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'photo', 'phone',
                'nis', 'class_id', 'parent_name', 'parent_phone', 'birth_date', 'address',
                'nip', 'subject',
            ]);
        });
    }
};
