<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRequest extends Model
{
    protected $fillable = [
        'user_id', 'identifier', 'status', 'requested_at', 'processed_at', 'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Reset password user ke default (NISN untuk siswa/pengelola, NIP untuk guru,
     * No. HP untuk orangtua), lalu tandai permintaan ini sebagai disetujui.
     */
    public function approve(User $admin): void
    {
        $user     = $this->user;
        $default  = $user->isOrangtua() ? $user->phone : ($user->isSiswa() ? $user->nisn : $user->nip);

        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($default)]);
        $user->resetDevices();

        $this->update([
            'status'       => 'approved',
            'processed_at' => now(),
            'processed_by' => $admin->id,
        ]);
    }

    public function reject(User $admin): void
    {
        $this->update([
            'status'       => 'rejected',
            'processed_at' => now(),
            'processed_by' => $admin->id,
        ]);
    }
}
