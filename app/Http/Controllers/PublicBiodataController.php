<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class PublicBiodataController extends Controller
{
    public function show(string $identifier): View
    {
        $siswa = User::where(function ($q) use ($identifier) {
                $q->where('nis', $identifier);
                if (is_numeric($identifier)) {
                    $q->orWhere('id', (int) $identifier);
                }
            })
            ->where('role', 'like', 'siswa%')
            ->with('schoolClass')
            ->firstOrFail();

        return view('public.biodata', compact('siswa'));
    }
}
