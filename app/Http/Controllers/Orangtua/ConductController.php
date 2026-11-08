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

        // Tandai bahwa orangtua telah melihat catatan karakter anak
        \App\Models\ParentFeatureSeen::updateOrCreate(
            ['parent_id' => $orangtua->id, 'student_id' => $student->id, 'feature' => 'conduct'],
            [
                'last_seen_at' => now(),
                'last_seen_id' => \App\Models\ConductLog::where('student_id', $student->id)->max('id'),
            ]
        );

        $data = StudentDataService::conductLogs($student);

        return view('orangtua.conduct.index', [
            'student'          => $student,
            'logs'             => $data['logs'],
            'prestasiCount'    => $data['summary']['prestasi_count'],
            'pelanggaranCount' => $data['summary']['pelanggaran_count'],
        ]);
    }
}
