<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayananController extends Controller
{
    public function index(Request $request): View
    {
        // 1. List Wali Kelas
        $waliKelas = SchoolClass::with('homeroomTeacher')
            ->orderBy('name')
            ->get();

        // 2. List Pembina Ekstrakurikuler
        $extracurriculars = Extracurricular::with(['teachers', 'pembina'])
            ->orderBy('name')
            ->get();

        // 3. List Semua Guru & Pengajar
        $gurus = User::where('role', 'guru')
            ->orderBy('name')
            ->get();

        // 4. Guru BK / Konselor Sekolah
        $guruBk = User::where('role', 'guru')
            ->where(function ($q) {
                $q->where('subject', 'LIKE', '%BK%')
                  ->orWhere('subject', 'LIKE', '%Bimbingan%')
                  ->orWhere('subject', 'LIKE', '%Konseling%');
            })
            ->get();

        // 5. Jadwal Mengajar / Guru Piket Per Hari (1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat)
        $piketSchedule = Schedule::with(['teacher', 'subject', 'schoolClass'])
            ->orderBy('day')
            ->orderBy('period')
            ->get()
            ->groupBy('day');

        return view('guru.layanan.index', compact(
            'waliKelas',
            'extracurriculars',
            'gurus',
            'guruBk',
            'piketSchedule'
        ));
    }
}
