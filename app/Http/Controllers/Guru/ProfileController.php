<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
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
        /** @var User $guru */
        $guru = Auth::user();
        $guru->loadMissing(['subjects', 'homeroomClass']);

        $allSubjects = Subject::orderBy('name')->get();

        return view('guru.profile', [
            'guru'        => $guru,
            'allSubjects' => $allSubjects,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Normalisasi field unik yang opsional agar string kosong menjadi null
        $request->merge([
            'nip'          => $request->filled('nip') ? preg_replace('/\s+/', '', trim((string) $request->input('nip'))) : null,
            'teacher_code' => $request->filled('teacher_code') ? trim((string) $request->input('teacher_code')) : null,
            'email'        => $request->filled('email') ? trim((string) $request->input('email')) : $user->email,
        ]);

        $validated = $request->validate([
            'name'                       => 'required|string|max:100',
            'nickname'                   => 'nullable|string|max:50',
            'email'                      => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'                      => 'nullable|string|max:30',
            'nip'                        => 'nullable|string|max:30|unique:users,nip,' . $user->id,
            'nik'                        => 'nullable|string|max:30',
            'nuptk'                      => 'nullable|string|max:30',
            'teacher_code'               => 'nullable|string|max:50|unique:users,teacher_code,' . $user->id,
            'gender'                     => 'nullable|in:L,P',
            'birth_place'                => 'nullable|string|max:100',
            'birth_date'                 => 'nullable|date',
            'religion'                   => 'nullable|string|max:50',
            'citizenship'                => 'nullable|string|max:50',
            'blood_type'                 => 'nullable|string|max:10',
            'marital_status'             => 'nullable|string|max:30',
            'employment_status'          => 'nullable|string|max:50',
            'rank_grade'                 => 'nullable|string|max:50',
            'position'                   => 'nullable|string|max:100',
            'work_unit'                  => 'nullable|string|max:100',
            'last_education'             => 'nullable|string|max:50',
            'alma_mater'                 => 'nullable|string|max:150',
            'tmt_date'                   => 'nullable|date',
            'subject'                    => 'nullable|string|max:255',
            'subject_ids'                => 'nullable|array',
            'subject_ids.*'              => 'integer|exists:subjects,id',
            'address'                    => 'nullable|string|max:255',
            'rt_rw'                      => 'nullable|string|max:50',
            'kelurahan'                  => 'nullable|string|max:100',
            'kecamatan'                  => 'nullable|string|max:100',
            'kabupaten'                  => 'nullable|string|max:100',
            'emergency_contact_name'     => 'nullable|string|max:100',
            'emergency_contact_phone'    => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:50',
        ], [
            'name.required'       => 'Nama lengkap wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'email.unique'        => 'Email tersebut sudah digunakan oleh akun lain.',
            'nip.unique'          => 'NIP tersebut sudah terdaftar pada akun lain.',
            'teacher_code.unique' => 'Kode Guru tersebut sudah digunakan oleh guru lain.',
        ]);

        $subjectIds = $validated['subject_ids'] ?? null;
        unset($validated['subject_ids']);

        if ($user->role !== 'pegawai' && ($request->has('subjects_section_present') || is_array($subjectIds))) {
            $ids = is_array($subjectIds) ? $subjectIds : [];
            $user->subjects()->sync($ids);

            if (! empty($ids)) {
                $subjectNames = Subject::whereIn('id', $ids)->orderBy('name')->pluck('name')->all();
                if (empty($validated['subject'])) {
                    $validated['subject'] = implode(', ', $subjectNames);
                }
            }
        }

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'min:8', 'confirmed', 'regex:/^\S+$/'],
        ], [
            'password.regex'   => 'Password tidak boleh mengandung spasi.',
        ]);

        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        Auth::user()->update([
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

        /** @var User $user */
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
