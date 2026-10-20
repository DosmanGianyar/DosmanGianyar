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
     * Status pembacaan untuk pengirim:
     * - 'sent' (centang 1 abu-abu) jika belum ada lawan bicara yang membaca
     * - 'read' (centang 2 biru) jika sudah ada minimal 1 lawan bicara yang membaca
     * - 'read_all' jika semua lawan bicara sudah membaca
     */
    public function getReadStatusAttribute(): string
    {
        $readsCount = $this->reads()->where('user_id', '!=', $this->sender_id)->count();
        if ($readsCount === 0) {
            return 'sent'; // centang abu-abu
        }

        // Hitung total partisipan lain di room selain pengirim
        $otherParticipantsCount = $this->chatRoom->participants()
            ->where('user_id', '!=', $this->sender_id)
            ->count();

        return ($otherParticipantsCount > 0 && $readsCount >= $otherParticipantsCount)
            ? 'read_all'
            : 'read';
    }
}
