<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Login dari Flutter.
     * Binding device_id ke akun saat pertama kali login.
     * Selanjutnya, hanya device yang sama yang diperbolehkan.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'     => 'required|string',
            'password'  => 'required|string',
            'device_id' => 'required|string|max:255',
        ]);

        $loginInput = trim($request->input('login'));

        $user = str_contains($loginInput, '@')
            ? User::where('email', $loginInput)->first()
            : $this->findByUsername($loginInput);

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Email/NIS/NIP atau password salah.',
                'code'    => 'INVALID_CREDENTIALS',
            ], 401);
        }

        // Akses API hanya untuk siswa dan guru (admin pakai Filament web)
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Admin tidak menggunakan aplikasi mobile.',
                'code'    => 'ROLE_NOT_ALLOWED',
            ], 403);
        }

        $deviceId = $request->input('device_id');

        // Daftarkan device. Jika user_devices belum ada (migration pending), lewati.
        try {
            if (! $user->registerDevice($deviceId)) {
                $max = \App\Models\User::MAX_DEVICES;
                return response()->json([
                    'message' => "Perangkat ini belum terdaftar dan akun Anda sudah mencapai batas {$max} perangkat. "
                               . 'Hubungi admin untuk menghapus salah satu perangkat lama.',
                    'code'    => 'DEVICE_LIMIT_REACHED',
                    'limit'   => $max,
                    'current' => $user->deviceCount(),
                ], 403);
            }
        } catch (\Throwable $e) {
            // user_devices table may not exist yet — skip device check
        }

        // Satu akun = satu sesi aktif (revoke token lama)
        try { $user->tokens()->delete(); } catch (\Throwable $e) { /* skip */ }

        $token = $user->createToken(
            name:       'flutter-app',
            abilities:  ['*'],
            expiresAt:  now()->addMonths(6),
        )->plainTextToken;

        return response()->json([
            'token'       => $token,
            'token_type'  => 'Bearer',
            'expires_in'  => 6 * 30 * 24 * 60 * 60, // detik (~6 bulan)
            'user'        => $this->userPayload($user),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /** Logout — hapus token aktif saja, device binding tetap. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    /** Data user yang sedang login. */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user'        => $this->userPayload($user),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function findByUsername(string $input): ?User
    {
        $query = User::where('nis', $input)
            ->orWhere('nisn', $input)
            ->orWhere('nip', $input);

        if (ctype_digit($input) && strlen($input) < 10) {
            $padded = str_pad($input, 10, '0', STR_PAD_LEFT);
            $query->orWhere('nisn', $padded)->orWhere('nis', $padded);
        }

        return $query->first();
    }

    private function userPayload(User $user): array
    {
        $teacherSubjects = [];
        if (in_array($user->role, ['guru', 'admin'])) {
            try {
                $user->loadMissing('subjects');
                $teacherSubjects = $user->subjects
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->values()->all();
            } catch (\Throwable $e) {
                // teacher_subjects table may not exist yet — return empty array
            }
        }

        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
            'nis'          => $user->nis,
            'nisn'         => $user->nisn,
            'nip'               => $user->nip,
            'subject'           => $user->subject,
            'subjects'          => $teacherSubjects,
            'photo_url'         => $user->photo_url,
            'class_id'          => $user->class_id,
            'class_name'        => $user->schoolClass?->name,
            'homeroom_class_id'   => $user->homeroomClass?->id,
            'homeroom_class_name' => $user->homeroomClass?->name,
            'device_bound' => $user->hasDeviceLocked(),
            'is_bk'        => $user->role === 'guru' ? $user->isBk() : false,
            'phone'        => $user->phone,
            'address'      => $user->address,
            'birth_date'   => $user->birth_date?->toDateString(),
            'gender'       => $user->gender,
            'parent_name'  => $user->parent_name,
            'parent_phone' => $user->parent_phone,
        ];
    }

    /** Update profil (phone, address). */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->update($request->only('phone', 'address'));

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $this->userPayload($user),
        ]);
    }

    /** Ganti foto profil (multipart). */
    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.mimes'    => 'Format foto harus JPG, PNG, atau WebP.',
            'photo.max'      => 'Ukuran foto maksimal 5 MB.',
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = ImageService::storeAvatar($request->file('photo'), 'avatars');
        $user->update(['photo' => $path]);

        return response()->json([
            'message'   => 'Foto profil berhasil diperbarui.',
            'photo_url' => $user->fresh()->photo_url,
            'user'      => $this->userPayload($user->fresh()),
        ]);
    }

    /** Ganti password. */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['message' => 'Password saat ini salah.'], 422);
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }
}
