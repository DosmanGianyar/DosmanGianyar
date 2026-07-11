<?php

namespace App\Console\Commands;

use App\Services\OrangtuaSyncService;
use Illuminate\Console\Command;

class SyncOrangtuaAccounts extends Command
{
    protected $signature   = 'orangtua:sync-all';
    protected $description = 'Buat/perbarui akun orangtua dari kolom parent_phone semua siswa (untuk sinkronisasi retroaktif data lama)';

    public function handle(): int
    {
        $synced = OrangtuaSyncService::syncAll();

        $this->info("Selesai. {$synced} akun orangtua tersinkronisasi.");

        return self::SUCCESS;
    }
}
