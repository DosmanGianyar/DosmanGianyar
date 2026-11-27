<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GalleryReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleryMediaController extends Controller
{
    public function photo(Request $request, GalleryPhoto $photo): StreamedResponse
    {
        $diskName = $photo->storage_disk ?: 'public';
        $disk     = Storage::disk($diskName);

        $useThumb = $request->boolean('thumb');
        $path     = ($useThumb && $photo->thumbnail && $disk->exists($photo->thumbnail))
            ? $photo->thumbnail
            : $photo->photo;

        abort_if(! $path || ! $disk->exists($path), 404);

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return response()->stream(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function report(Request $request, GalleryReport $report): StreamedResponse
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isGuruOrPegawai() && ! $user->isPiket() && ! str_starts_with((string) $user->role, 'admin')), 403);

        $diskName = $report->storage_disk ?: 'synology';
        $disk     = Storage::disk($diskName);

        abort_if(! $report->file_path || ! $disk->exists($report->file_path), 404);

        return $disk->download($report->file_path, $report->original_name ?: basename($report->file_path));
    }
}
