<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolRegulation;
use Illuminate\Http\JsonResponse;

class SchoolRegulationController extends Controller
{
    /**
     * Semua tata tertib aktif, dikelompokkan per kategori.
     */
    public function index(): JsonResponse
    {
        $regulations = SchoolRegulation::active()
            ->ordered()
            ->get(['id', 'category', 'title', 'content']);

        $grouped = $regulations->groupBy('category')->map(fn ($items, $category) => [
            'category'       => $category,
            'category_label' => SchoolRegulation::categories()[$category] ?? $category,
            'items'          => $items->map(fn ($r) => [
                'id'      => $r->id,
                'title'   => $r->title,
                'content' => $r->content,
            ])->values(),
        ])->values();

        return response()->json(['regulations' => $grouped]);
    }
}
