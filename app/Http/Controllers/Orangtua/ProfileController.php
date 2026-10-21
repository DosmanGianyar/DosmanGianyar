<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var \App\Models\User $orangtua */
        $orangtua = Auth::user();
        $orangtua->loadMissing('children.schoolClass');

        return view('orangtua.profile', [
            'orangtua' => $orangtua,
            'children' => $orangtua->children,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama lengkap orang tua wajib diisi.',
            'name.max'      => 'Nama lengkap maksimal 100 karakter.',
        ]);

        /** @var \App\Models\User $orangtua */
        $orangtua = Auth::user();

        // Catatan: Nomor HP akun orang tua dilarang diubah secara mandiri
        // karena merupakan ID login dan pengait relasi data siswa.
        $orangtua->update([
            'name'    => $validated['name'],
            'address' => $validated['address'] ?? null,
        ]);

        // Sinkronkan nama orang tua ke data siswa yang terhubung
        $orangtua->children()->update([
            'parent_name' => $orangtua->name,
        ]);

        return back()->with('success', 'Profil orang tua berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'min:8', 'confirmed', 'regex:/^\S+$/'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password baru tidak cocok.',
            'password.regex'            => 'Password tidak boleh mengandung spasi.',
        ]);

        /** @var \App\Models\User $orangtua */
        $orangtua = Auth::user();

        if (! Hash::check($request->current_password, $orangtua->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $orangtua->update([
            'password'             => $request->password,
            'must_change_password' => false,
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function updatePhoto(Request $request): mixed
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp,heic|max:5120',
        ], [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.mimes'    => 'Format foto harus JPG, PNG, atau WebP.',
            'photo.max'      => 'Ukuran foto maksimal 5 MB.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = ImageService::storeAvatar($request->file('photo'), 'avatars');
        $user->update(['photo' => $path]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Foto profil berhasil diperbarui.',
                'photo_url' => $user->fresh()->photo_url,
            ]);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
