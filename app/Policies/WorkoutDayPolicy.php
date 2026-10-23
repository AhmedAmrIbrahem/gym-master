<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutDay;

class WorkoutDayPolicy
{
    public function update(User $user, WorkoutDay $day): bool
    {
        return $day->program()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, WorkoutDay $day): bool
    {
        return $this->update($user, $day);
    }

    public function attachExercise(User $user, WorkoutDay $day): bool
    {
        return $this->update($user, $day);
    }
}
