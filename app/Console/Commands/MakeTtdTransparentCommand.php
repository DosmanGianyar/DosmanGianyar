<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeTtdTransparentCommand extends Command
{
    protected $signature = 'ttd:make-transparent';
    protected $description = 'Remove white background and crop border lines from ttd_kepsek.png';

    public function handle()
    {
        // Re-copy original image if saved or process current png
        $imgPath = public_path('img/ttd_kepsek.png');
        if (!file_exists($imgPath)) {
            $this->error("File not found: " . $imgPath);
            return 1;
        }

        $im = imagecreatefrompng($imgPath);
        $width  = imagesx($im);
        $height = imagesy($im);

        // Crop margin off outer border to eliminate faint outline box
        $cropMargin = 5;
        $newW = max(1, $width - ($cropMargin * 2));
        $newH = max(1, $height - ($cropMargin * 2));

        $transparent = imagecreatetruecolor($newW, $newH);
        imagealphablending($transparent, false);
        imagesavealpha($transparent, true);
        $transColor = imagecolorallocatealpha($transparent, 0, 0, 0, 127);
        imagefill($transparent, 0, 0, $transColor);

        for ($x = 0; $x < $newW; $x++) {
            for ($y = 0; $y < $newH; $y++) {
                $srcX = $x + $cropMargin;
                $srcY = $y + $cropMargin;
                $rgba = imagecolorat($im, $srcX, $srcY);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $a = ($rgba >> 24) & 0x7F;

                // Keep black signature ink and purple stamp, discard background & gray border lines
                $isBlackInk    = ($r < 130 && $g < 130 && $b < 130);
                $isPurpleStamp = ($b > 75 && ($b - $g > 8 || $r - $g > 8));

                if ($a < 100 && ($isBlackInk || $isPurpleStamp)) {
                    $color = imagecolorallocatealpha($transparent, $r, $g, $b, $a);
                    imagesetpixel($transparent, $x, $y, $color);
                } else {
                    imagesetpixel($transparent, $x, $y, $transColor);
                }
            }
        }

        imagepng($transparent, $imgPath);
        imagedestroy($im);
        imagedestroy($transparent);

        $this->info("Berhasil memotong border & membuat ttd_kepsek.png transparan 100%!");
        return 0;
    }
}
