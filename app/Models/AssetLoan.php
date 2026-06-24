<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends Model
{
    protected $fillable = [
        'asset_id', 'user_id', 'start_date', 'end_date',
        'status', 'purpose', 'approved_by', 'rejection_note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'active'   => 'Dipinjam',
            'returned' => 'Dikembalikan',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'  => 'yellow',
            'approved' => 'blue',
            'active'   => 'green',
            'returned' => 'gray',
            'rejected' => 'red',
            default    => 'gray',
        };
    }
}
