<?php

namespace App\Http\Controllers\Guru;

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
        return view('guru.profile', ['guru' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'nullable|string|max:20',
            'nip'     => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        Auth::user()->update($request->only('name', 'phone', 'nip', 'subject', 'address'));

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        Auth::user()->update(['password' => $request->password]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png|max:2048']);

        $user = Auth::user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = ImageService::storeAvatar($request->file('photo'), 'avatars');
        $user->update(['photo' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
