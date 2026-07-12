<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConductController extends Controller
{
    public function index(int $studentId): View
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();
        $student  = $orangtua->children()->where('users.id', $studentId)->firstOrFail();

        $data = StudentDataService::conductLogs($student);

        return view('orangtua.conduct.index', [
            'student'          => $student,
            'logs'             => $data['logs'],
            'prestasiCount'    => $data['summary']['prestasi_count'],
            'pelanggaranCount' => $data['summary']['pelanggaran_count'],
        ]);
    }
}
