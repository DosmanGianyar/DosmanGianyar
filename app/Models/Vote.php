<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'voting_session_id', 'voter_id', 'candidate_id',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(VotingSession::class, 'voting_session_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
