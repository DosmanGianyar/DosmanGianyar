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

        $data = StudentDataService::achievements($student);

        return view('orangtua.achievement.index', [
            'student'      => $student,
            'achievements' => $data['achievements'],
            'stats'        => $data['stats'],
        ]);
    }
}
