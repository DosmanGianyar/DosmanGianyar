<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Compress and store an uploaded image.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  Storage directory (e.g. 'conduct')
     * @param  int           $maxWidth   Max width in pixels
     * @param  int           $quality    JPEG quality 1–100
     * @param  string        $disk
     * @return string  Stored path relative to disk root
     */
    public static function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1280,
        int $quality  = 80,
        string $disk  = 'public'
    ): string {
        $filename = Str::uuid() . '.jpg';
        $path     = $directory . '/' . $filename;

        $image = Image::read($file)
            ->scaleDown(width: $maxWidth)
            ->toJpeg($quality);

        Storage::disk($disk)->put($path, $image);

        return $path;
    }

    /**
     * Compress and store a selfie (smaller max size, higher compression).
     */
    public static function storeSelfie(UploadedFile $file, string $directory = 'attendance'): string
    {
        return self::store($file, $directory, maxWidth: 800, quality: 75);
    }

    /**
     * Compress and store a profile photo (square crop).
     */
    public static function storeAvatar(UploadedFile $file, string $directory = 'photos'): string
    {
        $filename = Str::uuid() . '.jpg';
        $path     = $directory . '/' . $filename;

        $image = Image::read($file)
            ->cover(400, 400)
            ->toJpeg(85);

        Storage::disk('public')->put($path, $image);

        return $path;
    }
}
