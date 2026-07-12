<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\OrangtuaSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            return redirect($user->dashboardRoute());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $loginInput = trim($request->input('login'));

        // Siswa/pengelola: hanya boleh login pakai NISN. Guru: NIP atau email. Admin: email.
        // Untuk NISN numerik: abaikan leading zeros (0001233344 = 1233344)
        $user = str_contains($loginInput, '@')
            ? User::where('email', $loginInput)->first()
            : $this->findByUsername($loginInput);

        // Siswa/pengelola tidak boleh login pakai email — hanya NISN.
        if ($user && $user->isSiswa() && str_contains($loginInput, '@')) {
            $user = null;
        }

        if (! $user || ! Auth::attempt(
            ['email' => $user->email, 'password' => $request->input('password')],
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors(['login' => 'NISN (siswa) / NIP atau Email (guru) / No. HP (orangtua) / password salah.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        ActivityLog::record('login');

        return redirect($user->dashboardRoute());
    }

    private function findByUsername(string $input): ?User
    {
        // Siswa login dengan NISN, guru login dengan NIP, orangtua login dengan No. HP. NIS tidak lagi dipakai untuk login.
        $query = User::where('nisn', $input)->orWhere('nip', $input);

        // NISN selalu 10 digit — jika input numerik kurang dari 10 digit, coba zero-pad
        if (ctype_digit($input) && strlen($input) < 10) {
            $padded = str_pad($input, 10, '0', STR_PAD_LEFT);
            $query->orWhere('nisn', $padded);
        }

        $normalizedPhone = OrangtuaSyncService::normalizePhone($input);
        if ($normalizedPhone) {
            $query->orWhere(function ($q) use ($normalizedPhone) {
                $q->where('role', 'orangtua')->where('phone', $normalizedPhone);
            });
        }

        return $query->first();
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record('logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
