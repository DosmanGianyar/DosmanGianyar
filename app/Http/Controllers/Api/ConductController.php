<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConductController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var \App\Models\User $siswa */
        $siswa = Auth::user();

        return response()->json(StudentDataService::conductLogs($siswa));
    }
}
