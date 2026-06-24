<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SchoolEvent extends Model
{
    protected $fillable = [
        'title', 'description', 'event_date', 'end_date',
        'location', 'cover_photo', 'type', 'is_published', 'created_by', 'gallery_id',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'end_date'     => 'date',
        'is_published' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function coverPhotoUrl(): ?string
    {
        return $this->cover_photo ? Storage::disk('public')->url($this->cover_photo) : null;
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'kegiatan' => 'Kegiatan',
            'lomba'    => 'Lomba',
            'rapat'    => 'Rapat',
            'upacara'  => 'Upacara',
            'wisuda'   => 'Wisuda',
            default    => 'Lainnya',
        };
    }

    public function typeBadgeClass(): string
    {
        return match($this->type) {
            'lomba'   => 'bg-yellow-100 text-yellow-700',
            'rapat'   => 'bg-gray-100 text-gray-700',
            'upacara' => 'bg-red-100 text-red-700',
            'wisuda'  => 'bg-purple-100 text-purple-700',
            default   => 'bg-blue-100 text-blue-700',
        };
    }

    public function isUpcoming(): bool
    {
        return $this->event_date->isFuture() || $this->event_date->isToday();
    }
}
