<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayExercise extends Model
{
    protected $fillable = ['workout_day_id', 'exercise_id', 'default_sets', 'default_reps'];

    /** @return BelongsTo<WorkoutDay, $this> */
    public function workoutDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutDay::class);
    }

    /** @return BelongsTo<Exercise, $this> */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
