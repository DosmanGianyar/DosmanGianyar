<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAllPasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reset-passwords {--force : Jalankan tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password seluruh akun Siswa (ke NISN), Guru (ke NIP), dan Orang Tua (ke No. HP) serta paksa ganti password pada login pertama.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin mereset password SELURUH akun Siswa, Guru, dan Orang Tua?')) {
            $this->info('Operasi dibatalkan.');
            return Command::SUCCESS;
        }

        $this->info('Memulai proses reset password massal...');

        $siswaCount = 0;
        $guruCount = 0;
        $orangtuaCount = 0;
        $skippedCount = 0;

        User::chunk(200, function ($users) use (&$siswaCount, &$guruCount, &$orangtuaCount, &$skippedCount) {
            foreach ($users as $user) {
                // Skip admin dan akun demo playstore
                if ($user->role === 'admin' || $user->email === 'playstore.demo@sims.sch.id') {
                    $skippedCount++;
                    continue;
                }

                $newPasswordRaw = null;

                if (in_array($user->role, ['siswa', 'pengelola'], true)) {
                    $newPasswordRaw = trim((string) ($user->nisn ?? $user->nis ?? $user->username));
                    $siswaCount++;
                } elseif ($user->role === 'guru') {
                    $newPasswordRaw = trim((string) ($user->nip ?? $user->username));
                    $guruCount++;
                } elseif ($user->role === 'orangtua') {
                    $newPasswordRaw = trim((string) $user->phone);
                    $orangtuaCount++;
                }

                if (filled($newPasswordRaw)) {
                    $user->update([
                        'password'             => Hash::make($newPasswordRaw),
                        'must_change_password' => true,
                    ]);
                }
            }
        });

        $this->newLine();
        $this->info('✅ Proses Reset Password Selesai:');
        $this->line("   • Siswa / Pengelola di-reset : {$siswaCount} akun (Password = NISN)");
        $this->line("   • Guru di-reset              : {$guruCount} akun (Password = NIP)");
        $this->line("   • Orang Tua di-reset         : {$orangtuaCount} akun (Password = No. HP)");
        $this->line("   • Dikecualikan (Admin/Demo)  : {$skippedCount} akun");
        $this->newLine();
        $this->info('Seluruh akun yang direset telah ditandai wajib mengganti password setelah login.');

        return Command::SUCCESS;
    }
}
