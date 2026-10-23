<?php

namespace App\Actions\Workouts;

use App\Models\WorkoutLog;
use App\Models\WorkoutSession;
use Illuminate\Support\Facades\DB;

class SaveWorkoutExerciseAction
{
    /**
     * Save one exercise's sets for today's workout session.
     * The caller must validate inputs and authorize the day and exercise.
     *
     * @param  array<int, array{weight?: numeric-string|int|float|null, reps?: int|string|null}>  $sets
     */
    public function execute(
        int $userId,
        int $workoutDayId,
        int $exerciseId,
        array $sets,
        ?string $notes = null,
    ): void {
        DB::transaction(function () use ($userId, $workoutDayId, $exerciseId, $sets, $notes): void {
            $session = WorkoutSession::firstOrCreate(
                [
                    'user_id' => $userId,
                    'workout_day_id' => $workoutDayId,
                    'date' => now()->toDateString(),
                ],
                ['notes' => $notes],
            );

            WorkoutLog::where('workout_session_id', $session->id)
                ->where('exercise_id', $exerciseId)
                ->delete();

            foreach ($sets as $index => $setData) {
                // Preserve the existing UI behavior: skip incomplete/empty sets.
                if (! empty($setData['weight']) && ! empty($setData['reps'])) {
                    WorkoutLog::create([
                        'workout_session_id' => $session->id,
                        'exercise_id' => $exerciseId,
                        'set_number' => $index + 1,
                        'weight' => $setData['weight'],
                        'reps' => $setData['reps'],
                    ]);
                }
            }
        });
    }
}
