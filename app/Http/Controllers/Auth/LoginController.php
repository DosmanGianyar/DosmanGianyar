<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PiketSession;
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
            'login'    => ['required', 'string'],
            'password' => ['required', 'string', 'regex:/^\S+$/'],
        ], [
            'password.regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        $loginInput = trim($request->input('login'));
        if (! str_contains($loginInput, '@')) {
            $loginInput = preg_replace('/\s+/', '', $loginInput);
        }

        $isEmailInput = str_contains($loginInput, '@');

        // Siswa/pengelola: hanya boleh login pakai NISN. Guru: NIP atau email. Admin: email.
        $user = $isEmailInput
            ? User::where('email', $loginInput)->first()
            : $this->findByUsername($loginInput);

        // Siswa/pengelola tidak boleh login pakai email — hanya NISN.
        if ($isEmailInput && $user && $user->isSiswa()) {
            return back()
                ->withErrors(['login' => 'Siswa wajib menggunakan NISN untuk login, bukan Alamat Email.'])
                ->onlyInput('login');
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

        if ($user->isPiket()) {
            $request->session()->forget(['piket_teacher_id', 'piket_teacher_name', 'piket_session_id']);
            return redirect()->route('piket.select-teacher');
        }

        return redirect($user->dashboardRoute());
    }

    private function findByUsername(string $input): ?User
    {
        // Siswa login dengan NISN, guru login dengan NIP, orangtua login dengan No. HP. NIS tidak lagi dipakai untuk login.
        $query = User::where('nisn', $input)->orWhere('nip', $input);

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
        $piketSessionId = $request->session()->get('piket_session_id');
        if ($piketSessionId) {
            $piketSession = PiketSession::where('id', $piketSessionId)
                ->whereNull('ended_at')
                ->first();
            if ($piketSession) {
                $piketSession->update(['ended_at' => now()]);
                ActivityLog::record('piket_duty_end', $piketSession, [
                    'teacher_id'   => $piketSession->teacher_id,
                    'teacher_name' => $request->session()->get('piket_teacher_name'),
                ]);
            }
        }

        ActivityLog::record('logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
