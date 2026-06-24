<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    protected $fillable = [
        'title', 'description', 'event_date',
        'cover_photo', 'is_published', 'created_by',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'is_published' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order');
    }

    public function coverPhotoUrl(): ?string
    {
        if ($this->cover_photo) {
            return Storage::disk('public')->url($this->cover_photo);
        }
        $first = $this->photos()->first();
        return $first ? Storage::disk('public')->url($first->photo) : null;
    }
}
