<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AchievementCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function achievements(): HasMany
    {
        return $this->hasMany(StudentAchievement::class, 'category_id');
    }
}
