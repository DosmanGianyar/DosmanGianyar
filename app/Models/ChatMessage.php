<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(ChatMessageRead::class, 'chat_message_id');
    }

    /**
     * Cek apakah pesan ini sudah dibaca oleh user tertentu
     */
    public function isReadBy(int $userId): bool
    {
        return $this->reads->contains('user_id', $userId);
    }

    /**
     * Rincian siapa saja yang telah membaca pesan ini (khusus lawan bicara)
     */
    public function getReadDetailsAttribute(): array
    {
        $reads = $this->relationLoaded('reads')
            ? $this->reads->where('user_id', '!=', $this->sender_id)
            : $this->reads()->where('user_id', '!=', $this->sender_id)->with('user')->get();

        return $reads->map(function ($read) {
            $roleLabel = match ($read->user?->role) {
                'guru'     => 'Wali Kelas',
                'orangtua' => 'Orang Tua',
                'siswa'    => 'Siswa',
                default    => 'Pengguna',
            };

            return [
                'user_id'   => $read->user_id,
                'user_name' => $read->user?->name ?? 'Pengguna',
                'role'      => $roleLabel,
                'read_at'   => $read->read_at?->format('H:i') ?? '',
                'read_date' => $read->read_at?->isoFormat('D MMM Y') ?? '',
            ];
        })
        ->values()
        ->toArray();
    }

    /**
     * Cek apakah pesan dapat ditarik oleh user tertentu
     * (Hanya pengirim asli, belum dihapus, dan maksimal 60 menit)
     */
    public function canBeDeletedBy(User $user): bool
    {
        if ($this->is_deleted) {
            return false;
        }

        if ($this->sender_id !== $user->id) {
            return false;
        }

        // Batas waktu tarik pesan 60 menit
        return $this->created_at->diffInMinutes(now()) <= 60;
    }

    /**
     * Status pembacaan untuk pengirim:
     * - 'sent' (centang 1 abu-abu) jika belum ada lawan bicara yang membaca
     * - 'read' (centang 2 biru) jika sudah ada minimal 1 lawan bicara yang membaca
     * - 'read_all' jika semua lawan bicara sudah membaca
     */
    public function getReadStatusAttribute(): string
    {
        $readsCount = $this->relationLoaded('reads')
            ? $this->reads->where('user_id', '!=', $this->sender_id)->count()
            : $this->reads()->where('user_id', '!=', $this->sender_id)->count();

        if ($readsCount === 0) {
            return 'sent'; // centang abu-abu
        }

        // Hitung total partisipan lain di room selain pengirim
        $room = $this->relationLoaded('chatRoom') ? $this->chatRoom : ChatRoom::find($this->chat_room_id);
        $otherParticipantsCount = $room ? $room->participants()->where('user_id', '!=', $this->sender_id)->count() : 0;

        return ($otherParticipantsCount > 0 && $readsCount >= $otherParticipantsCount)
            ? 'read_all'
            : 'read';
    }
}
