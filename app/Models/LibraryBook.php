<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LibraryBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_code',
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
        'category',
        'total_stock',
        'borrowed_count',
        'shelf_location',
        'cover_image',
        'description',
    ];

    protected $appends = [
        'available_stock',
        'cover_url',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class, 'book_id');
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->total_stock - (int) $this->borrowed_count);
    }

    public function getCoverUrlAttribute(): string
    {
        if ($this->cover_image) {
            if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
                return $this->cover_image;
            }
            return Storage::disk('public')->url($this->cover_image);
        }
        return asset('img/default-book-cover.png');
    }

    protected static function booted(): void
    {
        static::saved(function (LibraryBook $book) {
            if ($book->wasChanged('cover_image') && $book->cover_image) {
                $book->optimizeCoverImage();
            }
        });
    }

    public function optimizeCoverImage(): void
    {
        if (!$this->cover_image || str_starts_with($this->cover_image, 'http')) {
            return;
        }

        $fullPath = Storage::disk('public')->path($this->cover_image);

        if (!file_exists($fullPath) || !extension_loaded('gd')) {
            return;
        }

        $info = @getimagesize($fullPath);
        if (!$info) return;

        list($origWidth, $origHeight, $type) = $info;

        $targetWidth  = 400;
        $targetHeight = 533;

        if ($origWidth <= $targetWidth && filesize($fullPath) < 150000) {
            return;
        }

        $srcImg = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($fullPath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($fullPath),
            default        => null,
        };

        if (!$srcImg) return;

        $dstImg = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

        if ($type === IMAGETYPE_PNG) {
            imagepng($dstImg, $fullPath, 7);
        } elseif ($type === IMAGETYPE_WEBP) {
            imagewebp($dstImg, $fullPath, 80);
        } else {
            imagejpeg($dstImg, $fullPath, 80);
        }

        imagedestroy($srcImg);
        imagedestroy($dstImg);
    }

    public function recalculateBorrowedCount(): void
    {
        $count = $this->loans()
            ->whereIn('status', ['borrowed', 'overdue'])
            ->count();

        $this->update(['borrowed_count' => $count]);
    }
}
