<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutDay extends Model
{
    protected $fillable = ['workout_program_id', 'name'];

    // تحديد workout_program_id صراحة لتفادي خطأ البحث عن program_id

    /** @return BelongsTo<WorkoutProgram, $this> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(WorkoutProgram::class, 'workout_program_id');
    }

    /** @return HasMany<DayExercise, $this> */
    public function dayExercises(): HasMany
    {
        return $this->hasMany(DayExercise::class, 'workout_day_id');
    }
}
