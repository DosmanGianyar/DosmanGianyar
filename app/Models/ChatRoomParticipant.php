<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatRoomParticipant extends Model
{
    protected $fillable = [
        'chat_room_id',
        'user_id',
        'role',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
