<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        if ($user->hasDeviceLocked()) {
            // Akun sudah terikat ke device lain
            if (! $user->isDeviceAllowed($deviceId)) {
                return response()->json([
                    'message' => 'Akun ini sudah terdaftar di perangkat lain. Hubungi Admin untuk reset perangkat.',
                    'code'    => 'DEVICE_LOCKED',
                ], 403);
            }
        } else {
            // Pertama kali login dari Flutter → ikat device
            $user->lockToDevice($deviceId);
        }

        // Satu akun = satu sesi aktif (revoke token lama)
        $user->tokens()->delete();

        $token = $user->createToken(
            name:       'flutter-app',
            abilities:  ['*'],
            expiresAt:  now()->addDays(30),
        )->plainTextToken;

        return response()->json([
            'token'       => $token,
            'token_type'  => 'Bearer',
            'expires_in'  => 30 * 24 * 60 * 60, // detik
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
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
            'nis'          => $user->nis,
            'nisn'         => $user->nisn,
            'nip'          => $user->nip,
            'photo_url'    => $user->photo_url,
            'class_id'     => $user->class_id,
            'class_name'   => $user->schoolClass?->name,
            'device_bound' => $user->hasDeviceLocked(),
        ];
    }
}
