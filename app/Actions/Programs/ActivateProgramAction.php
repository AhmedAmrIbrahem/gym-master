<?php

namespace App\Actions\Programs;

use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Support\Facades\DB;

class ActivateProgramAction
{
    public function execute(User $user, WorkoutProgram $program): void
    {
        abort_unless((int) $program->user_id === (int) $user->id, 403);

        DB::transaction(function () use ($user, $program): void {
            $user->workoutPrograms()->update(['is_active' => false]);
            $program->update(['is_active' => true]);
        });
    }
}
