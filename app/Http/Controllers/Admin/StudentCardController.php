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
        $qrToken   = $user->qr_code_token ?? $user->qr_token ?? $user->nisn ?? $user->id;
        $qrContent = url('/biodata/' . $qrToken);
        $qrOptions = new QROptions([
            'outputType'   => 'png',
            'outputBase64' => true,
            'scale'        => 8,
            'quietzoneSize'=> 2,
        ]);
        $qrPng = (new QRCode($qrOptions))->render($qrContent);

        // Logo sekolah sebagai base64
        $logoBase64 = $this->toBase64(public_path('img/logo_sekolah.png'), 'png');

        // Stempel & TTD Kepsek sebagai base64
        $ttdBase64  = $this->toBase64(public_path('img/ttd_kepsek.png'), 'png');

        // Foto siswa sebagai base64
        $photoBase64 = null;
        if ($user->photo) {
            $path = Storage::disk('public')->path($user->photo);
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
            $photoBase64 = $this->toBase64($path, $ext === 'png' ? 'png' : 'jpeg');
        }

        // Render Aksara Bali sebagai Base64 Image untuk kompatibilitas 100% PDF
        $aksaraBaliBase64 = $this->renderAksaraBaliBase64();

        $pdf = Pdf::loadView('pdf.student-card', [
            'siswa'            => $user,
            'qrPng'            => $qrPng,
            'logoBase64'       => $logoBase64,
            'ttdBase64'        => $ttdBase64,
            'photoBase64'      => $photoBase64,
            'aksaraBaliBase64' => $aksaraBaliBase64,
        ])->setPaper([0, 0, 242.56, 153.07]); // 85.6mm × 54mm in points

        $filename = 'kartu-pelajar-' . ($user->nis ?? $user->id) . '.pdf';
        return $pdf->download($filename);
    }

    private function renderAksaraBaliBase64(): ?string
    {
        $fontPath = public_path('fonts/NotoSansBalinese-Bold.ttf');
        if (!file_exists($fontPath) || !function_exists('imagettfbbox')) {
            return null;
        }

        $text = '᭞ᬏᬲ᭄ᬏᬫ᭄ᬅ᭞ᬦᭂᬕᭂᬭᬶ᭞᭑᭞ᬕ᭄ᬬᬜᬃ᭞';
        $fontSize = 20;

        $bbox = @imagettfbbox($fontSize, 0, $fontPath, $text);
        if (!$bbox) return null;

        $width  = abs($bbox[4] - $bbox[0]) + 10;
        $height = abs($bbox[5] - $bbox[1]) + 10;

        $im = imagecreatetruecolor($width, $height);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);

        $color = imagecolorallocate($im, 147, 197, 253);

        $x = 2;
        $y = abs($bbox[5]) + 2;

        @imagettftext($im, $fontSize, 0, $x, $y, $color, $fontPath, $text);

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,' . base64_encode($data);
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
