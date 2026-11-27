<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\User;
use App\Services\GalleryMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $galleries = Gallery::with(['photos' => fn ($q) => $q->orderBy('sort_order')->orderByDesc('id')->limit(1)])
            ->withCount(['photos', 'reports'])
            ->where(function ($q) use ($user) {
                $q->where('is_published', true);
                if ($user->isGuruOrPegawai() || $user->isPiket() || $this->isAdminUser($user)) {
                    $q->orWhere('allow_upload', true);
                } elseif ($user->isSiswa()) {
                    $q->orWhereHas('allowedStudents', fn ($sq) => $sq->where('users.id', $user->id));
                }
            })
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        $items = $galleries->map(fn (Gallery $g) => $this->serializeGallery($g, $user))->values();

        return response()->json([
            'status' => 'success',
            'data'   => $items,
        ]);
    }

    public function show(Request $request, Gallery $gallery): JsonResponse
    {
        /** @var User $user */
        $user      = $request->user();
        $canUpload = $gallery->canUserUpload($user);

        abort_if(! $gallery->is_published && ! $canUpload && ! $this->isAdminUser($user), 404);

        $gallery->load([
            'photos' => fn ($q) => $q->with('uploader:id,name,role')->orderByDesc('id'),
            'reports.uploader:id,name',
        ])->loadCount(['photos', 'reports']);

        $isStaffOrAdmin = $this->isAdminUser($user) || $user->isGuruOrPegawai() || $user->isPiket();

        $photos = $gallery->photos->map(fn (GalleryPhoto $p) => $this->serializePhoto($p, $user))->values();
        $reports = $isStaffOrAdmin
            ? $gallery->reports->map(fn ($r) => [
                'id'            => $r->id,
                'title'         => $r->title,
                'original_name' => $r->original_name,
                'file_size'     => $r->file_size,
                'uploader_name' => $r->uploader?->name ?? 'Admin',
                'download_url'  => $r->downloadUrl(),
                'created_at'    => $r->created_at?->toIso8601String(),
            ])->values()
            : [];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'gallery' => $this->serializeGallery($gallery, $user),
                'photos'  => $photos,
                'reports' => $reports,
            ],
        ]);
    }

    public function storePhotos(Request $request, Gallery $gallery, GalleryMediaService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $gallery->isUploadOpen()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Upload foto untuk kegiatan ini sudah ditutup.',
            ], 403);
        }

        if (! $gallery->canUserUpload($user)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Anda belum dipilih oleh Admin untuk mengunggah foto pada kegiatan ini.',
            ], 403);
        }

        // Normalisasi: dukung field 'photos[]' maupun single 'photo'
        $files = $request->file('photos');
        if (! is_array($files)) {
            $single = $request->file('photo');
            $files  = $single ? [$single] : [];
        }

        if (empty($files)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pilih minimal 1 foto untuk diunggah.',
            ], 422);
        }

        $maxPerUser = $gallery->effectiveMaxPhotosPerUser();
        $usedBefore = $gallery->uploadedCountBy($user);
        $remaining  = max(0, $maxPerUser - $usedBefore);

        if (count($files) > $remaining) {
            return response()->json([
                'status'  => 'error',
                'message' => $remaining === 0
                    ? "Batas upload maksimal {$maxPerUser} foto per akun untuk kegiatan ini sudah tercapai."
                    : "Sisa kuota upload Anda untuk kegiatan ini hanya {$remaining} foto lagi (maksimal {$maxPerUser} foto).",
                'data' => [
                    'max_photos_per_user' => $maxPerUser,
                    'my_uploaded_count'   => $usedBefore,
                    'remaining_quota'     => $remaining,
                ],
            ], 422);
        }

        $request->merge(['_has_photos' => true]);
        if ($request->hasFile('photos')) {
            $request->validate([
                'photos'   => ['required', 'array', 'max:' . $maxPerUser],
                'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:15360'],
                'caption'  => ['nullable', 'string', 'max:200'],
            ]);
        } else {
            $request->validate([
                'photo'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:15360'],
                'caption' => ['nullable', 'string', 'max:200'],
            ]);
        }

        $caption = $request->input('caption');
        $created = [];
        foreach ($files as $file) {
            $photo     = $service->storePhoto($gallery, $file, $user, $caption);
            $photo->load('uploader:id,name,role');
            $created[] = $this->serializePhoto($photo, $user);
        }

        $usedAfter      = $gallery->uploadedCountBy($user);
        $remainingAfter = max(0, $maxPerUser - $usedAfter);

        return response()->json([
            'status'  => 'success',
            'message' => count($created) . ' foto berhasil diunggah dan dikompres otomatis.',
            'data'    => [
                'photos'              => $created,
                'my_uploaded_count'   => $usedAfter,
                'max_photos_per_user' => $maxPerUser,
                'remaining_quota'     => $remainingAfter,
            ],
        ], 201);
    }

    public function destroyPhoto(Request $request, Gallery $gallery, GalleryPhoto $photo): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($photo->gallery_id !== $gallery->id) {
            abort(404);
        }

        $isOwner = $photo->uploaded_by !== null && (int) $photo->uploaded_by === (int) $user->id;
        if (! $isOwner && ! $this->isAdminUser($user)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak berhak menghapus foto ini.',
            ], 403);
        }

        $photo->delete();

        $maxPerUser = $gallery->effectiveMaxPhotosPerUser();
        $usedAfter  = $gallery->uploadedCountBy($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Foto berhasil dihapus.',
            'data'    => [
                'my_uploaded_count'   => $usedAfter,
                'max_photos_per_user' => $maxPerUser,
                'remaining_quota'     => max(0, $maxPerUser - $usedAfter),
            ],
        ]);
    }

    private function isAdminUser(User $user): bool
    {
        return in_array($user->role, [
            'admin',
            'admin_humas',
            'admin_kesiswaan',
            'admin_kurikulum',
            'admin_sarpras',
            'admin_perpustakaan',
            'admin_prestasi',
        ], true);
    }

    private function serializeGallery(Gallery $gallery, User $user): array
    {
        $maxPerUser = $gallery->effectiveMaxPhotosPerUser();
        $used       = $gallery->uploadedCountBy($user);

        return [
            'id'                  => $gallery->id,
            'title'               => $gallery->title,
            'description'         => $gallery->description,
            'event_date'          => $gallery->event_date?->format('Y-m-d'),
            'event_date_label'    => $gallery->event_date?->translatedFormat('d M Y'),
            'cover_url'           => $gallery->coverPhotoUrl(),
            'is_published'        => (bool) $gallery->is_published,
            'is_upload_open'      => $gallery->isUploadOpen(),
            'upload_closed_at'    => $gallery->upload_closed_at?->toIso8601String(),
            'can_upload'          => $gallery->canUserUpload($user),
            'my_uploaded_count'   => $used,
            'max_photos_per_user' => $maxPerUser,
            'remaining_quota'     => max(0, $maxPerUser - $used),
            'photos_count'        => (int) ($gallery->photos_count ?? $gallery->photos()->count()),
            'reports_count'       => (int) ($gallery->reports_count ?? $gallery->reports()->count()),
        ];
    }

    private function serializePhoto(GalleryPhoto $photo, User $user): array
    {
        $isOwner = $photo->uploaded_by !== null && (int) $photo->uploaded_by === (int) $user->id;

        return [
            'id'            => $photo->id,
            'url'           => $photo->url(),
            'thumbnail_url' => $photo->thumbnailUrl(),
            'caption'       => $photo->caption,
            'uploader_name' => $photo->uploader?->name ?? 'Admin',
            'uploader_role' => $photo->uploader?->role ?? 'admin',
            'is_mine'       => $isOwner,
            'can_delete'    => $isOwner || $this->isAdminUser($user),
            'file_size_kb'  => $photo->file_size ? (int) round($photo->file_size / 1024) : null,
            'width'         => $photo->width,
            'height'        => $photo->height,
            'created_at'    => $photo->created_at?->toIso8601String(),
        ];
    }
}
