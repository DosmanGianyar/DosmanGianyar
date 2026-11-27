<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\GalleryReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class GalleryMediaService
{
    public const MAX_DIMENSION = 1600;
    public const JPEG_QUALITY  = 80;
    public const THUMB_SIZE    = 400;
    public const THUMB_QUALITY = 75;

    /**
     * Konversi foto ke JPEG standar lihat (maks 1600px, kualitas 80%), buat thumbnail,
     * lalu simpan ke folder kegiatan di Synology Data Sekolah.
     */
    public function storePhoto(
        Gallery $gallery,
        UploadedFile $file,
        User $uploader,
        ?string $caption = null,
    ): GalleryPhoto {
        $folder = $gallery->ensureFolderSlug();
        $disk   = Storage::disk('synology');

        $image = Image::read($file->getRealPath())->orient();
        $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);

        $width  = $image->width();
        $height = $image->height();

        $encodedJpg = (string) $image->toJpeg(self::JPEG_QUALITY);

        // Buat thumbnail ringan untuk tampilan grid mobile & Filament
        $thumbImage = Image::read($file->getRealPath())->orient();
        $thumbImage->scaleDown(width: self::THUMB_SIZE, height: self::THUMB_SIZE);
        $encodedThumb = (string) $thumbImage->toJpeg(self::THUMB_QUALITY);

        $uploaderSlug = Str::slug(Str::limit($uploader->name, 24, ''), '_') ?: 'user_' . $uploader->id;
        $filename     = now()->format('Ymd_His') . '_' . $uploaderSlug . '_' . strtolower(Str::random(4)) . '.jpg';

        $photoPath = "01_Dokumentasi_Kegiatan/{$folder}/Foto/{$filename}";
        $thumbPath = "01_Dokumentasi_Kegiatan/{$folder}/Foto/_thumbs/{$filename}";

        $disk->put($photoPath, $encodedJpg);
        $disk->put($thumbPath, $encodedThumb);

        $nextOrder = ((int) $gallery->photos()->max('sort_order')) + 1;

        return GalleryPhoto::create([
            'gallery_id'   => $gallery->id,
            'uploaded_by'  => $uploader->id,
            'photo'        => $photoPath,
            'thumbnail'    => $thumbPath,
            'storage_disk' => 'synology',
            'file_size'    => strlen($encodedJpg),
            'width'        => $width,
            'height'       => $height,
            'caption'      => $caption ? mb_substr(trim($caption), 0, 200) : null,
            'sort_order'   => $nextOrder,
        ]);
    }

    /**
     * Simpan dokumen laporan kegiatan (PDF/Office) ke subfolder Laporan/ di Synology.
     */
    public function storeReport(
        Gallery $gallery,
        UploadedFile $file,
        User $uploader,
        ?string $title = null,
    ): GalleryReport {
        $folder = $gallery->ensureFolderSlug();
        $disk   = Storage::disk('synology');

        $originalName = $file->getClientOriginalName();
        $ext          = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $baseName     = Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '_') ?: 'laporan';
        $storedName   = now()->format('Ymd_His') . '_' . Str::limit($baseName, 60, '') . '.' . $ext;
        $reportDir    = "01_Dokumentasi_Kegiatan/{$folder}/Laporan";

        $file->storeAs($reportDir, $storedName, 'synology');
        $filePath = "{$reportDir}/{$storedName}";

        return GalleryReport::create([
            'gallery_id'    => $gallery->id,
            'title'         => mb_substr(trim($title ?: pathinfo($originalName, PATHINFO_FILENAME)), 0, 180),
            'file_path'     => $filePath,
            'original_name' => mb_substr($originalName, 0, 255),
            'mime_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'storage_disk'  => 'synology',
            'uploaded_by'   => $uploader->id,
        ]);
    }

    /**
     * Hapus file foto utama dan thumbnail dari disk penyimpanan.
     */
    public static function deletePhotoFiles(GalleryPhoto $photo): void
    {
        $diskName = $photo->storage_disk ?: 'public';
        $disk     = Storage::disk($diskName);

        if ($photo->photo && $disk->exists($photo->photo)) {
            $disk->delete($photo->photo);
        }
        if ($photo->thumbnail && $disk->exists($photo->thumbnail)) {
            $disk->delete($photo->thumbnail);
        }
    }

    /**
     * Hapus file laporan dari disk penyimpanan.
     */
    public static function deleteReportFile(GalleryReport $report): void
    {
        $diskName = $report->storage_disk ?: 'synology';
        $disk     = Storage::disk($diskName);

        if ($report->file_path && $disk->exists($report->file_path)) {
            $disk->delete($report->file_path);
        }
    }
}
