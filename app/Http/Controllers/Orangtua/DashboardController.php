<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $orangtua */
        $orangtua = Auth::user();

        $children = $orangtua->children()->with('schoolClass')->get();

        return view('orangtua.dashboard', compact('children'));
    }
}
