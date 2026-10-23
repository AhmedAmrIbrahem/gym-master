<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    protected $fillable = ['user_id', 'workout_day_id', 'date', 'notes'];

    /** @return BelongsTo<WorkoutDay, $this> */
    public function workoutDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutDay::class);
    }

    /** @return HasMany<WorkoutLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }
}
