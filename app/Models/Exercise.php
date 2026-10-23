<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $fillable = ['user_id', 'name', 'target_muscle'];

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->whereNull('user_id')
            ->orWhere('user_id', $user->id));
    }

    /** @return HasMany<WorkoutLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }
}
