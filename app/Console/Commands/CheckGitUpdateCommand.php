<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckGitUpdateCommand extends Command
{
    protected $signature   = 'git:check-update {--force : Paksa git pull tanpa mengecek jumlah commit baru}';
    protected $description = 'Mengecek dan menarik update dari repositori GitHub DosmanGianyar secara otomatis';

    public function handle(): int
    {
        $this->info('Mengecek update dari repositori GitHub (origin/main)...');

        $baseDir = base_path();

        // 1. Fetch remote origin
        exec("cd {$baseDir} && git fetch origin main 2>&1", $fetchOutput, $fetchCode);
        if ($fetchCode !== 0) {
            $this->error('Gagal terhubung ke Git remote origin.');
            Log::error('Git update check failed during fetch', ['output' => $fetchOutput]);
            return 1;
        }

        // 2. Cek commit di belakang origin/main
        exec("cd {$baseDir} && git rev-list HEAD..origin/main --count 2>&1", $countOutput, $countCode);
        $behindCount = isset($countOutput[0]) ? (int) $countOutput[0] : 0;

        if ($behindCount === 0 && ! $this->option('force')) {
            $this->info('Repositori lokal sudah paling baru (0 commit baru).');
            Log::info('Git update check: Repositori sudah up to date.');
            return 0;
        }

        $this->info("Ditemukan {$behindCount} commit baru. Menarik update dari origin/main...");

        // 3. Pull update
        exec("cd {$baseDir} && git pull origin main 2>&1", $pullOutput, $pullCode);
        if ($pullCode !== 0) {
            $this->error('Gagal melakukan git pull.');
            Log::error('Git pull failed', ['output' => $pullOutput]);
            return 1;
        }

        // 4. Clear cache jika berhasil
        $this->call('optimize:clear');

        $this->info("Berhasil memperbarui repositori! ({$behindCount} commit baru ditarik).");
        Log::info("Git update sukses: {$behindCount} commit ditarik dari origin/main.");

        return 0;
    }
}
