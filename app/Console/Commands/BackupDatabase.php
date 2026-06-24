<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup {--keep=7 : Jumlah backup yang disimpan}';
    protected $description = 'Backup database ke storage/backups/';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            return $this->backupSqlite();
        }

        return $this->backupMysql();
    }

    private function backupSqlite(): int
    {
        $source = database_path('database.sqlite');

        if (! file_exists($source)) {
            $this->error('File SQLite tidak ditemukan: ' . $source);
            return 1;
        }

        $filename = 'backups/sims_' . now()->format('Ymd_His') . '.sqlite';
        Storage::disk('local')->put($filename, file_get_contents($source));

        $this->info("Backup SQLite berhasil: storage/app/{$filename}");
        $this->pruneOldBackups('sqlite');

        return 0;
    }

    private function backupMysql(): int
    {
        $cfg  = config('database.connections.' . config('database.default'));
        $host = $cfg['host'];
        $port = $cfg['port'] ?? 3306;
        $db   = $cfg['database'];
        $user = $cfg['username'];
        $pass = $cfg['password'];

        $filename = 'backups/sims_' . now()->format('Ymd_His') . '.sql.gz';
        $path     = storage_path('app/' . $filename);

        @mkdir(dirname($path), 0755, true);

        $cmd = sprintf(
            'mysqldump --single-transaction --routines --triggers -h %s -P %s -u %s %s %s | gzip > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($user),
            $pass ? '-p' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($path)
        );

        exec($cmd, $output, $code);

        if ($code !== 0 || ! file_exists($path) || filesize($path) < 100) {
            $this->error('mysqldump gagal. Pastikan mysqldump terinstall dan kredensial DB benar.');
            return 1;
        }

        $size = round(filesize($path) / 1024, 1);
        $this->info("Backup MySQL berhasil: storage/app/{$filename} ({$size} KB)");
        $this->pruneOldBackups('sql.gz');

        return 0;
    }

    private function pruneOldBackups(string $ext): void
    {
        $keep  = (int) $this->option('keep');
        $files = Storage::disk('local')->files('backups');

        $matching = array_filter($files, fn($f) => str_ends_with($f, '.' . $ext));
        sort($matching);

        $toDelete = array_slice($matching, 0, max(0, count($matching) - $keep));

        foreach ($toDelete as $file) {
            Storage::disk('local')->delete($file);
            $this->line("Backup lama dihapus: {$file}");
        }
    }
}
