<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;

class ExercisePolicy
{
    public function update(User $user, Exercise $exercise): bool
    {
        return $exercise->user_id !== null && (int) $exercise->user_id === (int) $user->id;
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $this->update($user, $exercise);
    }
}
