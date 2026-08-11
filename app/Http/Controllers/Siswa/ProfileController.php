<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
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
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();
        $siswa->load('schoolClass');

        $canEditProfile = \App\Models\AppSetting::canStudentEditProfile();

        $qrContent = url('/biodata/' . $siswa->qr_token);
        $options   = new QROptions(['outputType' => 'svg']);
        $qrSvg     = (new QRCode($options))->render($qrContent);

        return view('siswa.profile', compact('siswa', 'qrSvg', 'canEditProfile'));
    }

    public function update(Request $request): RedirectResponse
    {
        if (! \App\Models\AppSetting::canStudentEditProfile()) {
            return back()->with('error', 'Pengisian & pembaruan data profil siswa sedang dikunci oleh pihak sekolah.');
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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update($validated);

        return back()->with('success', 'Data profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password baru tidak cocok dengan password baru.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah / tidak sesuai.']);
        }

        $user->update(['password' => $request->password, 'must_change_password' => false]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function updatePhoto(Request $request): RedirectResponse
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

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
