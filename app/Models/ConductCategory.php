<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConductCategory extends Model
{
    protected $fillable = ['name', 'type', 'context', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ConductLog::class, 'category_id');
    }

    public function isPrestasi(): bool   { return $this->type === 'prestasi'; }
    public function isPelanggaran(): bool { return $this->type === 'pelanggaran'; }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeContext(Builder $query, string $context): Builder
    {
        return $query->where('context', $context);
    }
}
