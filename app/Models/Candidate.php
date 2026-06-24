<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    protected $fillable = [
        'voting_session_id', 'name', 'vision', 'photo', 'order',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(VotingSession::class, 'voting_session_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function photoUrl(): ?string
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::url($this->photo);
        }
        return null;
    }

    public function voteCount(): int
    {
        return $this->votes()->count();
    }
}
