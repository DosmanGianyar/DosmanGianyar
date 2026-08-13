<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeTtdTransparentCommand extends Command
{
    protected $signature = 'ttd:make-transparent';
    protected $description = 'Remove white background from ttd_kepsek.png';

    public function handle()
    {
        $imgPath = public_path('img/ttd_kepsek.png');
        if (!file_exists($imgPath)) {
            $this->error("File not found: " . $imgPath);
            return 1;
        }

        $im = imagecreatefrompng($imgPath);
        $width  = imagesx($im);
        $height = imagesy($im);

        $transparent = imagecreatetruecolor($width, $height);
        imagealphablending($transparent, false);
        imagesavealpha($transparent, true);
        $transColor = imagecolorallocatealpha($transparent, 0, 0, 0, 127);
        imagefill($transparent, 0, 0, $transColor);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($im, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                // Make white and near-white pixels transparent
                if ($r > 200 && $g > 200 && $b > 200) {
                    imagesetpixel($transparent, $x, $y, $transColor);
                } else {
                    $color = imagecolorallocatealpha($transparent, $r, $g, $b, 0);
                    imagesetpixel($transparent, $x, $y, $color);
                }
            }
        }

        imagepng($transparent, $imgPath);
        imagedestroy($im);
        imagedestroy($transparent);

        $this->info("Berhasil membuat ttd_kepsek.png transparan!");
        return 0;
    }
}
