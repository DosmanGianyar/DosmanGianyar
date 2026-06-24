<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code')->unique();
            $table->string('name');
            $table->enum('category', ['furniture', 'elektronik', 'olahraga', 'lab', 'perpustakaan', 'lain']);
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->year('purchase_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
