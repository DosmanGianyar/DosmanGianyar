<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolRegulation;
use Illuminate\Http\JsonResponse;

class SchoolRegulationController extends Controller
{
    /**
     * Semua tata tertib aktif, dikelompokkan per kategori,
     * lengkap dengan Rich Text HTML, gambar seragam, dan panduan harian.
     */
    public function index(): JsonResponse
    {
        $regulations = SchoolRegulation::active()
            ->ordered()
            ->get();

        $grouped = $regulations->groupBy('category')->map(fn ($items, $category) => [
            'category'       => $category,
            'category_label' => SchoolRegulation::categories()[$category] ?? $category,
            'items'          => $items->map(fn (SchoolRegulation $r) => [
                'id'         => $r->id,
                'category'   => $r->category,
                'badge_text' => $r->badge_text,
                'title'      => $r->title,
                'content'    => $r->content,
                'image_url'  => $r->image_url,
                'image_urls' => $r->image_urls,
                'sort_order' => $r->sort_order,
            ])->values(),
        ])->values();

        // Kumpulan khusus panduan seragam harian untuk visual showcase di mobile
        $uniformGuides = $regulations
            ->where('category', 'berpakaian')
            ->values()
            ->map(fn (SchoolRegulation $r) => [
                'id'         => $r->id,
                'day'        => $r->badge_text ?? $r->title,
                'title'      => $r->title,
                'content'    => $r->content,
                'image_url'  => $r->image_url,
                'image_urls' => $r->image_urls,
            ]);

        return response()->json([
            'pdf_url'        => asset('tatatertib/Scan TataTertib SIswa.pdf'),
            'uniform_guides' => $uniformGuides,
            'regulations'    => $grouped,
        ]);
    }
}
