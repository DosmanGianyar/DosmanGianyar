<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PiketSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PiketDutyController extends Controller
{
    public function selectForm(Request $request): View
    {
        $teachers = User::where('role', 'guru')
            ->orderBy('name')
            ->get(['id', 'name', 'nip', 'subject']);

        $recentShifts = PiketSession::with('teacher:id,name,nip')
            ->latest('started_at')
            ->limit(5)
            ->get();

        $activeTeacherId = $request->session()->get('piket_teacher_id');
        $activeTeacherName = $request->session()->get('piket_teacher_name');

        return view('piket.select-teacher', compact(
            'teachers',
            'recentShifts',
            'activeTeacherId',
            'activeTeacherName'
        ));
    }

    public function selectStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'notes'      => ['nullable', 'string', 'max:255'],
        ]);

        $teacher = User::where('id', $data['teacher_id'])
            ->where('role', 'guru')
            ->first();

        if (! $teacher) {
            return back()
                ->withErrors(['teacher_id' => 'Pengguna yang dipilih bukan guru aktif.'])
                ->withInput();
        }

        // Tutup sesi piket sebelumnya jika sedang berganti shift di perangkat ini
        $prevSessionId = $request->session()->get('piket_session_id');
        if ($prevSessionId) {
            $prevSession = PiketSession::where('id', $prevSessionId)
                ->whereNull('ended_at')
                ->first();
            if ($prevSession) {
                $prevSession->update(['ended_at' => now()]);
                ActivityLog::record('piket_duty_end', $prevSession, [
                    'teacher_id'   => $prevSession->teacher_id,
                    'teacher_name' => $request->session()->get('piket_teacher_name'),
                    'reason'       => 'switch_teacher',
                ]);
            }
        }

        $piketSession = PiketSession::create([
            'account_user_id' => Auth::id(),
            'teacher_id'      => $teacher->id,
            'started_at'      => now(),
            'ip_address'      => $request->ip(),
            'user_agent'      => mb_substr((string) $request->userAgent(), 0, 255),
            'notes'           => $data['notes'] ?? null,
        ]);

        $request->session()->put([
            'piket_teacher_id'   => $teacher->id,
            'piket_teacher_name' => $teacher->name,
            'piket_session_id'   => $piketSession->id,
        ]);

        ActivityLog::record('piket_duty_start', $piketSession, [
            'teacher_id'   => $teacher->id,
            'teacher_name' => $teacher->name,
        ]);

        return redirect()
            ->route('piket.dashboard')
            ->with('success', "Selamat bertugas piket, {$teacher->name}!");
    }
}
