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
        Schema::table('library_loans', function (Blueprint $table) {
            $table->string('book_nisb')->nullable()->after('book_code');
            $table->string('book_author')->nullable()->after('book_nisb');
        });
    }

    public function down(): void
    {
        Schema::table('library_loans', function (Blueprint $table) {
            $table->dropColumn(['book_nisb', 'book_author']);
        });
    }
};
