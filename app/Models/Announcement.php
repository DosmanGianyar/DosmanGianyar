<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'image', 'target', 'target_class_ids', 'is_pinned',
        'is_active', 'show_as_modal', 'published_at', 'expires_at', 'author_id',
    ];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'is_active'        => 'boolean',
        'show_as_modal'    => 'boolean',
        'published_at'     => 'datetime',
        'expires_at'       => 'datetime',
        'target_class_ids' => 'array',
    ];

    protected $appends = [
        'image_url',
    ];

    protected static function booted(): void
    {
        static::saving(function (Announcement $announcement) {
            if (is_string($announcement->attributes['body'] ?? null)) {
                $announcement->attributes['body'] = static::normalizeBodyHtml($announcement->attributes['body']);
            }
        });
    }

    public function getBodyAttribute(?string $value): string
    {
        return static::normalizeBodyHtml($value);
    }

    /**
     * Bersihkan HTML dari RichEditor agar tampil konsisten di Web maupun Mobile App (RichContentView).
     * - Membuka pembungkus <blockquote> jika membungkus paragraf jamak atau daftar (<ol>/<ul>),
     *   yang sering terjadi saat copy-paste teks ke RichEditor.
     * - Merapikan <li><p>...</p></li> menjadi <li>...</li> agar nomor/bullet list di mobile rapi.
     */
    public static function normalizeBodyHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $cleaned = trim($html);

        // Jika terdapat <blockquote> yang membungkus list (<ol>/<ul>) atau beberapa paragraf (<p>),
        // lepaskan tag <blockquote> agar paragraf dan penomoran list dirender per blok di mobile app
        $cleaned = preg_replace_callback(
            '/<blockquote[^>]*>([\s\S]*?)<\/blockquote>/i',
            function (array $matches): string {
                $inner = trim($matches[1]);
                $pCount = preg_match_all('/<p[\s>]/i', $inner);
                $hasListOrHeading = (bool) preg_match('/<(ol|ul|h[1-6]|table)[\s>]/i', $inner);

                if ($hasListOrHeading || $pCount > 1) {
                    return $inner;
                }

                // Jika hanya 1 paragraf kutipan pendek, tetap ubah menjadi paragraf biasa bila seluruh body hanya blockquote itu
                return $inner;
            },
            $cleaned
        ) ?? $cleaned;

        // Bersihkan <p> di dalam <li> (contoh: <li><p>Teks</p></li> -> <li>Teks</li>)
        $cleaned = preg_replace('/<li([^>]*)>\s*<p[^>]*>([\s\S]*?)<\/p>\s*<\/li>/i', '<li$1>$2</li>', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image || trim($this->image) === '') {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        $cleanPath = ltrim($this->image, '/');

        // Auto-heal: Jika file tersimpan di disk private/local, salin otomatis ke disk public
        try {
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if (! $publicDisk->exists($cleanPath)) {
                $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
                if ($localDisk->exists($cleanPath)) {
                    $publicDisk->put($cleanPath, $localDisk->get($cleanPath));
                } elseif (file_exists(storage_path('app/private/' . $cleanPath))) {
                    $publicDisk->put($cleanPath, file_get_contents(storage_path('app/private/' . $cleanPath)));
                } elseif (file_exists(storage_path('app/' . $cleanPath))) {
                    $publicDisk->put($cleanPath, file_get_contents(storage_path('app/' . $cleanPath)));
                }
            }
        } catch (\Throwable $e) {
            // silent fallback
        }

        return asset('storage/' . $cleanPath);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeActiveModal(Builder $query, string $role = 'siswa', ?int $classId = null): Builder
    {
        return $query->published()
            ->where('show_as_modal', true)
            ->forRole($role, $classId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at');
    }

    public function scopeForRole(Builder $query, string $role, ?int $classId = null): Builder
    {
        return $query->where(function ($q) use ($role, $classId) {
            $q->where('target', 'all')->orWhere('target', $role);
        })->where(function ($q) use ($classId) {
            $q->whereNull('target_class_ids')
              ->orWhereJsonLength('target_class_ids', 0);
            if ($classId) {
                $q->orWhereJsonContains('target_class_ids', $classId);
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->is_active
            && $this->published_at !== null
            && $this->published_at->lte(now())
            && ($this->expires_at === null || $this->expires_at->gte(now()));
    }
}
