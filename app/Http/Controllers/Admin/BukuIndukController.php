<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\View\View;

class BukuIndukController extends Controller
{
    public function printSingle(User $siswa): View
    {
        abort_if(! in_array($siswa->role, ['siswa', 'pengelola']), 404, 'User bukan siswa.');

        return view('reports.buku-induk-siswa', [
            'students' => collect([$siswa]),
        ]);
    }

    public function printClass(SchoolClass $class): View
    {
        $students = User::where('class_id', $class->id)
            ->whereIn('role', ['siswa', 'pengelola'])
            ->orderBy('name')
            ->get();

        abort_if($students->isEmpty(), 404, 'Tidak ada data siswa pada kelas ini.');

        return view('reports.buku-induk-siswa', [
            'students' => $students,
            'class'    => $class,
        ]);
    }
}
