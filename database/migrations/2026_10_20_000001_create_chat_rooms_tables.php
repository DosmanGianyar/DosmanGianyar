<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('name')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_text')->nullable();
            $table->foreignId('last_message_sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('student_id');
            $table->index('school_class_id');
            $table->index('last_message_at');
        });

        Schema::create('chat_room_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)->default('siswa'); // guru, orangtua, siswa
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_room_id', 'user_id']);
            $table->index(['user_id', 'chat_room_id']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_type', 30)->nullable(); // image, file
            $table->timestamps();

            $table->index(['chat_room_id', 'id']);
            $table->index('created_at');
        });

        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['chat_message_id', 'user_id']);
            $table->index(['user_id', 'chat_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reads');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_room_participants');
        Schema::dropIfExists('chat_rooms');
    }
};
