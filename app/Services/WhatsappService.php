<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsappService
{
    private string $url;
    private string $secret;

    public function __construct()
    {
        $this->url    = rtrim(config('services.baileys.url', 'http://localhost:3000'), '/');
        $this->secret = config('services.baileys.secret', '');
    }

    /**
     * Kirim pesan WA. Return true jika berhasil.
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($phone)) return false;

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->secret ? ['x-api-key' => $this->secret] : [])
                ->post("{$this->url}/send", [
                    'phone'   => $phone,
                    'message' => $message,
                ]);

            if ($response->successful() && $response->json('success')) {
                return true;
            }

            Log::warning('WhatsApp gagal kirim', [
                'phone'    => $phone,
                'response' => $response->body(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('WhatsApp exception', ['error' => $e->getMessage(), 'phone' => $phone]);
            return false;
        }
    }

    // ── Template Pesan ────────────────────────────────────────────────────

    public function templateCheckIn(
        string $parentName,
        string $studentName,
        string $className,
        string $status,
        string $time
    ): string {
        $statusLabel = $status === 'hadir' ? '✅ *Hadir*' : '⚠️ *Terlambat*';
        $tanggal     = Carbon::now()->isoFormat('dddd, D MMMM Y');

        return "🏫 *SIMS - SMA Negeri 1 Gianyar*\n\n"
            . "Yth. Orang Tua/Wali *{$parentName}*\n\n"
            . "Putra/Putri Anda:\n"
            . "👤 *{$studentName}* ({$className})\n\n"
            . "telah melakukan *Absen Masuk*\n"
            . "🕐 Jam    : *{$time}*\n"
            . "📊 Status : {$statusLabel}\n"
            . "📅 Tanggal: {$tanggal}\n\n"
            . "_Pesan ini dikirim otomatis oleh sistem SIMS._";
    }

    public function templateCheckOut(
        string $parentName,
        string $studentName,
        string $className,
        string $time
    ): string {
        $tanggal = Carbon::now()->isoFormat('dddd, D MMMM Y');

        return "🏫 *SIMS - SMA Negeri 1 Gianyar*\n\n"
            . "Yth. Orang Tua/Wali *{$parentName}*\n\n"
            . "Putra/Putri Anda:\n"
            . "👤 *{$studentName}* ({$className})\n\n"
            . "telah melakukan *Absen Pulang*\n"
            . "🕐 Jam    : *{$time}*\n"
            . "📅 Tanggal: {$tanggal}\n\n"
            . "_Pesan ini dikirim otomatis oleh sistem SIMS._";
    }

    public function templatePermit(
        string $parentName,
        string $studentName,
        string $className,
        string $typeLabel,
        string $startDate,
        string $endDate,
        string $reason
    ): string {
        $start = Carbon::parse($startDate)->isoFormat('D MMMM Y');
        $end   = Carbon::parse($endDate)->isoFormat('D MMMM Y');
        $range = $start === $end ? $start : "{$start} s/d {$end}";

        return "🏫 *SIMS - SMA Negeri 1 Gianyar*\n\n"
            . "Yth. Orang Tua/Wali *{$parentName}*\n\n"
            . "Putra/Putri Anda:\n"
            . "👤 *{$studentName}* ({$className})\n\n"
            . "mengajukan *{$typeLabel}*\n"
            . "📅 Tanggal  : *{$range}*\n"
            . "📝 Keterangan: {$reason}\n\n"
            . "Status: ⏳ *Menunggu persetujuan guru*\n\n"
            . "_Pesan ini dikirim otomatis oleh sistem SIMS._";
    }
}
