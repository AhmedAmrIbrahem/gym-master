<?php

namespace App\Actions\Programs;

use App\Models\User;
use App\Models\WorkoutProgram;

class CreateProgramAction
{
    public function execute(User $user, string $name): WorkoutProgram
    {
        return $user->workoutPrograms()->create([
            'name' => $name,
            'is_active' => $user->workoutPrograms()->count() === 0,
        ]);
    }
}
