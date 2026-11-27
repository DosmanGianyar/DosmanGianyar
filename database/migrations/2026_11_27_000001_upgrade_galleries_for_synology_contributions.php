<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('folder_slug', 180)->nullable()->after('event_date');
            $table->boolean('allow_upload')->default(true)->after('is_published');
            $table->timestamp('upload_closed_at')->nullable()->after('allow_upload');
            $table->unsignedSmallInteger('max_photos_per_user')->default(10)->after('upload_closed_at');
        });

        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->foreignId('uploaded_by')->nullable()->after('gallery_id')->constrained('users')->nullOnDelete();
            $table->string('storage_disk', 32)->default('public')->after('photo');
            $table->string('thumbnail', 255)->nullable()->after('storage_disk');
            $table->unsignedInteger('file_size')->nullable()->after('thumbnail');
            $table->unsignedSmallInteger('width')->nullable()->after('file_size');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
        });

        Schema::create('gallery_allowed_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['gallery_id', 'user_id']);
        });

        Schema::create('gallery_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('file_path', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('storage_disk', 32)->default('synology');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_reports');
        Schema::dropIfExists('gallery_allowed_students');

        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['uploaded_by', 'storage_disk', 'thumbnail', 'file_size', 'width', 'height']);
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['folder_slug', 'allow_upload', 'upload_closed_at', 'max_photos_per_user']);
        });
    }
};
