<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatRoom extends Model
{
    protected $fillable = [
        'student_id',
        'school_class_id',
        'type',
        'name',
        'last_message_at',
        'last_message_text',
        'last_message_sender_id',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function isTrio(): bool
    {
        return ($this->type ?? 'trio') === 'trio';
    }

    public function isParentTeacher(): bool
    {
        return ($this->type ?? '') === 'parent_teacher';
    }

    public function scopeTrio($query)
    {
        return $query->where('type', 'trio');
    }

    public function scopeParentTeacher($query)
    {
        return $query->where('type', 'parent_teacher');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function lastMessageSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_message_sender_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChatRoomParticipant::class, 'chat_room_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_participants', 'chat_room_id', 'user_id')
            ->withPivot('role', 'last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_room_id');
    }

    /**
     * Hitung jumlah pesan belum dibaca oleh user tertentu di room ini
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->count();
    }
}
