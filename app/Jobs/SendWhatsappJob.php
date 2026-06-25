<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;      // retry 3x jika gagal
    public int $backoff = 10;     // tunggu 10 detik antar retry

    public function __construct(
        private readonly string $phone,
        private readonly string $message
    ) {}

    public function handle(WhatsappService $wa): void
    {
        if (empty($this->phone)) {
            Log::info('SendWhatsappJob: nomor HP kosong, dilewati.');
            return;
        }

        $wa->send($this->phone, $this->message);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsappJob gagal setelah semua retry', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}
