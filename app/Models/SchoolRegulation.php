<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SchoolRegulation extends Model
{
    protected $fillable = [
        'category',
        'badge_text',
        'title',
        'content',
        'image',
        'images',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
            'images'     => 'array',
        ];
    }

    protected $appends = ['image_url', 'image_urls'];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return Storage::disk('public')->url($this->image);
    }

    public function getImageUrlsAttribute(): array
    {
        $urls = [];
        if (! empty($this->images) && is_array($this->images)) {
            foreach ($this->images as $img) {
                if (! $img) continue;
                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                    $urls[] = $img;
                } elseif (str_starts_with($img, 'images/')) {
                    $urls[] = asset($img);
                } else {
                    $urls[] = Storage::disk('public')->url($img);
                }
            }
        }
        return $urls;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function categoryLabel(): string
    {
        return match($this->category) {
            'kehadiran'       => 'Kehadiran & Keterlambatan',
            'kbm'             => 'Kegiatan Belajar Mengajar & Waktu Belajar',
            'kerapian'        => 'Tata Tertib Kerapian Peserta Didik',
            'berpakaian'      => 'Tata Cara Berpakaian & Seragam',
            'ekstrakurikuler'  => 'Kegiatan Ekstrakurikuler',
            'hak_kewajiban'   => 'Hak & Kewajiban Peserta Didik',
            'perilaku'        => 'Tata Perilaku & Upacara Bendera',
            'larangan'        => 'Larangan Peserta Didik',
            default           => ucfirst($this->category),
        };
    }

    public static function categories(): array
    {
        return [
            'kehadiran'       => 'Kehadiran & Keterlambatan',
            'kbm'             => 'Kegiatan Belajar Mengajar & Waktu Belajar',
            'kerapian'        => 'Tata Tertib Kerapian Peserta Didik',
            'berpakaian'      => 'Tata Cara Berpakaian & Seragam',
            'ekstrakurikuler'  => 'Kegiatan Ekstrakurikuler',
            'hak_kewajiban'   => 'Hak & Kewajiban Peserta Didik',
            'perilaku'        => 'Tata Perilaku & Upacara Bendera',
            'larangan'        => 'Larangan Peserta Didik',
        ];
    }
}
