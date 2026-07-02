<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class StudentCardController extends Controller
{
    public function download(User $user): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless(str_starts_with($user->role, 'siswa'), 403);

        $user->load('schoolClass');

        // QR sebagai PNG base64 (dompdf tidak render SVG base64 dengan baik)
        $qrContent = url('/biodata/' . ($user->nis ?? $user->id));
        $qrOptions = new QROptions([
            'outputType'   => 'png',
            'outputBase64' => true,
            'scale'        => 8,
            'quietzoneSize'=> 2,
        ]);
        $qrPng = (new QRCode($qrOptions))->render($qrContent);

        // Logo sekolah sebagai base64
        $logoBase64 = $this->toBase64(public_path('img/logo_sekolah.png'), 'png');

        // Foto siswa sebagai base64
        $photoBase64 = null;
        if ($user->photo) {
            $path = Storage::disk('public')->path($user->photo);
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
            $photoBase64 = $this->toBase64($path, $ext === 'png' ? 'png' : 'jpeg');
        }

        $pdf = Pdf::loadView('pdf.student-card', [
            'siswa'       => $user,
            'qrPng'       => $qrPng,
            'logoBase64'  => $logoBase64,
            'photoBase64' => $photoBase64,
        ])->setPaper([0, 0, 242.56, 153.07]); // 85.6mm × 54mm in points

        $filename = 'kartu-pelajar-' . ($user->nis ?? $user->id) . '.pdf';
        return $pdf->download($filename);
    }

    private function toBase64(string $path, string $ext): ?string
    {
        if (!file_exists($path)) return null;
        $mime = match($ext) {
            'png'  => 'image/png',
            'jpeg' => 'image/jpeg',
            default => 'image/png',
        };
        return "data:{$mime};base64," . base64_encode(file_get_contents($path));
    }
}
