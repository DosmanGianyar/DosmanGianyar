<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dispensation extends Model
{
    protected $fillable = [
        'requester_id', 'approved_by', 'activity_name', 'date', 'file', 'status',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dispensation_students', 'dispensation_id', 'student_id');
    }

    public function isApproved(): bool { return $this->status === 'approved'; }
}
