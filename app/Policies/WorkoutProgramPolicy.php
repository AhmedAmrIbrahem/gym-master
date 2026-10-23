<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutProgram;

class WorkoutProgramPolicy
{
    public function update(User $user, WorkoutProgram $program): bool
    {
        return (int) $program->user_id === (int) $user->id;
    }

    public function delete(User $user, WorkoutProgram $program): bool
    {
        return $this->update($user, $program);
    }

    public function activate(User $user, WorkoutProgram $program): bool
    {
        return $this->update($user, $program);
    }

    public function addDay(User $user, WorkoutProgram $program): bool
    {
        return $this->update($user, $program);
    }
}
