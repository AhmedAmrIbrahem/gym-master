<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutProgram extends Model
{
    protected $fillable = ['user_id', 'name', 'is_active'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<WorkoutDay, $this> */
    public function days(): HasMany
    {
        return $this->hasMany(WorkoutDay::class, 'workout_program_id');
    }
}
