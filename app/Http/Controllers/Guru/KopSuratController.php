<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class KopSuratController extends Controller
{
    /**
     * Tampilkan halaman preview & download KOP Surat resmi.
     */
    public function index(): View
    {
        return view('guru.kop-surat.index');
    }

    /**
     * Download file gambar KOP Surat langsung sebagai attachment (aktif maupun arsip lama).
     */
    public function download(string $type): BinaryFileResponse
    {
        $map = [
            // KOP Surat Baru (Aktif)
            'portrait' => [
                'file' => 'kop-surat-portrait.png',
                'name' => 'KOP-Surat-SMAN1-Gianyar-Portrait-LatarPutih.png',
            ],
            'portrait-transparan' => [
                'file' => 'kop-surat-portrait-transparan.png',
                'name' => 'KOP-Surat-SMAN1-Gianyar-Portrait-Transparan.png',
            ],
            'landscape' => [
                'file' => 'kop-surat-landscape.png',
                'name' => 'KOP-Surat-SMAN1-Gianyar-Landscape-LatarPutih.png',
            ],
            'landscape-transparan' => [
                'file' => 'kop-surat-landscape-transparan.png',
                'name' => 'KOP-Surat-SMAN1-Gianyar-Landscape-Transparan.png',
            ],

            // Arsip KOP Surat Lama (Format Disdikpora)
            'arsip-portrait' => [
                'file' => 'arsip-kop-lama/kop-surat-portrait.png',
                'name' => 'KOP-Surat-LAMA-Disdikpora-SMAN1-Gianyar-Portrait-LatarPutih.png',
            ],
            'arsip-portrait-transparan' => [
                'file' => 'arsip-kop-lama/kop-surat-portrait-transparan.png',
                'name' => 'KOP-Surat-LAMA-Disdikpora-SMAN1-Gianyar-Portrait-Transparan.png',
            ],
            'arsip-landscape' => [
                'file' => 'arsip-kop-lama/kop-surat-landscape.png',
                'name' => 'KOP-Surat-LAMA-Disdikpora-SMAN1-Gianyar-Landscape-LatarPutih.png',
            ],
            'arsip-landscape-transparan' => [
                'file' => 'arsip-kop-lama/kop-surat-landscape-transparan.png',
                'name' => 'KOP-Surat-LAMA-Disdikpora-SMAN1-Gianyar-Landscape-Transparan.png',
            ],
        ];

        if (!isset($map[$type])) {
            abort(404, 'Format KOP surat tidak ditemukan.');
        }

        $filePath = public_path('img/' . $map[$type]['file']);

        if (!file_exists($filePath)) {
            abort(404, 'File gambar KOP surat belum dibuat.');
        }

        return response()->download($filePath, $map[$type]['name'], [
            'Content-Type'        => 'image/png',
            'Cache-Control'       => 'public, max-age=86400',
            'Content-Disposition' => 'attachment; filename="' . $map[$type]['name'] . '"',
        ]);
    }
}
