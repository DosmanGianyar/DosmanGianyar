<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SchoolRegulation extends Model
{
    protected $fillable = ['category', 'title', 'content', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
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
