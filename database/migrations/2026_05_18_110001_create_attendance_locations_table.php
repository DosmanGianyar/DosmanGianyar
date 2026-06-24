<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->unsignedSmallInteger('radius_meters')->default(50);
            $table->boolean('is_default')->default(false);
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'start_at', 'end_at']);
        });

        // Seed default school location
        DB::table('attendance_locations')->insert([
            'name'           => 'SMA Negeri 1 Gianyar',
            'latitude'       => -8.542304297173528,
            'longitude'      => 115.33400530740592,
            'radius_meters'  => 50,
            'is_default'     => true,
            'class_id'       => null,
            'start_at'       => null,
            'end_at'         => null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_locations');
    }
};
