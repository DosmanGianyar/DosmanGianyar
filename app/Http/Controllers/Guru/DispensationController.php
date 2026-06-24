<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Dispensation;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DispensationController extends Controller
{
    public function create(): View
    {
        $classes = SchoolClass::with('students')->get();
        return view('guru.attendance.dispensation-create', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'activity_name' => 'required|string|max:255',
            'date'          => 'required|date',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('dispensations', 'public');
        }

        $dispensation = Dispensation::create([
            'requester_id'  => Auth::id(),
            'activity_name' => $request->activity_name,
            'date'          => $request->date,
            'file'          => $filePath,
            'status'        => 'approved',
            'approved_by'   => Auth::id(),
        ]);

        $dispensation->students()->attach($request->student_ids);

        foreach ($request->student_ids as $studentId) {
            Attendance::updateOrCreate(
                ['user_id' => $studentId, 'date' => $request->date],
                ['status' => 'dispensasi', 'check_in_time' => null]
            );
        }

        return redirect()->route('guru.attendance.index')
            ->with('success', 'Dispensasi untuk ' . count($request->student_ids) . ' siswa berhasil dicatat.');
    }
}
