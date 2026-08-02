<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class StudentCardVerificationController extends Controller
{
    public function verify(string $identifier): View
    {
        $siswa = User::where(function ($q) use ($identifier) {
                $q->where('nis', $identifier);
                $q->orWhere('nisn', $identifier);
                if (is_numeric($identifier)) {
                    $q->orWhere('id', (int) $identifier);
                }
            })
            ->where('role', 'like', 'siswa%')
            ->with('schoolClass')
            ->firstOrFail();

        return view('public.verify-student-card', compact('siswa'));
    }
}
