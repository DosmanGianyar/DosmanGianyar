<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->string('book_code')->unique();
            $table->string('isbn')->nullable()->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('publish_year')->nullable();
            $table->string('category')->default('Umum')->index();
            $table->integer('total_stock')->default(1);
            $table->integer('borrowed_count')->default(0);
            $table->string('shelf_location')->nullable();
            $table->string('cover_image'); // Filepath sampul buku (wajib)
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
