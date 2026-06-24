<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryPhoto extends Model
{
    protected $fillable = ['gallery_id', 'photo', 'caption', 'sort_order'];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->photo);
    }
}
