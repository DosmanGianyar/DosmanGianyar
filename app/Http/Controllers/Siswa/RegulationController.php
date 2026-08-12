<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SchoolRegulation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegulationController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = $request->query('category');
        $search = $request->query('q');

        $query = SchoolRegulation::active()->ordered();

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $regulations = $query->get()->groupBy('category');
        $categories  = SchoolRegulation::categories();
        $pdfUrl      = asset('tatatertib/Scan TataTertib SIswa.pdf');

        return view('siswa.tata-tertib.index', compact(
            'regulations',
            'categories',
            'selectedCategory',
            'search',
            'pdfUrl'
        ));
    }
}
