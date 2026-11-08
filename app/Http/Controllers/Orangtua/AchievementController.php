<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(int $studentId): View
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();
        $student  = $orangtua->children()->where('users.id', $studentId)->firstOrFail();

        // Tandai bahwa orangtua telah melihat prestasi anak
        \App\Models\ParentFeatureSeen::updateOrCreate(
            ['parent_id' => $orangtua->id, 'student_id' => $student->id, 'feature' => 'achievement'],
            [
                'last_seen_at' => now(),
                'last_seen_id' => \App\Models\StudentAchievement::where('student_id', $student->id)->max('id'),
            ]
        );

        $data = StudentDataService::achievements($student);

        return view('orangtua.achievement.index', [
            'student'      => $student,
            'achievements' => $data['achievements'],
            'stats'        => $data['stats'],
        ]);
    }
}
