<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageService;
use App\Services\OrangtuaSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();
        $orangtua->loadMissing('children.schoolClass');

        return view('orangtua.profile', [
            'orangtua' => $orangtua,
            'children' => $orangtua->children,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();

        $normalizedPhone = $request->filled('phone')
            ? OrangtuaSyncService::normalizePhone((string) $request->input('phone'))
            : $orangtua->phone;

        $request->merge([
            'phone' => $normalizedPhone,
            'email' => $request->filled('email') ? trim((string) $request->input('email')) : $orangtua->email,
        ]);

        $validated = $request->validate([
            'name'                       => 'required|string|max:100',
            'nickname'                   => 'nullable|string|max:50',
            'parent_relation'            => 'nullable|string|max:50',
            'nik'                        => 'nullable|string|max:30',
            'phone'                      => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')
                    ->where(fn ($q) => $q->where('role', 'orangtua'))
                    ->ignore($orangtua->id),
            ],
            'email'                      => 'nullable|email|max:255|unique:users,email,' . $orangtua->id,
            'gender'                     => 'nullable|in:L,P',
            'birth_place'                => 'nullable|string|max:100',
            'birth_date'                 => 'nullable|date',
            'religion'                   => 'nullable|string|max:50',
            'citizenship'                => 'nullable|string|max:50',
            'marital_status'             => 'nullable|string|max:30',
            'last_education'             => 'nullable|string|max:50',
            'occupation'                 => 'nullable|string|max:100',
            'company_name'               => 'nullable|string|max:150',
            'income_range'               => 'nullable|string|max:100',
            'address'                    => 'nullable|string|max:255',
            'rt_rw'                      => 'nullable|string|max:50',
            'kelurahan'                  => 'nullable|string|max:100',
            'kecamatan'                  => 'nullable|string|max:100',
            'kabupaten'                  => 'nullable|string|max:100',
            'emergency_contact_name'     => 'nullable|string|max:100',
            'emergency_contact_phone'    => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:50',
        ], [
            'name.required'  => 'Nama lengkap orang tua wajib diisi.',
            'name.max'       => 'Nama lengkap maksimal 100 karakter.',
            'phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'phone.unique'   => 'Nomor HP tersebut sudah terdaftar pada akun orang tua lain.',
            'email.unique'   => 'Email tersebut sudah digunakan oleh akun lain.',
        ]);

        if (empty($validated['email'])) {
            $validated['email'] = $validated['phone'] . '@ortu.sims.sch.id';
        }

        $orangtua->update($validated);

        // Sinkronkan ke seluruh siswa (anak) yang terhubung dengan akun orang tua ini
        $childUpdates = [
            'parent_name'  => $orangtua->name,
            'parent_phone' => $orangtua->phone,
        ];

        $rel = $orangtua->parent_relation;
        if ($rel === 'Ayah Kandung') {
            $childUpdates['father_name']        = $orangtua->name;
            $childUpdates['father_phone']       = $orangtua->phone;
            $childUpdates['father_birth_place'] = $orangtua->birth_place;
            $childUpdates['father_birth_date']  = $orangtua->birth_date;
            $childUpdates['father_religion']    = $orangtua->religion;
            $childUpdates['father_citizenship'] = $orangtua->citizenship;
            $childUpdates['father_education']   = $orangtua->last_education;
            $childUpdates['father_job']         = $orangtua->occupation;
            $childUpdates['father_income']      = $orangtua->income_range;
            $childUpdates['father_address']     = $orangtua->address;
        } elseif ($rel === 'Ibu Kandung') {
            $childUpdates['mother_name']        = $orangtua->name;
            $childUpdates['mother_phone']       = $orangtua->phone;
            $childUpdates['mother_birth_place'] = $orangtua->birth_place;
            $childUpdates['mother_birth_date']  = $orangtua->birth_date;
            $childUpdates['mother_religion']    = $orangtua->religion;
            $childUpdates['mother_citizenship'] = $orangtua->citizenship;
            $childUpdates['mother_education']   = $orangtua->last_education;
            $childUpdates['mother_job']         = $orangtua->occupation;
            $childUpdates['mother_income']      = $orangtua->income_range;
            $childUpdates['mother_address']     = $orangtua->address;
        } elseif ($rel === 'Wali') {
            $childUpdates['guardian_name']        = $orangtua->name;
            $childUpdates['guardian_phone']       = $orangtua->phone;
            $childUpdates['guardian_birth_place'] = $orangtua->birth_place;
            $childUpdates['guardian_birth_date']  = $orangtua->birth_date;
            $childUpdates['guardian_religion']    = $orangtua->religion;
            $childUpdates['guardian_citizenship'] = $orangtua->citizenship;
            $childUpdates['guardian_education']   = $orangtua->last_education;
            $childUpdates['guardian_job']         = $orangtua->occupation;
            $childUpdates['guardian_income']      = $orangtua->income_range;
            $childUpdates['guardian_address']     = $orangtua->address;
        }

        // Gunakan query update langsung agar relasi parent_students tidak terputus
        $orangtua->children()->update(array_filter($childUpdates, fn ($v) => $v !== null));

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

        /** @var User $orangtua */
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
