<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageService;
use App\Services\OrangtuaSyncService;
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

        // Siswa/pengelola: hanya boleh login pakai NISN. Guru: NIP atau email.
        $user = str_contains($loginInput, '@')
            ? User::where('email', $loginInput)->first()
            : $this->findByUsername($loginInput);

        // Siswa/pengelola tidak boleh login pakai email — hanya NISN.
        if ($user && $user->isSiswa() && str_contains($loginInput, '@')) {
            $user = null;
        }

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'NISN (siswa) / NIP atau Email (guru) / No. HP (orangtua) / password salah.',
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

    /** Ajukan permintaan reset password (diproses manual oleh admin). */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'birth_date' => 'required|date',
        ]);

        $identifier = trim($request->input('identifier'));
        $birthDate  = trim($request->input('birth_date'));
        $normalizedPhone = OrangtuaSyncService::normalizePhone($identifier);

        $user = User::where('nisn', $identifier)
            ->orWhere('nip', $identifier)
            ->when($normalizedPhone, fn ($q) => $q->orWhere(
                fn ($q2) => $q2->where('role', 'orangtua')->where('phone', $normalizedPhone)
            ))
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'NISN/NIP/No. HP tidak ditemukan. Periksa kembali nomor yang Anda masukkan.',
                'code'    => 'NOT_FOUND',
            ], 404);
        }

        // Verifikasi Tanggal Lahir jika tersimpan di database
        if ($user->birth_date) {
            $userBirth = \Illuminate\Support\Carbon::parse($user->birth_date)->format('Y-m-d');
            $inputBirth = \Illuminate\Support\Carbon::parse($birthDate)->format('Y-m-d');
            if ($userBirth !== $inputBirth) {
                return response()->json([
                    'message' => 'Tanggal lahir tidak cocok dengan data NISN/NIP tersebut. Silakan periksa kembali.',
                    'code'    => 'BIRTH_DATE_MISMATCH',
                ], 422);
            }
        }

        // Auto-reset khusus akun demo Play Store
        if ($user->email === 'playstore.demo@sims.sch.id' || $user->nisn === '0000000001') {
            $user->update([
                'password'             => Hash::make('PlayReview123'),
                'must_change_password' => false,
            ]);
            $user->resetDevices();

            return response()->json([
                'message' => '⚡ Password Akun Demo Play Store telah berhasil di-reset kembali ke: PlayReview123',
            ]);
        }

        $existingPending = \App\Models\PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return response()->json([
                'message' => 'Permintaan reset password Anda sebelumnya masih menunggu diproses admin.',
            ]);
        }

        \App\Models\PasswordResetRequest::create([
            'user_id'      => $user->id,
            'identifier'   => $identifier,
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        return response()->json([
            'message' => 'Permintaan reset password berhasil dikirim. Admin akan segera memprosesnya.',
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
        // Siswa login dengan NISN, guru login dengan NIP, orangtua login dengan No. HP.
        $query = User::where('nisn', $input)->orWhere('nip', $input);

        $normalizedPhone = OrangtuaSyncService::normalizePhone($input);
        if ($normalizedPhone) {
            $query->orWhere(function ($q) use ($normalizedPhone) {
                $q->where('role', 'orangtua')->where('phone', $normalizedPhone);
            });
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

        $children = [];
        if ($user->role === 'orangtua') {
            $user->loadMissing('children.schoolClass');
            $children = $user->children->map(fn (User $c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'class_name' => $c->schoolClass?->name,
                'photo_url'  => $c->photo_url,
            ])->values()->all();
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
            'must_change_password' => (bool) $user->must_change_password,
            'is_bk'        => $user->role === 'guru' ? $user->isBk() : false,
            'can_edit_profile'   => $user->isSiswa() ? \App\Models\AppSetting::canStudentEditProfile() : true,
            'phone'        => $user->phone,
            'address'      => $user->address,
            'birth_date'   => $user->birth_date?->toDateString(),
            'gender'       => $user->gender,
            'parent_name'  => $user->parent_name,
            'parent_phone' => $user->parent_phone,
            'angkatan'     => $user->angkatan,
            'hobbies'      => $user->hobbies,
            'aspirations'  => $user->aspirations,
            'rt_rw'        => $user->rt_rw,
            'kelurahan'    => $user->kelurahan,
            'kecamatan'    => $user->kecamatan,
            'kabupaten'    => $user->kabupaten,
            'residence_status'   => $user->residence_status,
            'transportation'     => $user->transportation,
            'distance_km'        => $user->distance_km,
            'travel_time_minutes' => $user->travel_time_minutes,
            'father_name'        => $user->father_name,
            'father_phone'       => $user->father_phone,
            'father_job'         => $user->father_job,
            'mother_name'        => $user->mother_name,
            'mother_phone'       => $user->mother_phone,
            'mother_job'         => $user->mother_job,
            'guardian_name'      => $user->guardian_name,
            'guardian_phone'     => $user->guardian_phone,
            'guardian_job'       => $user->guardian_job,
            'emergency_contact_name'     => $user->emergency_contact_name,
            'emergency_contact_phone'    => $user->emergency_contact_phone,
            'emergency_contact_relation' => $user->emergency_contact_relation,
            'blood_type'         => $user->blood_type,
            'medical_history'    => $user->medical_history,
            'height_cm'          => $user->height_cm,
            'weight_kg'          => $user->weight_kg,
            'children'     => $children,
        ];
    }

    /** Update profil. */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isSiswa() && ! \App\Models\AppSetting::canStudentEditProfile()) {
            return response()->json([
                'message' => 'Pengisian & pembaruan data profil siswa sedang dikunci oleh pihak sekolah.',
            ], 403);
        }

        $validated = $request->validate([
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:255',
            'hobbies'     => 'nullable|string|max:255',
            'aspirations' => 'nullable|string|max:255',
            'rt_rw'       => 'nullable|string|max:50',
            'kelurahan'   => 'nullable|string|max:100',
            'kecamatan'   => 'nullable|string|max:100',
            'kabupaten'   => 'nullable|string|max:100',
            'residence_status' => 'nullable|string|max:50',
            'transportation'   => 'nullable|string|max:50',
            'distance_km'      => 'nullable|numeric|min:0|max:500',
            'travel_time_minutes' => 'nullable|integer|min:0|max:1440',
            'father_name'      => 'nullable|string|max:255',
            'father_phone'     => 'nullable|string|max:50',
            'father_job'       => 'nullable|string|max:100',
            'mother_name'      => 'nullable|string|max:255',
            'mother_phone'     => 'nullable|string|max:50',
            'mother_job'       => 'nullable|string|max:100',
            'guardian_name'    => 'nullable|string|max:255',
            'guardian_phone'   => 'nullable|string|max:50',
            'guardian_job'     => 'nullable|string|max:100',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:50',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'blood_type'       => 'nullable|string|max:10',
            'medical_history'  => 'nullable|string',
            'height_cm'        => 'nullable|integer|min:30|max:250',
            'weight_kg'        => 'nullable|integer|min:10|max:300',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $this->userPayload($user->fresh()),
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

        $user->update([
            'password'              => Hash::make($request->input('password')),
            'must_change_password'  => false,
        ]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }

    /** Cek versi aplikasi mobile & status force update. */
    public function appVersion(): JsonResponse
    {
        return response()->json([
            'latest_version'       => config('sims.latest_mobile_version', '1.3.2'),
            'min_required_version' => config('sims.min_mobile_version', '1.3.0'),
            'force_update'         => (bool) config('sims.force_mobile_update', false),
            'update_url'           => config('sims.play_store_url', 'https://play.google.com/store/apps/details?id=com.sman1gianyar.sims_mobile'),
            'title'                => 'Pembaruan Aplikasi Diperlukan',
            'message'              => 'Versi baru aplikasi SIMS SMAN 1 Gianyar telah tersedia. Silakan perbarui aplikasi Anda untuk melanjutkan.',
        ]);
    }
}
