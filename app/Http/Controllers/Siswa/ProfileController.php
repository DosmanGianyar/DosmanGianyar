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

        $qrContent = url('/verifikasi/kartu-pelajar/' . $siswa->qr_token);
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
            'phone'                => 'nullable|string|max:50',
            'nickname'             => 'nullable|string|max:50',
            'birth_place'          => 'nullable|string|max:100',
            'religion'             => 'nullable|string|max:50',
            'citizenship'          => 'nullable|string|max:50',
            'child_order'          => 'nullable|integer|min:1',
            'siblings_count'       => 'nullable|integer|min:0',
            'step_siblings_count'  => 'nullable|integer|min:0',
            'foster_siblings_count'=> 'nullable|integer|min:0',
            'orphan_status'        => 'nullable|string|max:50',
            'daily_language'       => 'nullable|string|max:100',
            'address'              => 'nullable|string|max:255',
            'living_with'          => 'nullable|string|max:100',
            'hobbies'              => 'nullable|string|max:255',
            'aspirations'          => 'nullable|string|max:255',
            'rt_rw'                => 'nullable|string|max:50',
            'kelurahan'            => 'nullable|string|max:100',
            'kecamatan'            => 'nullable|string|max:100',
            'kabupaten'            => 'nullable|string|max:100',
            'residence_status'     => 'nullable|string|max:50',
            'transportation'       => 'nullable|string|max:50',
            'distance_km'          => 'nullable|numeric|min:0|max:500',
            'travel_time_minutes'  => 'nullable|integer|min:0|max:1440',
            'father_name'          => 'nullable|string|max:255',
            'father_birth_place'   => 'nullable|string|max:100',
            'father_birth_date'    => 'nullable|date',
            'father_religion'      => 'nullable|string|max:50',
            'father_citizenship'   => 'nullable|string|max:50',
            'father_education'     => 'nullable|string|max:100',
            'father_phone'         => 'nullable|string|max:50',
            'father_job'           => 'nullable|string|max:100',
            'father_income'        => 'nullable|string|max:100',
            'father_address'       => 'nullable|string|max:255',
            'father_status'        => 'nullable|string|max:50',
            'mother_name'          => 'nullable|string|max:255',
            'mother_birth_place'   => 'nullable|string|max:100',
            'mother_birth_date'    => 'nullable|date',
            'mother_religion'      => 'nullable|string|max:50',
            'mother_citizenship'   => 'nullable|string|max:50',
            'mother_education'     => 'nullable|string|max:100',
            'mother_phone'         => 'nullable|string|max:50',
            'mother_job'           => 'nullable|string|max:100',
            'mother_income'        => 'nullable|string|max:100',
            'mother_address'       => 'nullable|string|max:255',
            'mother_status'        => 'nullable|string|max:50',
            'guardian_name'        => 'nullable|string|max:255',
            'guardian_birth_place' => 'nullable|string|max:100',
            'guardian_birth_date'  => 'nullable|date',
            'guardian_religion'    => 'nullable|string|max:50',
            'guardian_citizenship' => 'nullable|string|max:50',
            'guardian_education'   => 'nullable|string|max:100',
            'guardian_phone'       => 'nullable|string|max:50',
            'guardian_job'         => 'nullable|string|max:100',
            'guardian_income'      => 'nullable|string|max:100',
            'guardian_address'     => 'nullable|string|max:255',
            'guardian_relation'    => 'nullable|string|max:100',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:50',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'blood_type'           => 'nullable|string|max:10',
            'medical_history'      => 'nullable|string',
            'physical_disability'  => 'nullable|string|max:100',
            'height_cm'            => 'nullable|integer|min:30|max:250',
            'weight_kg'            => 'nullable|integer|min:10|max:300',
            'prev_school_name'     => 'nullable|string|max:255',
            'prev_sttb_no'         => 'nullable|string|max:100',
            'prev_sttb_date'       => 'nullable|date',
            'prev_study_duration'  => 'nullable|string|max:50',
            'transfer_from_school' => 'nullable|string|max:255',
            'transfer_reason'      => 'nullable|string|max:255',
            'admission_grade'      => 'nullable|string|max:50',
            'admission_class_group'=> 'nullable|string|max:50',
            'admission_major'      => 'nullable|string|max:50',
            'admission_date'       => 'nullable|date',
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
