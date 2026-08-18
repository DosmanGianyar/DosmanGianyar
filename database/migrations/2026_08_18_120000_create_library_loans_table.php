<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('manual_student_name')->nullable();
            $table->string('manual_class_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('book_title');
            $table->string('book_code')->nullable();
            $table->date('borrowed_at');
            $table->date('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['borrowed', 'returned', 'overdue'])->default('borrowed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_loans');
    }
};
