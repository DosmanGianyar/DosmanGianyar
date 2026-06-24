<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAssetQr extends Command
{
    protected $signature = 'assets:generate-qr {--force : Regenerate even if QR already exists}';
    protected $description = 'Generate QR code images for all assets';

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('qrcodes');

        $query = Asset::query();
        $total = $query->count();

        if ($total === 0) {
            $this->info('No assets found.');
            return self::SUCCESS;
        }

        $this->withProgressBar($query->cursor(), function (Asset $asset) {
            $exists = Storage::disk('public')->exists("qrcodes/{$asset->qr_code}.svg");

            if (!$exists || $this->option('force')) {
                $asset->generateQrImage();
            }
        });

        $this->newLine();
        $this->info("QR codes generated for {$total} assets.");

        return self::SUCCESS;
    }
}
