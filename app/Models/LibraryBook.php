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

    public function recalculateBorrowedCount(): void
    {
        $count = $this->loans()
            ->whereIn('status', ['borrowed', 'overdue'])
            ->count();

        $this->update(['borrowed_count' => $count]);
    }
}
