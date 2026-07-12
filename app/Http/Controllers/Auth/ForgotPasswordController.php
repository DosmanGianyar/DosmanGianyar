<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\OrangtuaSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showForm(): View
    {
        return view('auth.forgot-password');
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = trim($request->input('identifier'));
        $normalizedPhone = OrangtuaSyncService::normalizePhone($identifier);

        $user = User::where('nisn', $identifier)
            ->orWhere('nip', $identifier)
            ->when($normalizedPhone, fn ($q) => $q->orWhere(
                fn ($q2) => $q2->where('role', 'orangtua')->where('phone', $normalizedPhone)
            ))
            ->first();

        if (! $user) {
            return back()
                ->withErrors(['identifier' => 'NISN/NIP/No. HP tidak ditemukan. Periksa kembali nomor yang Anda masukkan.'])
                ->onlyInput('identifier');
        }

        $existingPending = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('status', 'Permintaan reset password Anda sebelumnya masih menunggu diproses admin.');
        }

        PasswordResetRequest::create([
            'user_id'      => $user->id,
            'identifier'   => $identifier,
            'status'       => 'pending',
            'requested_at' => now(),
        ]);

        return back()->with('status', 'Permintaan reset password berhasil dikirim. Admin akan segera memprosesnya.');
    }
}
