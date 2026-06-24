<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    protected $fillable = [
        'asset_id', 'reporter_id', 'photo', 'description',
        'status', 'handled_by', 'resolution_note',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'     => 'Menunggu',
            'in_progress' => 'Ditangani',
            'resolved'    => 'Selesai',
            default       => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'     => 'yellow',
            'in_progress' => 'blue',
            'resolved'    => 'green',
            default       => 'gray',
        };
    }

    public function daysOpen(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    // SLA threshold: 3 days for pending, 7 days for in_progress
    public function slaLevel(): string
    {
        if ($this->status === 'resolved') return 'ok';
        $days      = $this->daysOpen();
        $threshold = $this->status === 'pending' ? 3 : 7;
        if ($days >= $threshold * 2) return 'critical';
        if ($days >= $threshold)     return 'warning';
        return 'ok';
    }
}
