<?php

namespace App\Models;

use App\Services\GalleryMediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryReport extends Model
{
    protected $fillable = [
        'gallery_id',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'storage_disk',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (GalleryReport $report) {
            GalleryMediaService::deleteReportFile($report);
        });
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function downloadUrl(): string
    {
        return route('media.gallery-report', $this);
    }
}
