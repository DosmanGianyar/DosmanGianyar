<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VotingSession extends Model
{
    protected $fillable = [
        'title', 'description', 'start_time', 'end_time', 'status', 'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->orderBy('order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'  => 'Draft',
            'active' => 'Berlangsung',
            'closed' => 'Selesai',
            default  => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft'  => 'gray',
            'active' => 'green',
            'closed' => 'blue',
            default  => 'gray',
        };
    }

    public function hasVoted(int $userId): bool
    {
        return $this->votes()->where('voter_id', $userId)->exists();
    }

    public function myVote(int $userId): ?Vote
    {
        return $this->votes()->where('voter_id', $userId)->with('candidate')->first();
    }
}
