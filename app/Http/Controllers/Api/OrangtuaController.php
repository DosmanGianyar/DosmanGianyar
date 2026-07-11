<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrangtuaController extends Controller
{
    public function children(): JsonResponse
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();

        $children = $orangtua->children()->with('schoolClass')->get()->map(fn (User $c) => [
            'id'         => $c->id,
            'name'       => $c->name,
            'class_name' => $c->schoolClass?->name,
            'photo_url'  => $c->photo_url,
        ])->values();

        return response()->json(['children' => $children]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $child = $this->resolveChild($request);
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        return response()->json(StudentDataService::attendanceHistory($child, $month, $year));
    }

    public function conduct(Request $request): JsonResponse
    {
        return response()->json(StudentDataService::conductLogs($this->resolveChild($request)));
    }

    public function achievements(Request $request): JsonResponse
    {
        return response()->json(StudentDataService::achievements($this->resolveChild($request)));
    }

    /** Resolve & pastikan student_id yang diminta memang anak dari orangtua yang login. */
    private function resolveChild(Request $request): User
    {
        $request->validate(['student_id' => 'required|integer']);

        /** @var User $orangtua */
        $orangtua = Auth::user();

        $child = $orangtua->children()->where('users.id', $request->integer('student_id'))->first();

        abort_if(! $child, 403, 'Anda tidak memiliki akses ke data siswa ini.');

        return $child;
    }
}
