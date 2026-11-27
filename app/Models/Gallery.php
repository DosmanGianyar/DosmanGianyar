<?php

namespace App\Models;

use App\Services\GalleryMediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Gallery extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'folder_slug',
        'cover_photo',
        'is_published',
        'allow_upload',
        'upload_closed_at',
        'max_photos_per_user',
        'created_by',
    ];

    protected $casts = [
        'event_date'          => 'date',
        'is_published'        => 'boolean',
        'allow_upload'        => 'boolean',
        'upload_closed_at'    => 'datetime',
        'max_photos_per_user' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (Gallery $gallery) {
            $gallery->ensureFolderSlug();
        });

        static::deleting(function (Gallery $gallery) {
            foreach ($gallery->photos as $photo) {
                GalleryMediaService::deletePhotoFiles($photo);
            }
            foreach ($gallery->reports as $report) {
                GalleryMediaService::deleteReportFile($report);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order')->orderByDesc('id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(GalleryReport::class)->latest();
    }

    public function allowedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gallery_allowed_students')
            ->withPivot('granted_by')
            ->withTimestamps();
    }

    public function coverPhotoUrl(): ?string
    {
        if ($this->cover_photo) {
            return Storage::disk('public')->url($this->cover_photo);
        }
        $first = $this->photos()->first();
        return $first ? $first->thumbnailUrl() : null;
    }

    /**
     * Pastikan kegiatan memiliki folder unik terstruktur YYYY/YYYY-MM-DD_judul di Synology.
     */
    public function ensureFolderSlug(): string
    {
        if (! empty($this->folder_slug)) {
            return $this->folder_slug;
        }

        $date     = $this->event_date ?? $this->created_at ?? now();
        $year     = $date->format('Y');
        $datePart = $date->format('Y-m-d');
        $slug     = Str::slug(Str::limit($this->title ?: 'kegiatan', 60, ''), '_') ?: 'kegiatan_' . $this->id;
        $folder   = "{$year}/{$datePart}_{$slug}";

        if (static::where('folder_slug', $folder)->where('id', '!=', $this->id)->exists()) {
            $folder .= "_{$this->id}";
        }

        $this->folder_slug = $folder;
        if ($this->exists) {
            $this->saveQuietly();
        }

        return $folder;
    }

    public function isUploadOpen(): bool
    {
        if (! $this->allow_upload) {
            return false;
        }

        if ($this->upload_closed_at !== null && $this->upload_closed_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Kebijakan upload foto:
     * - Semua Admin & Guru/Pegawai/Piket otomatis boleh upload selama upload terbuka.
     * - Siswa hanya boleh upload jika akunnya dipilih Admin pada kegiatan ini.
     */
    public function canUserUpload(User $user): bool
    {
        if (! $this->isUploadOpen()) {
            return false;
        }

        $adminRoles = [
            'admin',
            'admin_humas',
            'admin_kesiswaan',
            'admin_kurikulum',
            'admin_sarpras',
            'admin_perpustakaan',
            'admin_prestasi',
        ];

        if (in_array($user->role, $adminRoles, true) || $user->isGuruOrPegawai() || $user->isPiket()) {
            return true;
        }

        if ($user->isSiswa()) {
            return $this->allowedStudents()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function effectiveMaxPhotosPerUser(): int
    {
        return max(1, (int) ($this->max_photos_per_user ?: 10));
    }

    public function uploadedCountBy(User $user): int
    {
        return $this->photos()->where('uploaded_by', $user->id)->count();
    }

    public function remainingQuotaFor(User $user): int
    {
        return max(0, $this->effectiveMaxPhotosPerUser() - $this->uploadedCountBy($user));
    }
}
