<?php

namespace App\Actions\Programs;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutDay;

class AttachExerciseToDayAction
{
    public function execute(User $user, WorkoutDay $day, array $data): void
    {
        abort_unless($day->program()->where('user_id', $user->id)->exists(), 403);

        abort_unless(Exercise::query()->whereKey($data['exercise_id'])
            ->accessibleTo($user)
            ->exists(), 403);

        $day->dayExercises()->create([
            'exercise_id' => $data['exercise_id'],
            'default_sets' => $data['default_sets'],
            'default_reps' => $data['default_reps'],
        ]);
    }
}
