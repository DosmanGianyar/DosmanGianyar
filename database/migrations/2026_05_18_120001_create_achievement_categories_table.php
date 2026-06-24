<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('achievement_categories')->insert([
            ['name' => 'Akademik / Ilmiah',        'description' => 'Olimpiade, lomba sains, karya ilmiah'],
            ['name' => 'Olahraga',                  'description' => 'Pertandingan, turnamen, kejuaraan olahraga'],
            ['name' => 'Seni & Budaya',             'description' => 'Lomba seni, festival budaya, pertunjukan'],
            ['name' => 'Teknologi & Inovasi',       'description' => 'Hackathon, lomba robotik, inovasi digital'],
            ['name' => 'Kepemimpinan & Organisasi', 'description' => 'OSIS, pramuka, organisasi eksternal'],
            ['name' => 'Keagamaan',                 'description' => 'MTQ, lomba keagamaan, kegiatan rohani'],
            ['name' => 'Lainnya',                   'description' => 'Prestasi di luar kategori di atas'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_categories');
    }
};
