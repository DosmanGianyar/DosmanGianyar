<?php

namespace App\Models;

use App\Services\GalleryMediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryPhoto extends Model
{
    protected $fillable = [
        'gallery_id',
        'uploaded_by',
        'photo',
        'storage_disk',
        'thumbnail',
        'file_size',
        'width',
        'height',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'width'      => 'integer',
        'height'     => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (GalleryPhoto $photo) {
            GalleryMediaService::deletePhotoFiles($photo);
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

    public function url(): string
    {
        if (($this->storage_disk ?: 'public') === 'synology') {
            return route('media.gallery-photo', $this);
        }

        return Storage::disk('public')->url($this->photo);
    }

    public function thumbnailUrl(): string
    {
        if (($this->storage_disk ?: 'public') === 'synology') {
            return route('media.gallery-photo', ['photo' => $this, 'thumb' => 1]);
        }

        return Storage::disk('public')->url($this->photo);
    }
}
